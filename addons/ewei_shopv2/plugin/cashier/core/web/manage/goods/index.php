<?php
/*
 * 人人商城
 *
 * 青岛易联互动网络科技有限公司
 * http://www.we7shop.cn
 * TEL: 4000097827/18661772381/15865546761
 */
if (!defined('IN_IA')) {
    exit('Access Denied');
}
require EWEI_SHOPV2_PLUGIN . 'cashier/core/inc/page_cashier.php';
class Index_EweiShopV2Page extends CashierWebPage
{
    public function main()
    {
        global $_W,$_GPC;
        if ($_W['ispost']){
            $selfgoods = isset($_GPC['selfgoods']) ? $_GPC['selfgoods'] : array();
            $goods = isset($_GPC['goods']) ? $_GPC['goods'] : array();
            $data = $_GPC['data'];

            /*线上商品收款时，判断库存是否充足 by lgt -- start*/
            if (!empty($goods)) {
                //线上商品ID集
                $goodsIds = array();
                foreach ($goods as $index => $item) {
                    $goodsIds[] = $item['goodsid'];
                }

                //根据ID集查询商品信息
                $goodsData = array();
                if (!empty($goodsIds)) {
                    $goodsData = pdo_fetchall('SELECT title,total FROM ' . tablename('ewei_shop_goods') . ' WHERE id IN  (:id)', array(':id' => implode(',', $goodsIds)));
                }

                //找出线上商品库存不足的给出错误提示
                if (!empty($goodsData)) {
                    foreach ($goodsData as $g) {
                        if ((int)$g['total'] <= 0) {
                            show_json(0,$g['title'].'库存不足');
                        }
                    }
                }
            }
            /* 判断库存 -- end */

            if ((float)$data['money'] <= 0){
                show_json(-101,'请输入有效收款金额');
            }

            if (!empty($data['mobile'])){
                $userinfo = m('member')->getMobileMember($data['mobile']);
            }

            $data['paytype'] = $this->model->paytype((int)$data['paytype'],$data['auth_code']);
            $params = array(
                'auth_code'=>$data['auth_code'],
                'paytype'=>$data['paytype'],
                'openid'=>isset($userinfo['openid']) ? $userinfo['openid'] : '',
                'money'=>$data['money'],
                'deduction'=>$data['deduction'],
                'mobile'=>$data['mobile'],
                'operatorid'=>isset($_W['cashieruser']['operator']) ? $_W['cashieruser']['operator']['id'] : 0,
            );
            $res = $this->model->goodsCalculate($selfgoods,$goods,$params);
            if (is_error($res['res'])){
                if($res['res']['errno'] == -2){
                    $message = explode(':',$res['res']['message']);
                    if ($message[0] != 'USERPAYING' && $message[0] != 'need_query'){
                        show_json(-101,$res['res']);
                    }
                }else{
                    show_json(-101,$res['res']);
                }
            }
            $success = $this->model->payResult($res['id']);
            $success ? show_json(1,'收款成功!') : show_json(0,$res['id']);
        }
        $cate = pdo_fetchall("SELECT * FROM " . tablename('ewei_shop_cashier_goods_category') . " WHERE uniacid=:uniacid and cashierid=:cashierid ORDER BY displayorder desc, id DESC", array(':uniacid' => $_W['uniacid'],':cashierid'=>$_W['cashierid']));
        include $this->template();
    }

    public function get_goods()
    {
        global $_W,$_GPC;
        $keyword = trim($_GPC['keyword']);
        $cate = trim($_GPC['cate']);

        //库存预警值
        $goodstotal = intval($_W['shopset']['shop']['goodstotal']);
        if (empty($keyword) && empty($cate)){
            show_json(0,'请选择分类或者输入关键字!');
        }

        $selfgoods = array();
        $goods = array();

        if ($cate != 'shop'){
            $where = '';
            $params = array();

            if (!empty($cate)){
                $where .= ' AND categoryid=:categoryid';
                $params[':categoryid'] = $cate;
            }

            if (!empty($keyword)){
                $where .= ' AND (title LIKE :keyword OR goodssn LIKE :goodssn)';
                $params[':goodssn'] = "%{$keyword}%";
                $params[':keyword'] = "%{$keyword}%";
//                if (is_numeric($keyword) && isset($keyword['7'])){
//                    $where .= ' AND goodssn = :goodssn';
//                    $params[':goodssn'] = $keyword;
//                }else{
//                    $where .= ' AND title LIKE :keyword';
//                    $params[':keyword'] = "%{$keyword}%";
//                }
            }
            $selfgoods = $this->getSelfGoods($where,$params);
            foreach ($selfgoods as $key=>$item){
                //库存预警
                $iswarning=0;

                if($goodstotal!=0){
                    $iswarning =$item['total']<$goodstotal?1:0;
                }
                $selfgoods[$key]['iswarning'] =$iswarning;
            }

        }


        if (($cate != 'shop' && empty($cate)) || $cate == 'shop' || empty($cate)){
            $goodsmanage = $this->getUserSet('goodsmanage');

            if (!empty($goodsmanage['goods_ids'])){

                $where = '';
                $params = array(':uniacid'=>$_W['uniacid']);

                if (!empty($keyword)){
                    $where .= ' AND (productsn LIKE :productsn OR title LIKE :keyword)';
                    $params[':productsn'] = "%{$keyword}%";
                    $params[':keyword'] = "%{$keyword}%";
//                    if (is_numeric($keyword) && isset($keyword['7'])){
//                        $where .= ' AND productsn = :productsn';
//                        $params[':productsn'] = "%{$keyword}%";
//                    }else{
//                        $where .= ' AND title LIKE :keyword';
//                        $params[':keyword'] = "%{$keyword}%";
//                    }
                }

                $goods_ids = implode(',',$goodsmanage['goods_ids']);

                $mer_condition = '';
                if($_W['cashieruser']['merchid']){
                    $mer_condition = ' AND merchid='.$_W['cashieruser']['merchid'];
                }

                $goods = pdo_fetchall("SELECT id,thumb,title,marketprice,total as stock,maxbuy,minbuy,productsn,unit,hasoption,showtotal,diyformid,diyformtype,diyfields,isdiscount,isdiscount_time,isdiscount_discounts, needfollow, followtip, followurl, `type`, isverify, maxprice, minprice, merchsale FROM ".tablename('ewei_shop_goods')." WHERE uniacid=:uniacid AND cashier=1 AND id IN ({$goods_ids}) {$where} {$mer_condition}",$params);

                //根据关键字查询规格的条码获取收银台商品
                $option_goods =   pdo_fetchall("SELECT goodsid FROM ".tablename('ewei_shop_goods_option')." WHERE uniacid=:uniacid {$where}",$params);

                $goods_id_arr = array();
                $goods1 = array();
                if(!empty($option_goods)){
                    foreach($option_goods as $option_goodsk => $option_goodsv){
                        $goods_id_arr[] = $option_goodsv['goodsid'];
                    }

                    if(!empty($goodsmanage['goods_ids'])) {
                        $goods_id_arr = array_unique($goods_id_arr);
                        $intersect = array_intersect($goodsmanage['goods_ids'], $goods_id_arr);//返回数组交集
                        $goods_id = implode(",", $intersect);
                        $goods1 = pdo_fetchall("SELECT id,thumb,title,marketprice,total as stock,maxbuy,minbuy,productsn,unit,hasoption,showtotal,diyformid,diyformtype,diyfields,isdiscount,isdiscount_time,isdiscount_discounts, needfollow, followtip, followurl, `type`, isverify, maxprice, minprice, merchsale FROM " . tablename('ewei_shop_goods') . " WHERE uniacid={$_W['uniacid']} AND cashier=1 AND id IN ({$goods_id}) {$mer_condition}");
                    }
                }
                //合并通过商品条码查询和通过商品规格条码查询的数据
                if(is_array($goods1)){
                    $goods = array_merge($goods,$goods1);
                }
                foreach ($goods as $k=>$g){
                    $options = false;
                    $stock = 0;
                    $price = $g['marketprice'];
                    //库存预警
                    $iswarning=0;
                    if (!empty($g) && $g['hasoption']) {
                        $options = pdo_fetchall('select * from ' . tablename('ewei_shop_goods_option') . ' where goodsid=:goodsid and uniacid=:uniacid order by displayorder asc', array(':goodsid' => $g['id'], ':uniacid' => $_W['uniacid']),'id');

                        if (!empty($options)){
                            foreach ($options as $key=>$val){
                                $stock += $val['stock'];
                                if(!$iswarning && $goodstotal!=0){
                                    $iswarning=$val['stock']<$goodstotal?1:0;
                                }
                            }
                            $options_val = array_values($options);
                            $op = array_shift($options_val);
                            $price = $op['marketprice'];
                        }
                        $goods[$k]['stock'] = $stock;
                        $goods[$k]['options'] = $options;
                        $goods[$k]['price'] = $price;
                    }

                    if(!$g['hasoption'] && $goodstotal!=0){

                        $iswarning =$g['stock']<$goodstotal?1:0;
                    }
                    $goods[$k]['iswarning'] =$iswarning;
                }



            }
        }
        foreach ($selfgoods as &$val){
            $val['thumb'] = tomedia($val['image']);
        }
        unset($val);
        foreach ($goods as &$val){
            $val['thumb'] = tomedia($val['thumb']);
        }

        unset($val);
        show_json(1,array('selfgoods'=>$selfgoods,'goods'=>$goods));
    }

    protected function getSelfGoods($where='',$params=array())
    {
        global $_W;
        $params = array_merge(array(':cashierid'=>$_W['cashierid']),$params);
        $selfgoods = pdo_fetchall("SELECT * FROM ". tablename('ewei_shop_cashier_goods')." WHERE cashierid=:cashierid {$where} AND status=1 AND total<>0",$params);
        return $selfgoods;
    }

    function query()
    {
        global $_W, $_GPC;
        $kwd = trim($_GPC['keyword']);
        $params = array();
        $params[':uniacid'] = $_W['uniacid'];
        $condition = " and uniacid=:uniacid";

        if (!empty($kwd)) {
            $condition .= " AND (`realname` LIKE :keyword or `nickname` LIKE :keyword or `mobile` LIKE :keyword)";
            $params[':keyword'] = "%{$kwd}%";
        }
        $ds = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_member') . " WHERE 1 {$condition} order by id asc", $params);

        if ($_GPC['suggest']) {
            die(json_encode(array('value' => $ds)));
        }
        include $this->template();
    }

    public function orderquery()
    {
        global $_W,$_GPC;
        $orderid = $_GPC['orderid'];
        if (!empty($orderid)){
            $res = $this->model->orderQuery($orderid);
            if (!empty($res)){
                show_json(1,array('list'=>$res));
            }
        }
        show_json(0,'支付结果等待中!');
    }

    function query_member()
    {
        global $_W,$_GPC;
        $mobile = trim($_GPC['mobile']);
        if (!$mobile){
            show_json(0);
        }
        $info = m('member')->getMobileMember($mobile);
        if (!empty($info['salt']) && !empty($info['pwd'])){
            show_json(1);
        }else{
            show_json(2);
        }
        show_json(0);
    }

    function verify_password()
    {
        global $_W,$_GPC;
        if ($_W['ispost']){
            $password = trim($_GPC['password']);
            $mobile = trim($_GPC['mobile']);
            $info = m('member')->getMobileMember($mobile);
            if (md5($password.$info['salt']) == $info['pwd']){
                show_json(1,$info);
            }
        }
        show_json(0);
    }

    function set_password()
    {
        global $_W,$_GPC;
        if ($_W['ispost']){
            $password = trim($_GPC['password']);
            $mobile = intval($_GPC['mobile']);
            $info = m('member')->getMobileMember($mobile);
            if (empty($info['salt']) && empty($info['pwd'])){
                $salt = random(8);
                $pwd = md5($password.$salt);
                pdo_update('ewei_shop_member',array('pwd'=>$pwd,'salt'=>$salt),array('id'=>$info['id']));
                show_json(1,$info);
            }
        }
        show_json(0);
    }

    function goodsquery(){
        global $_W, $_GPC;

        $where = '';
        $params = array(
            'uniacid' => $_W['uniacid'],
            'merchid' => $_W['cashieruser']['merchid']
        );
        if (!empty($_GPC['keyword'])){
            $where = " AND (title LIKE :keyword OR subtitle LIKE :keyword OR shorttitle LIKE :keyword)";
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }

        $ds = pdo_fetchall("SELECT id,uniacid,title,subtitle,shorttitle,thumb,share_icon FROM ".tablename('ewei_shop_goods')." WHERE uniacid=:uniacid AND merchid=:merchid AND cashier=1 {$where}",$params);
        $ds = set_medias($ds,array('thumb','share_icon'));
        include $this->template('index/goodsquery');
    }
}