<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
set_time_limit(0);

class TaskModel extends PluginModel
{

    //是否有未读消息
    public function get_unread()
    {
        global $_W;
        $sql = "select count(*) from " . tablename('ewei_shop_task_reward') . " where openid = :openid and uniacid = :uniacid and `get` = 1 and `sent` = 0 and `read` = 0";
        return pdo_fetchcolumn($sql, array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
    }

    //设为已读
    public function set_read()
    {
        global $_W;
        pdo_update('ewei_shop_task_reward', array('read' => 1), array('openid' => $_W['openid'], 'uniacid' => $_W['uniacid'],'get'=>1,'sent'=>0,'read'=>0));
    }

    public function isnew()
    {
        global $_W;
        $uniacid = pdo_fetchcolumn("select uniacid from " . tablename('ewei_shop_task_set') . " where uniacid = {$_W['uniacid']}");
        if (empty($uniacid)) pdo_insert('ewei_shop_task_set', array('uniacid' => $_W['uniacid']));
        $this->set_read();
        return pdo_fetchcolumn('select isnew from ' . tablename('ewei_shop_task_set') . " where uniacid = {$_W['uniacid']}");
    }

    public function getSceneTicket($expire, $scene_id)
    {

        global $_W, $_GPC;

        $account = m('common')->getAccount();
        $bb = "{\"expire_seconds\":" . $expire . ",\"action_info\":{\"scene\":{\"scene_id\":" . $scene_id . "}},\"action_name\":\"QR_SCENE\"}";
        $token = $account->fetch_token();
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $token;
        $ch1 = curl_init();
        curl_setopt($ch1, CURLOPT_URL, $url);
        curl_setopt($ch1, CURLOPT_POST, 1);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch1, CURLOPT_POSTFIELDS, $bb);
        $c = curl_exec($ch1);
        $result = @json_decode($c, true);
        if (!is_array($result)) {
            return false;
        }

        if (!empty($result['errcode'])) {
            return error(-1, $result['errmsg']);
        }
        $ticket = $result['ticket'];
        return array('barcode' => json_decode($bb, true), 'ticket' => $ticket);
    }

    function getSceneID()
    {

        global $_W;
        $acid = $_W['acid'];
        //$start  = -2147483648;
        $start = 1;
        $end = 2147483647;
        $scene_id = rand($start, $end);
        if (empty($scene_id)) {
            $scene_id = rand($start, $end);
        }
        while (1) {

            $count = pdo_fetchcolumn('select count(*) from ' . tablename('qrcode') . ' where qrcid=:qrcid and acid=:acid and model=0 limit 1', array(':qrcid' => $scene_id, ":acid" => $acid));
            if ($count <= 0) {
                break;
            }
            $scene_id = rand($start, $end);
            if (empty($scene_id)) {
                $scene_id = rand($start, $end);
            }
        }
        return $scene_id;
    }

    //获取各类型的二维码
    public function getQR($poster, $member)
    {

        global $_W, $_GPC;
        $acid = $_W['acid'];

        //过期时间
        $time = time();
        $expire = $poster['days'];
        if ($expire > 86400 * 30 - 15) {
            $expire = 86400 * 30 - 15;
        }
        $posterendtime = $time + $expire;

        //查找用户二维码
        $qr = pdo_fetch('select * from ' . tablename('ewei_shop_task_poster_qr') . ' where openid=:openid and acid=:acid and posterid=:posterid limit 1', array(':openid' => $member['openid'], ':acid' => $acid, ':posterid' => $poster['id']));
        if (empty($qr)) {
            $qr['current_qrimg'] = '';

            $scene_id = $this->getSceneID();
            $result = $this->getSceneTicket($expire, $scene_id);
            if (is_error($result)) {
                return $result;
            }
            if (empty($result)) {
                return error(-1, '生成二维码失败');
            }
            $barcode = $result['barcode'];
            $ticket = $result['ticket'];
            $qrimg = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $ticket;
            $ims_qrcode = array(
                'uniacid' => $_W['uniacid'],
                'acid' => $_W['acid'],
                'qrcid' => $scene_id,
                //'type'=>'scene',
                "model" => 0,
                "name" => "EWEI_SHOPV2_TASK_QRCODE",
                "keyword" => $poster['keyword'],
                "expire" => $expire,
                "createtime" => time(),
                "status" => 1,
                'url' => $result['url'],
                "ticket" => $result['ticket']
            );
            pdo_insert('qrcode', $ims_qrcode);
            $qr = array(
                'acid' => $acid,
                'openid' => $member['openid'],
                'sceneid' => $scene_id,
                'type' => 1,
                'ticket' => $result['ticket'],
                'qrimg' => $qrimg,
                'posterid' => $poster['id'],
                'expire' => $expire,
                'url' => $result['url'],
                'endtime' => $posterendtime
            );
            pdo_insert('ewei_shop_task_poster_qr', $qr);
            $qr['id'] = pdo_insertid();
        } else {

            $qr['current_qrimg'] = $qr['qrimg'];
            if ($time > $qr['endtime']) {

                $scene_id = $qr['sceneid'];
                $result = $this->getSceneTicket($expire, $scene_id);
                if (is_error($result)) {
                    return $result;
                }
                if (empty($result)) {
                    return error(-1, '生成二维码失败');
                }
                $barcode = $result['barcode'];
                $ticket = $result['ticket'];
                $qrimg = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $ticket;
                pdo_update('qrcode', array('ticket' => $result['ticket'], 'url' => $result['url']), array('acid' => $_W['acid'], 'qrcid' => $scene_id));
                pdo_update('ewei_shop_task_poster_qr', array('ticket' => $ticket, 'qrimg' => $qrimg, 'url' => $result['url'], 'endtime' => $posterendtime), array('id' => $qr['id']));
                $qr['ticket'] = $ticket;
                $qr['qrimg'] = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $qr['ticket'];
            }
        }
        return $qr;
    }

    public function getRealData($data)
    {

        $data['left'] = intval(str_replace('px', '', $data['left'])) * 2;
        $data['top'] = intval(str_replace('px', '', $data['top'])) * 2;
        $data['width'] = intval(str_replace('px', '', $data['width'])) * 2;
        $data['height'] = intval(str_replace('px', '', $data['height'])) * 2;
        $data['size'] = intval(str_replace('px', '', $data['size'])) * 2;
        $data['src'] = tomedia($data['src']);
        return $data;
    }

    public function createImage($imgurl)
    {
        load()->func('communication');
        $resp = ihttp_request($imgurl);
        if ($resp['code'] == 200 && !empty($resp['content'])) {
            return imagecreatefromstring($resp['content']);
        }
        $i = 0;
        while ($i < 3) {
            $resp = ihttp_request($imgurl);
            if ($resp['code'] == 200 && !empty($resp['content'])) {
                return imagecreatefromstring($resp['content']);
            }
            $i++;
        }
        return "";
    }

    public function mergeImage($target, $data, $imgurl)
    {

        $img = $this->createImage($imgurl);
        $w = imagesx($img);
        $h = imagesy($img);
        imagecopyresized($target, $img, $data['left'], $data['top'], 0, 0, $data['width'], $data['height'], $w, $h);
        imagedestroy($img);
        return $target;
    }

    public function mergeHead($target, $data, $imgurl)
    {
        if ($data['head_type'] == 'default') {
            $img = $this->createImage($imgurl);
            $w = imagesx($img);
            $h = imagesy($img);
            imagecopyresized($target, $img, $data['left'], $data['top'], 0, 0, $data['width'], $data['height'], $w, $h);
            imagedestroy($img);
            return $target;
        } elseif ($data['head_type'] == 'circle') {

        } elseif ($data['head_type'] == 'rounded') {

        }

    }

    public function mergeText($target, $data, $text)
    {
        $font = IA_ROOT . "/addons/ewei_shopv2/static/fonts/msyh.ttf";
        $colors = $this->hex2rgb($data['color']);
        $color = imagecolorallocate($target, $colors['red'], $colors['green'], $colors['blue']);
        imagettftext($target, $data['size'], 0, $data['left'], $data['top'] + $data['size'], $color, $font, $text);
        return $target;
    }

    function hex2rgb($colour)
    {
        if ($colour[0] == '#') {
            $colour = substr($colour, 1);
        }
        if (strlen($colour) == 6) {
            list($r, $g, $b) = array($colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]);
        } elseif (strlen($colour) == 3) {
            list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
        } else {
            return false;
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array('red' => $r, 'green' => $g, 'blue' => $b);
    }

    //生成海报图片
    public function createPoster($poster, $member, $qr, $upload = true)
    {

        global $_W;
        $path = IA_ROOT . "/addons/ewei_shopv2/data/task/poster/" . $_W['uniacid'] . "/";

        if (!is_dir($path)) {
            load()->func('file');
            mkdirs($path);
        }
        //文件名称，如果参数有变动，重新生成
        $md5 = md5(json_encode(array(
            'openid' => $member['openid'],
            'id' => $qr['id'],
            'bg' => $poster['bg'],
            'data' => $poster['data'],
            'version' => 1
        )));
        $file = $md5 . '.png';
        $is_new = false;
        if (!is_file($path . $file) || $qr['qrimg'] != $qr['current_qrimg']) {
            $is_new = true;
            //未生成过，或二维码变化
            //生成背景
            set_time_limit(0);
            @ini_set("memory_limit", "256M");
            $target = imagecreatetruecolor(640, 1008);
            $bg = $this->createImage(tomedia($poster['bg']));

            imagecopy($target, $bg, 0, 0, 0, 0, 640, 1008);
            imagedestroy($bg);
            $data = json_decode(str_replace('&quot;', "'", $poster['data']), true);


            foreach ($data as $d) {

                $d = $this->getRealData($d);
                if ($d['type'] == 'head') {
                    $avatar = preg_replace('/\/0$/i', '/96', $member['avatar']);
                    $target = $this->mergeImage($target, $d, $avatar);
                } else if ($d['type'] == 'time') {
                    $time = date('Y-m-d H:i', $qr['endtime']);
                    $target = $this->mergeText($target, $d, $d['title'] . ':' . $time);
                } else if ($d['type'] == 'img') {
                    $target = $this->mergeImage($target, $d, $d['src']);
                } else if ($d['type'] == 'qr') {
                    $target = $this->mergeImage($target, $d, tomedia($qr['qrimg']));
                } else if ($d['type'] == 'nickname') {
                    $target = $this->mergeText($target, $d, $member['nickname']);
                } else {

                    if (!empty($goods)) {
                        //商品
                        if ($d['type'] == 'title') {
                            $target = $this->mergeText($target, $d, $goods['title']);
                        } else if ($d['type'] == 'thumb') {
                            $thumb = !empty($goods['commission_thumb']) ? tomedia($goods['commission_thumb']) : tomedia($goods['thumb']);
                            $target = $this->mergeImage($target, $d, $thumb);
                        } else if ($d['type'] == 'marketprice') {
                            $target = $this->mergeText($target, $d, $goods['marketprice']);
                        } else if ($d['type'] == 'productprice') {
                            $target = $this->mergeText($target, $d, $goods['productprice']);
                        }
                    }
                }
            }
            imagepng($target, $path . $file);
            imagedestroy($target);
        }

        $img = $_W['siteroot'] . "addons/ewei_shopv2/data/task/poster/" . $_W['uniacid'] . "/" . $file;

        if (!$upload) {
            return $img;
        }

        if ($qr['qrimg'] != $qr['current_qrimg'] || empty($qr['mediaid']) || empty($qr['createtime']) || $qr['createtime'] + 3600 * 24 * 3 - 7200 < time() || $is_new) {
            //没上传或mediaid过期
            $mediaid = $this->uploadImage($path . $file);
            $qr['mediaid'] = $mediaid;
            $qr['img'] = $mediaid;
            pdo_update('ewei_shop_task_poster_qr', array('mediaid' => $mediaid, 'createtime' => time()), array('id' => $qr['id']));
        }
        return array('img' => $img, 'mediaid' => $qr['mediaid']);
    }

    //上传图片
    public function uploadImage($img)
    {
        load()->func('communication');
        $account = m('common')->getAccount();
        $access_token = $account->fetch_token();
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type=image";
        $ch1 = curl_init();
        $data = array("media" => "@" . $img);
        if (version_compare(PHP_VERSION, '5.5.0', '>')) {
            $data = array("media" => curl_file_create($img));
        }
        curl_setopt($ch1, CURLOPT_URL, $url);
        curl_setopt($ch1, CURLOPT_POST, 1);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch1, CURLOPT_POSTFIELDS, $data);
        $content = @json_decode(curl_exec($ch1), true);
        if (!is_array($content)) {
            $content = array('media_id' => '');
        }
        curl_close($ch1);

//			$resp = ihttp_request($url, array(
//				'media' => '@' . $img
//			));
//			$content = @json_decode($resp['content'], true);

        return $content['media_id'];
    }

    public function getQRByTicket($ticket = '')
    {
        global $_W;
        if (empty($ticket)) {
            return false;
        }
        $qrs = pdo_fetchall('select * from ' . tablename('ewei_shop_task_poster_qr') . ' where ticket=:ticket and acid=:acid limit 1', array(':ticket' => $ticket, ':acid' => $_W['acid']));
        $count = count($qrs);
        if ($count <= 0) {
            return false;
        }
        if ($count == 1) {
            return $qrs[0];
        }
        return false;
    }

    public function checkMember($openid = '', $acc = '')
    {
        global $_W;
        $redis = redis();

        if (empty($acc)) {
            $acc = WeiXinAccount::create();
        }
        $userinfo = $acc->fansQueryInfo($openid);
        $userinfo['avatar'] = $userinfo['headimgurl'];

        load()->model('mc');
        $uid = mc_openid2uid($openid);
        if (!empty($uid)) {
            pdo_update('mc_members', array(
                'nickname' => $userinfo['nickname'],
                'gender' => $userinfo['sex'],
                'nationality' => $userinfo['country'],
                'resideprovince' => $userinfo['province'],
                'residecity' => $userinfo['city'],
                'avatar' => $userinfo['headimgurl']), array('uid' => $uid)
            );
        }

        pdo_update('mc_mapping_fans', array(
            'nickname' => $userinfo['nickname']
        ), array('uniacid' => $_W['uniacid'], 'openid' => $openid));

        $model = m('member');
        $member = $model->getMember($openid);
        if (empty($member)) {

            if (!is_error($redis)) {
                $member = $redis->get($openid . '_task_checkMember');

                if (!empty($member)) {
                    return json_decode($member, true);
                }
            }
            $mc = mc_fetch($uid, array('realname', 'nickname', 'mobile', 'avatar', 'resideprovince', 'residecity', 'residedist'));
            $member = array(
                'uniacid' => $_W['uniacid'],
                'uid' => $uid,
                'openid' => $openid,
                'realname' => $mc['realname'],
                'mobile' => $mc['mobile'],
                'nickname' => !empty($mc['nickname']) ? $mc['nickname'] : $userinfo['nickname'],
                'avatar' => !empty($mc['avatar']) ? $mc['avatar'] : $userinfo['avatar'],
                'gender' => !empty($mc['gender']) ? $mc['gender'] : $userinfo['sex'],
                'province' => !empty($mc['resideprovince']) ? $mc['resideprovince'] : $userinfo['province'],
                'city' => !empty($mc['residecity']) ? $mc['residecity'] : $userinfo['city'],
                'area' => $mc['residedist'],
                'createtime' => time(),
                'status' => 0
            );
            pdo_insert('ewei_shop_member', $member);
            $member['id'] = pdo_insertid();
            $member['isnew'] = true;
            if(method_exists(m('member'),'memberRadisCountDelete')) {
                m('member')->memberRadisCountDelete(); //清除会员统计radis缓存
            }
            if (!is_error($redis)) {
                $redis->set($openid . '_task_checkMember', json_encode($member), 20);
            }
        } else {
            $member['nickname'] = $userinfo['nickname'];
            $member['avatar'] = $userinfo['headimgurl'];
            $member['province'] = $userinfo['province'];
            $member['city'] = $userinfo['city'];
            pdo_update('ewei_shop_member', $member, array('id' => $member['id']));
            $member['isnew'] = false;
        }
        return $member;
    }

    function perms()
    {
        return array(
            'task' => array(
                'text' => $this->getName(), 'isplugin' => true,
                'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log', 'log' => '扫描记录', 'clear' => '清除缓存-log', 'setdefault' => '设置默认海报-log'
            ));
    }

    //取消关注
    public function responseUnsubscribe($param = '')
    {
        global $_W;
        if (isset($param['openid']) && !empty($param['openid'])) {
            $openid = $param['openid'];
            $where = array(
                'uniacid' => $_W['uniacid'],
                'joiner_id' => $openid,
            );
            //判断是否存在此用户参加过的任务
            $task_info = pdo_fetch('SELECT join_user FROM ' . tablename('ewei_shop_task_join') . 'WHERE failtime>' . time() . ' and is_reward=0 and join_id in (SELECT join_id from ' . tablename('ewei_shop_task_joiner') . ' where uniacid=:uniacid and joiner_id=:joiner_id and join_status=1)', array(':uniacid' => $_W['uniacid'], ':joiner_id' => $openid));
            if ($task_info) {
                $member = $this->checkMember($openid);
                //更新参加记录
                pdo_update('ewei_shop_task_joiner', array('join_status' => 0), $where);
                //对参加的任务数据更新
                $updatesql = 'UPDATE ' . tablename('ewei_shop_task_join') . ' SET completecount = completecount-1 WHERE failtime>' . time() . ' and is_reward=0 and join_id in (SELECT join_id from ' . tablename('ewei_shop_task_joiner') . ' where uniacid=:uniacid and joiner_id=:joiner_id and join_status=1)';
                pdo_query($updatesql, array(':uniacid' => $_W['uniacid'], ':joiner_id' => $openid));
                //发送通知
                foreach ($task_info as $val) {
                    m('message')->sendCustomNotice($val['join_user'], '您推荐的用户[' . $member['nickname'] . ']取消了关注，您失去了一个小伙伴');
                }
            }
        }
    }

    //编译模版消息 type:1scaner 2tasker
    public function notice_complain($templete, $member, $poster, $scaner = '', $type = 1)
    {
        global $_W;
        $reward_type = 'sub';
        $openid = $scaner['openid'];
        if ($type == 2) {
            $reward_type = 'rec';
            $openid = $member['openid'];
        }
        if ($templete) {
            $templete = trim($templete);
            $templete = str_replace("[任务执行者昵称]", $member['nickname'], $templete);//任务参与者
            $templete = str_replace("[任务名称]", $poster['title'], $templete);//任务名称
//            $templete =str_replace("[popularoty]",$poster['popularoty'],$templete);//人气值名称
            if ($poster['poster_type'] == 1) {
                $templete = str_replace("[任务目标]", $poster['needcount'], $templete);//任务人数
            } elseif ($poster['poster_type'] == 2) {
                $reward_data = unserialize($poster['reward_data']);
                $reward_data = array_shift($reward_data['rec']);
                $templete = str_replace("[任务目标]", $reward_data['needcount'], $templete);//任务人数
            }
            $templete = str_replace("[任务领取时间]", date('Y年m月d日 H:i', $poster['timestart']) . '-' . date('Y年m月d日 H:i', $poster['timeend']), $templete);//任务有效日期
            if (!empty($scaner)) {
                $templete = str_replace("[海报扫描者昵称]", $scaner['nickname'], $templete);//扫描关注者
            }
            if ($poster['reward_data']) {
                $poster['reward_data'] = unserialize($poster['reward_data']);
                $templete = str_replace("[余额奖励]", $poster['reward_data'][$reward_type]['money']['num'], $templete);//奖励余额
                if (isset($poster['reward_data'][$reward_type]['coupon']['total'])) {
                    $templete = str_replace("[奖励优惠券]", $poster['reward_data'][$reward_type]['coupon']['total'], $templete);//奖励优惠券
                } else {
                    $templete = str_replace("[奖励优惠券]", '', $templete);//奖励优惠券
                }
                $templete = str_replace("[积分奖励]", $poster['reward_data'][$reward_type]['credit'], $templete);//奖励积分
                $reward_text = '';
                foreach ($poster['reward_data'][$reward_type] as $key => $val) {
                    if ($key == 'credit') {
                        $reward_text .= '积分' . $val . ' |';//积分20 | 余额50元 | 红包50元 | 优惠券3张 | 指定商品3件
                    }
                    if ($key == 'goods') {
                        $reward_text .= '指定商品' . count($val) . '件';
                    }
                    if ($key == 'money') {
                        $reward_text .= '余额' . $val['num'] . '元 |';
                    }
                    if ($key == 'coupon') {
                        $reward_text .= '优惠券' . $val['total'] . '张 |';
                    }
                    if ($key == 'bribery') {
                        $reward_text .= '红包' . $val . '元 |';
                    }
                }
                $templete = str_replace("[关注奖励列表]", $reward_text, $templete);//任务奖励列表

            } else {
                $templete = str_replace("[余额奖励]", '0', $templete);//奖励余额
                $templete = str_replace("[奖励优惠券]", '0', $templete);//奖励优惠券
                $templete = str_replace("[积分奖励]", '0', $templete);//奖励积分
            }

//            $templete =str_replace("[personal]",mobileUrl('member'),$templete);//个人中心链接
//            $templete =str_replace("[task]",mobileUrl('task/index',array('id'=>$openid)),$templete);//任务中心链接
            if (isset($poster['completecount'])) {
                $notcomplete = intval($poster['needcount'] - $poster['completecount']);
                if ($notcomplete <= 0) {
                    $notcomplete = 0;
                }
                $templete = str_replace("[还需完成数量]", $notcomplete, $templete);//未完成人数
                $templete = str_replace("[完成数量]", intval($poster['completecount']), $templete);//已完成人数
            }
            if (isset($poster['okdays'])) {
                $templete = str_replace("[海报有效期]", date('Y年m月d日 H:i', $poster['okdays']), $templete);
            }

            //任务说明
            $db_data = pdo_fetchcolumn('select `data` from ' . tablename('ewei_shop_task_default') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
            $res = '';
            if (!empty($db_data)) {
                $res = unserialize($db_data);
            }
            $rankinfo = array();
            $rankinfoone = array(
                1 => $res['taskranktitle'] . '1', 2 => $res['taskranktitle'] . '2', 3 => $res['taskranktitle'] . '3', 4 => $res['taskranktitle'] . '4', 5 => $res['taskranktitle'] . '5'
            );
            $rankinfotwo = array(
                1 => $res['taskranktitle'] . 'Ⅰ', 2 => $res['taskranktitle'] . 'Ⅱ', 3 => $res['taskranktitle'] . 'Ⅲ', 4 => $res['taskranktitle'] . 'Ⅳ', 5 => $res['taskranktitle'] . 'Ⅴ'
            );
            $rankinfothree = array(
                1 => $res['taskranktitle'] . 'A', 2 => $res['taskranktitle'] . 'B', 3 => $res['taskranktitle'] . 'C', 4 => $res['taskranktitle'] . 'D', 5 => $res['taskranktitle'] . 'E'
            );
            if ($res['taskranktype'] == 1) {
                $rankinfo = $rankinfoone;
            } elseif ($res['taskranktype'] == 2) {
                $rankinfo = $rankinfotwo;
            } elseif ($res['taskranktype'] == 3) {
                $rankinfo = $rankinfothree;
            } else {
                $rankinfo = $rankinfoone;
            }
            if (isset($poster['reward_rank']) && !empty($poster['reward_rank'])) {
                $templete = str_replace("[任务阶段]", $rankinfo[$poster['reward_rank']], $templete);
            }
            return trim($templete);
        }
        return '';
    }

    //编译模版消息 type:1scaner 2tasker
    public function rec_notice_complain($poster)
    {

        if ($poster['reward_data']) {
            $poster['reward_data'] = unserialize($poster['reward_data']);
            $reward_text = '';
            foreach ($poster['reward_data'] as $key => $val) {
                if ($key == 'credit') {
                    $reward_text .= '积分:' . $val;
                }
                if ($key == 'goods') {
                    $reward_text .= '商品:' . count($val) . '个';
                }
                if ($key == 'money') {
                    $reward_text .= '奖金:' . $val['num'] . '元';
                }
                if ($key == 'coupon') {
                    $reward_text .= '优惠券:' . $val['total'] . '张';
                }
                if ($key == 'bribery') {
                    $reward_text .= '红包:' . $val . '元';
                }
            }
            return trim($reward_text);//任务奖励列表

        }
        return '';

    }

    //系统设置权限
    public function getdefault($key)
    {
        global $_W;
        if ($key) {
            $default = pdo_fetchcolumn('select `data` from ' . tablename('ewei_shop_task_default') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
            $default = unserialize($default);
            return $default[$key];
        } else {
            return 0;
        }

    }


    /*
       * 提供指定价格商品接口
       * 赵坤
       * 20161012
       * $param = array('goods_num'=>1,'goods_id'=>1,'goods_spec'=>1,'openid'=>'openid','rank'=>'rank','join_id'=>'join_id '); goods_num:待减数量
       * */
    public function getGoods($param = '')
    {
        load()->func('logging');
        //传参goods_num则改变指定价格的商品的库存，不传参则返回指定价格的商品信息
        if (empty($param)) {
            return false;
        }

        if (!isset($param['join_id']) || empty($param['join_id'])) {
            return false;
        }
        global $_W;
        $search_sql = 'SELECT * FROM ' . tablename('ewei_shop_task_join') . ' WHERE join_user= :openid AND uniacid = :uniacid AND `join_id`=:join_id  AND is_reward=1';
        $data = array(
            ':uniacid' => $_W['uniacid'],
            ':openid' => $param['openid'],
            ':join_id' => $param['join_id']
        );
        $join_info = pdo_fetch($search_sql, $data);
        if (empty($join_info)) {
            return false;
        }
        if (isset($param['goods_num']) && !empty($param['goods_num'])) {
            //改变指定价格的商品库存

            if ($join_info['task_type'] == 1) {
                //普通海报
                $rec_reward = unserialize($join_info['reward_data']);
                if (!empty($rec_reward)) {
                    $goods_id = intval($param['goods_id']);
                    if (isset($rec_reward['goods'][$goods_id]) && !empty($rec_reward['goods'][$goods_id])) {
                        $goods_spec = intval($param['goods_spec']);
                        $goods_num = intval($param['goods_num']);
                        if ($goods_spec > 0) {
                            $rec_reward['goods'][$goods_id]['spec'][$goods_spec]['total'] -= $goods_num;
                            if ($rec_reward['goods'][$goods_id]['spec'][$goods_spec]['total'] < 0) {
                                return false;
                            } else {
                                $rec_reward = serialize($rec_reward);
                                $update_data = array(
                                    'reward_data' => $rec_reward
                                );
                                $update_where = array(
                                    'join_id' => $param['join_id']
                                );
                                $res = pdo_update('ewei_shop_task_join', $update_data, $update_where);
                                if ($res) {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        } else {
                            $rec_reward['goods'][$goods_id]['total'] -= $goods_num;
                            if ($rec_reward['goods'][$goods_id]['total'] < 0) {
                                return false;
                            } else {
                                $rec_reward = serialize($rec_reward);
                                $update_data = array(
                                    'reward_data' => $rec_reward
                                );
                                $update_where = array(
                                    'join_id' => $param['join_id']
                                );
                                $res = pdo_update('ewei_shop_task_join', $update_data, $update_where);
                                if ($res) {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } elseif ($join_info['task_type'] == 2) {
                //多级海报
                $rec_reward = unserialize($join_info['reward_data']);
                if (!empty($rec_reward)) {
                    $rank = intval($param['rank']);
                    $goods_id = intval($param['goods_id']);
                    if (!isset($rec_reward[$rank]['is_reward']) || empty($rec_reward[$rank]['is_reward'])) {
                        return false;
                    }
                    if (isset($rec_reward[$rank]['goods'][$goods_id]) && !empty($rec_reward[$rank]['goods'][$goods_id])) {
                        $goods_spec = intval($param['goods_spec']);
                        $goods_num = intval($param['goods_num']);
                        if ($goods_spec > 0) {
                            $rec_reward[$rank]['goods'][$goods_id]['spec'][$goods_spec]['total'] -= $goods_num;
                            if ($rec_reward[$rank]['goods'][$goods_id]['spec'][$goods_spec]['total'] < 0) {
                                return false;
                            } else {
                                $rec_reward = serialize($rec_reward);
                                $update_data = array(
                                    'reward_data' => $rec_reward
                                );
                                $update_where = array(
                                    'join_id' => $param['join_id']
                                );
                                $res = pdo_update('ewei_shop_task_join', $update_data, $update_where);
                                if ($res) {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        } else {
                            $rec_reward[$rank]['goods'][$goods_id]['total'] -= $goods_num;
                            if ($rec_reward[$rank]['goods'][$goods_id]['total'] < 0) {
                                return false;
                            } else {
                                $rec_reward = serialize($rec_reward);
                                $update_data = array(
                                    'reward_data' => $rec_reward
                                );
                                $update_where = array(
                                    'join_id' => $param['join_id']
                                );
                                $res = pdo_update('ewei_shop_task_join', $update_data, $update_where);
                                if ($res) {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }

            }
        } else {
            //取商品信息
            if ($join_info['task_type'] == 1) {
                //普通海报
                $rec_reward = unserialize($join_info['reward_data']);
                if (!empty($rec_reward)) {
                    $goods_id = intval($param['goods_id']);
                    if (isset($rec_reward['goods'][$goods_id]) && !empty($rec_reward['goods'][$goods_id])) {
                        $createtime_sql = 'SELECT `createtime` FROM ' . tablename('ewei_shop_task_log') . ' WHERE openid= :openid AND uniacid = :uniacid AND `join_id`=:join_id  AND (recdata IS NOT NULL AND recdata !="") ';
                        $createtime_data = array(
                            ':uniacid' => $_W['uniacid'],
                            ':openid' => $param['openid'],
                            ':join_id' => $param['join_id']
                        );
                        $createtime = pdo_fetchcolumn($createtime_sql, $createtime_data);

                        $rewardday_sql = 'SELECT `reward_days`,`is_goods` FROM ' . tablename('ewei_shop_task_poster') . ' WHERE  uniacid = :uniacid AND `id`=:id  AND poster_type=:poster_type ';
                        $rewardday_data = array(
                            ':uniacid' => $_W['uniacid'],
                            ':id' => $join_info['task_id'],
                            ':poster_type' => $join_info['task_type']
                        );
                        $reward_days = pdo_fetch($rewardday_sql, $rewardday_data);
                        //奖励过期时间
                        if ($reward_days['reward_days'] > 0) {
                            $reward_day = $createtime + $reward_days['reward_days'];
                        } else {
                            //是否分销
//                            $rec_reward['goods'][$goods_id]['']
                            return $rec_reward['goods'][$goods_id];
                        }

                        if ($reward_day > time()) {
                            //是否分销
//                            $rec_reward['goods'][$goods_id]['']
                            return $rec_reward['goods'][$goods_id];
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } elseif ($join_info['task_type'] == 2) {
                //多级海报
                $rec_reward = unserialize($join_info['reward_data']);
                if (!isset($param['rank']) || empty($param['rank'])) {
                    return false;
                }
                $rank = intval($param['rank']);

                if (!empty($rec_reward)) {

                    $goods_id = intval($param['goods_id']);
                    if (isset($rec_reward[$rank]['goods'][$goods_id]) && !empty($rec_reward[$rank]['goods'][$goods_id])) {

                        $rewardday_sql = 'SELECT `reward_days`,`is_goods` FROM ' . tablename('ewei_shop_task_poster') . ' WHERE  uniacid = :uniacid AND `id`=:id  AND poster_type=:poster_type ';
                        $rewardday_data = array(
                            ':uniacid' => $_W['uniacid'],
                            ':id' => $join_info['task_id'],
                            ':poster_type' => $join_info['task_type']
                        );
                        $reward_days = pdo_fetch($rewardday_sql, $rewardday_data);
                        //奖励过期时间
                        if ($reward_days['reward_days'] > 0) {
                            $reward_day = $rec_reward[$rank]['reward_time'] + $reward_days['reward_days'];
                        } else {
                            //是否分销
//                            $rec_reward['goods'][$goods_id]['']
                            return $rec_reward[$rank]['goods'][$goods_id];
                        }
                        if ($reward_day > time()) {
                            //是否分销
//                            $rec_reward['goods'][$goods_id]['']
                            return $rec_reward[$rank]['goods'][$goods_id];
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
    }

    //普通海报奖励
    public function reward($member_info, $poster, $join_info, $qr, $openid, $qrmember)
    {
        if (empty($member_info) || empty($poster) || empty($join_info) || empty($openid) || empty($qr)) {
            return false;
        }
        global $_W;
        if (empty($poster['autoposter'])) {
            $_SESSION['postercontent'] = null;
        } else {
            $_SESSION['postercontent'] = $poster['keyword'];
//            $content = trim($_SESSION['postercontent']);
//            $timeout = 10;
//            $url = mobileUrl('task/build',array('timestamp'=>TIMESTAMP),true);
//            ihttp_request($url, array('openid' => $_W['openid'], 'content' => urlencode($content)), array(), $timeout);
        }

        load()->func('logging');
        //载入日志函数

        $reward_data = unserialize($poster['reward_data']);
        $count = $join_info['completecount'] + 1;
        if ($join_info['needcount'] == $count && $join_info['is_reward'] == 0) {

            //更新任务推广人数
            $reward = serialize($reward_data['rec']);
            $sub_reward = serialize($reward_data['sub']);
            //奖励双方（推荐人和扫描人）并发送奖励通知
            $reward_log = array(
                'uniacid' => $_W['uniacid'],
                'openid' => $qr['openid'],
                'from_openid' => $openid,
                'join_id' => $join_info['join_id'],
                'taskid' => $qr['posterid'],
                'task_type' => 1,
                'subdata' => $sub_reward,
                'recdata' => $reward,
                'createtime' => time()
            );
            //更新任务进度
            pdo_update('ewei_shop_task_join', array('completecount' => $count, 'is_reward' => 1, 'reward_data' => $reward), array('uniacid' => $_W['uniacid'], 'join_id' => $join_info['join_id'], 'join_user' => $qr['openid'], 'task_id' => $poster['id'], 'task_type' => 1));
            //插入日志
            pdo_insert('ewei_shop_task_log', $reward_log);
            $log_id = pdo_insertid();
            //插入扫描者
            $scaner = array(
                'uniacid' => $_W['uniacid'],
                'task_user' => $qr['openid'],
                'joiner_id' => $openid,
                'task_id' => $qr['posterid'],
                'join_id' => $join_info['join_id'],
                'task_type' => 1,
                'join_status' => 1,
                'addtime' => time()
            );
            pdo_insert('ewei_shop_task_joiner', $scaner);
            foreach ($reward_data as $key => $val) {
                if ($key == 'rec') {
                    //积分
                    if (isset($val['credit']) && $val['credit'] > 0) {
                        m('member')->setCredit($qr['openid'], 'credit1', $val['credit'], array(0, '推荐扫码关注积分+' . $val['credit']));
                    }
                    //现金
                    if (isset($val['money']) && $val['money']['num'] > 0) {
                        // $val['money']['type'] 0:余额1：微信
                        $pay = $val['money']['num'];
                        if ($val['money']['type'] == 1) {
                            $pay *= 100;
                        }
                        m('finance')->pay($qr['openid'], $val['money']['type'], $pay, '', '任务活动推荐奖励', false);
                    }
                    //红包
                    if (isset($val['bribery']) && $val['bribery'] > 0) {

                        //红包参数
                        $tid = rand(1, 1000) . time() . rand(1, 10000);//订单编号
                        $params = array(
                            'openid' => $qr['openid'],
                            'tid' => $tid,
                            'send_name' => '推荐奖励',
                            'money' => $val['bribery'],
                            'wishing' => '推荐奖励',
                            'act_name' => $poster['title'],
                            'remark' => '推荐奖励',
                        );
                        $err = m('common')->sendredpack($params);
                        if (!is_error($err)) {
                            $reward = unserialize($reward);
                            $reward['briberyOrder'] = $tid;
                            $reward = serialize($reward);
                            $upgrade = array(
                                'recdata' => $reward
                            );
                            pdo_update('ewei_shop_task_log', $upgrade, array('id' => $log_id));
                        }
                    }
                    //优惠券
                    if (isset($val['coupon']) && !empty($val['coupon'])) {
                        //赠送优惠券
                        $cansendreccoupon = false;
                        $plugin_coupon = com('coupon');
                        unset($val['coupon']['total']);
                        foreach ($val['coupon'] as $k => $v) {
                            if ($plugin_coupon) {
                                //推荐者奖励
                                if (!empty($v['id']) && $v['couponnum'] > 0) {
                                    $reccoupon = $plugin_coupon->getCoupon($v['id']);
                                    if (!empty($reccoupon)) {
                                        $cansendreccoupon = true;
                                    }
                                }
                            }

                            //优惠券通知
                            if ($cansendreccoupon) {
                                //发送优惠券
                                $plugin_coupon->taskposter($qrmember, $v['id'], $v['couponnum']);
                            }
                        }
                    }
                    //指定价格商品
//                    if(isset($val['goods'])&&!empty($val['goods'])){
//                    }
                } elseif ($key == 'sub') {
                    //积分
                    if ($val['credit'] > 0) {
                        m('member')->setCredit($openid, 'credit1', $val['credit'], array(0, '扫码关注积分+' . $val['credit']));
                    }
                    //红包
                    if ($val['bribery'] > 0) {

                        //红包参数
                        $tid = rand(1, 1000) . time() . rand(1, 10000);//订单编号
                        $params = array(
                            'openid' => $openid,
                            'tid' => $tid,
                            'send_name' => '推荐奖励',
                            'money' => $val['bribery'],
                            'wishing' => '推荐奖励',
                            'act_name' => $poster['title'],
                            'remark' => '推荐奖励',
                        );
                        $err = m('common')->sendredpack($params);
                        if (!is_error($err)) {
                            $sub_reward = unserialize($sub_reward);
                            $sub_reward['briberyOrder'] = $tid;
                            $sub_reward = serialize($sub_reward);
                            $upgrade = array(
                                'subdata' => $sub_reward
                            );
                            pdo_update('ewei_shop_task_log', $upgrade, array('id' => $log_id));
                        } else {
                            logging_run('bribery' . $err['message']);
                        }
                    }
                    //现金
                    if ($val['money']['num'] > 0) {
                        // $val['money']['type'] 0:余额1：微信
                        $pay = $val['money']['num'];
                        if ($val['money']['type'] == 1) {
                            $pay *= 100;
                        }
                        $res = m('finance')->pay($openid, $val['money']['type'], $pay, '', '任务活动奖励', false);
                        if (is_error($res)) {
                            logging_run($res['message']);
                        }
                    }

                    //优惠券
                    if (isset($val['coupon']) && !empty($val['coupon'])) {
                        //赠送优惠券
                        $cansendreccoupon = false;
                        $plugin_coupon = com('coupon');
                        unset($val['coupon']['total']);
                        foreach ($val['coupon'] as $k => $v) {
                            if ($plugin_coupon) {
                                //推荐者奖励
                                if (!empty($v['id']) && $v['couponnum'] > 0) {
                                    $reccoupon = $plugin_coupon->getCoupon($v['id']);
                                    if (!empty($reccoupon)) {
                                        $cansendreccoupon = true;
                                    }
                                }
                            }

                            //推荐人奖励通知
                            if ($cansendreccoupon) {
                                //发送优惠券
                                $plugin_coupon->taskposter($member_info, $v['id'], $v['couponnum']);
                            }
                        }
                    }
                    //指定价格商品
//                    if(isset($val['goods'])&&!empty($val['goods'])){
//                    }
                }
            }
            //推送通知
            $default_text = pdo_fetchcolumn("SELECT `data` FROM " . tablename('ewei_shop_task_default') . " WHERE uniacid=:uniacid limit 1", array(':uniacid' => $_W['uniacid']));

            if (!empty($default_text)) {
                $default_text = unserialize($default_text);
                //扫描人通知
                if (!empty($default_text['successscaner'])) {
                    $poster['okdays'] = $join_info['failtime'];
                    $poster['completecount'] = $join_info['completecount'];
                    foreach ($default_text['successscaner'] as $key => $val) {
                        $default_text['successscaner'][$key]['value'] = $this->notice_complain($val['value'], $qrmember, $poster, $member_info, 1);
                    }
                    if ($default_text['templateid']) {
                        m('message')->sendTplNotice($openid, $default_text['templateid'], $default_text['successscaner'], '');
                    } else {
                        m('message')->sendCustomNotice($openid, '感谢您的关注，恭喜您获得关注奖励');
                    }
                } else {
                    m('message')->sendCustomNotice($openid, '感谢您的关注，恭喜您获得关注奖励');
                }

                //任务人通知
                if (!empty($default_text['complete'])) {
                    $poster['okdays'] = $join_info['failtime'];
                    $poster['completecount'] = $count;
                    foreach ($default_text['complete'] as $key => $val) {
                        $default_text['complete'][$key]['value'] = $this->notice_complain($val['value'], $qrmember, $poster, $member_info, 2);
                    }
                    if ($default_text['templateid']) {
                        m('message')->sendTplNotice($qrmember['openid'], $default_text['templateid'], $default_text['complete'], mobileUrl('task', array('tabpage' => 'complete'), true));
                    } else {
                        m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '恭喜您完成任务获得奖励', mobileUrl('task', array('tabpage' => 'complete'), true));
                    }
                } else {
                    m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '恭喜您完成任务获得奖励', mobileUrl('task', array('tabpage' => 'complete'), true));
                }
            } else {
                m('message')->sendCustomNotice($openid, '感谢您的关注，恭喜您获得关注奖励');
                m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '恭喜您完成任务获得奖励', mobileUrl('task', array('tabpage' => 'complete'), true));
            }
            if (p('lottery')) {
                //type 1:消费 2:签到 3:任务 4:其他
                $res = p('lottery')->getLottery($qrmember['openid'], 3, array('taskid' => $poster['id']));
                if ($res) {
                    p('lottery')->getLotteryList($qrmember['openid'], array('lottery_id' => $res));
                }
            }
        } else {
            //奖励扫描人，发送奖励通知给双方
            $reward = serialize($reward_data['rec']);
            $sub_reward = serialize($reward_data['sub']);
            $reward_log = array(
                'uniacid' => $_W['uniacid'],
                'openid' => $qr['openid'],
                'from_openid' => $openid,
                'join_id' => $join_info['join_id'],
                'taskid' => $qr['posterid'],
                'task_type' => 1,
                'subdata' => $sub_reward,
                'createtime' => time()
            );
            //更新任务进度
            pdo_update('ewei_shop_task_join', array('completecount' => $count), array('uniacid' => $_W['uniacid'], 'join_user' => $qr['openid'], 'task_id' => $poster['id'], 'task_type' => 1));
            //插入日志
            pdo_insert('ewei_shop_task_log', $reward_log);
            $log_id = pdo_insertid();
            //插入扫描者
            $scaner = array(
                'uniacid' => $_W['uniacid'],
                'task_user' => $qr['openid'],
                'joiner_id' => $openid,
                'task_id' => $qr['posterid'],
                'join_id' => $join_info['join_id'],
                'task_type' => 1,
                'join_status' => 1,
                'addtime' => time()
            );
            pdo_insert('ewei_shop_task_joiner', $scaner);
            foreach ($reward_data as $key => $val) {
                //至奖励扫描者
                if ($key == 'sub') {
                    //积分
                    if ($val['credit'] > 0) {
                        m('member')->setCredit($openid, 'credit1', $val['credit'], array(0, '扫码关注积分+' . $val['credit']));
                    }
                    //现金
                    if ($val['money']['num'] > 0) {
                        // $val['money']['type'] 0:余额1：微信
                        $pay = $val['money']['num'];
                        if ($val['money']['type'] == 1) {
                            $pay *= 100;
                        }
                        $res = m('finance')->pay($openid, $val['money']['type'], $pay, '', '任务活动奖励', false);
                        if (is_error($res)) {
                            logging_run('submoney' . $res['message']);
                        }
                    }
                    //红包
                    if ($val['bribery'] > 0) {
                        //红包参数
                        $tid = rand(1, 1000) . time() . rand(1, 10000);//订单编号
                        $params = array(
                            'openid' => $openid,
                            'tid' => $tid,
                            'send_name' => '推荐奖励',
                            'money' => $val['bribery'],
                            'wishing' => '推荐奖励',
                            'act_name' => $poster['title'],
                            'remark' => '推荐奖励',
                        );
                        $err = m('common')->sendredpack($params);
                        if (!is_error($err)) {
                            $sub_reward = unserialize($sub_reward);
                            $sub_reward['briberyOrder'] = $tid;
                            $sub_reward = serialize($sub_reward);
                            $upgrade = array(
                                'subdata' => $sub_reward
                            );
                            pdo_update('ewei_shop_task_log', $upgrade, array('id' => $log_id));
                        } else {
                            logging_run('bribery' . $err['message']);
                        }
                    }
                    //优惠券
                    if (isset($val['coupon']) && !empty($val['coupon'])) {
                        //赠送优惠券
                        $cansendreccoupon = false;
                        $plugin_coupon = com('coupon');
                        unset($val['coupon']['total']);
                        foreach ($val['coupon'] as $k => $v) {
                            if ($plugin_coupon) {
                                //推荐者奖励
                                $cansendreccoupon = false;
                                if (!empty($v['id']) && $v['couponnum'] > 0) {
                                    $reccoupon = $plugin_coupon->getCoupon($v['id']);
                                    if (!empty($reccoupon)) {
                                        $cansendreccoupon = true;
                                    }
                                }
                            }

                            //推荐人奖励通知
                            if ($cansendreccoupon) {
                                //发送优惠券
                                $plugin_coupon->taskposter($member_info, $v['id'], $v['couponnum']);
                            }
                        }
                    }
                    //指定价格商品
//                    if(isset($val['goods'])&&!empty($val['goods'])){
//                    }

                }
            }
            //推送通知
            $default_text = pdo_fetchcolumn("SELECT `data` FROM " . tablename('ewei_shop_task_default') . " WHERE uniacid=:uniacid limit 1", array(':uniacid' => $_W['uniacid']));
            if (!empty($default_text)) {
                $default_text = unserialize($default_text);
                //扫描人通知
                if (!empty($default_text['successscaner'])) {
                    $poster['okdays'] = $join_info['failtime'];
                    $poster['completecount'] = $join_info['completecount'];
                    foreach ($default_text['successscaner'] as $key => $val) {
                        $default_text['successscaner'][$key]['value'] = $this->notice_complain($val['value'], $qrmember, $poster, $member_info, 1);
                    }
                    if ($default_text['templateid']) {
                        m('message')->sendTplNotice($openid, $default_text['templateid'], $default_text['successscaner'], '');
                    } else {
                        m('message')->sendCustomNotice($openid, '感谢您的关注，恭喜您获得关注奖励');
                    }
                } else {
                    m('message')->sendCustomNotice($openid, '感谢您的关注，恭喜您获得关注奖励');
                }
                if ($poster['needcount'] < $count) {
                    if ($default_text['is_completed'] == 1) {
                        if (!empty($default_text['completed'])) {
                            $poster['okdays'] = $join_info['failtime'];
                            $poster['completecount'] = $count;
                            foreach ($default_text['completed'] as $key => $val) {
                                $default_text['completed'][$key]['value'] = $this->notice_complain($val['value'], $qrmember, $poster, $member_info, 2);
                            }
                            if ($default_text['templateid']) {
                                m('message')->sendTplNotice($qrmember['openid'], $default_text['templateid'], $default_text['completed'], mobileUrl('task', array('tabpage' => 'complete'), true));
                            } else {
                                m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '恭喜您完成任务获得奖励', mobileUrl('task', array('tabpage' => 'complete'), true));
                            }
                        } else {
                            m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '恭喜您完成任务获得奖励', mobileUrl('task', array('tabpage' => 'complete'), true));
                        }
                    }
                } else {
                    //任务人通知
                    if (!empty($default_text['successtasker'])) {
                        $poster['okdays'] = $join_info['failtime'];
                        $poster['completecount'] = $count;
                        foreach ($default_text['successtasker'] as $key => $val) {
                            $default_text['successtasker'][$key]['value'] = $this->notice_complain($val['value'], $qrmember, $poster, $member_info, 2);
                        }
                        if ($default_text['templateid']) {
                            m('message')->sendTplNotice($qrmember['openid'], $default_text['templateid'], $default_text['successtasker'], mobileUrl('task', array('tabpage' => 'runninga'), true));
                        } else {
                            m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '您的海报被' . $member_info['nickname'] . '关注,增加了1点人气值', mobileUrl('task', array('tabpage' => 'runninga'), true));
                        }

                    } else {
                        m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '您的海报被' . $member_info['nickname'] . '关注,增加了1点人气值', mobileUrl('task', array('tabpage' => 'runninga'), true));
                    }
                }
            } else {
                m('message')->sendCustomNotice($openid, '感谢您的关注，恭喜您获得关注奖励');
                m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '您的海报被' . $member_info['nickname'] . '关注,增加了1点人气值', mobileUrl('task', array('tabpage' => 'runninga'), true));
            }
        }
    }

    //多级海报奖励
    public function rankreward($member_info, $poster, $join_info, $qr, $openid, $qrmember)
    {
        if (empty($member_info) || empty($poster) || empty($join_info) || empty($openid) || empty($qr)) {
            return false;
        }
        global $_W;
        if (empty($poster['autoposter'])) {
            $_SESSION['postercontent'] = null;
        } else {
            $_SESSION['postercontent'] = $poster['keyword'];
        }
        //载入日志函数
        $reward_data = unserialize($poster['reward_data']);
        $rec_data = unserialize($join_info['reward_data']);
        $count = $join_info['completecount'] + 1;
        $is_reward = 0;
        $needcount = 0;
        foreach ($rec_data as $k => $val) {
            $needcount = $val['needcount'];
            if ($val['needcount'] == $count) {
                if ($is_reward == 0) {
                    $is_reward = 1;
                    //奖励双方
                    if (!isset($val['is_reward']) || empty($val['is_reward'])) {
                        unset($val['rank']);
                        unset($val['needcount']);
                        $reward_data['rec'] = $reward_data['rec'][$k];
                        $poster['reward_rank'] = $k;
                        $this->reward_both($count, $reward_data, $qr, $join_info, $openid, $qrmember, $member_info, $poster);
                        $rec_data[$k] = $reward_data['rec'];
                        $rec_data[$k]['is_reward'] = 1;
                        $rec_data[$k]['reward_time'] = time();
                        $rec_data = serialize($rec_data);
                        pdo_update('ewei_shop_task_join', array('reward_data' => $rec_data, 'is_reward' => 1), array('uniacid' => $_W['uniacid'], 'join_id' => $join_info['join_id'], 'join_user' => $qr['openid'], 'task_id' => $poster['id'], 'task_type' => 2));
                    } else {
                        $poster['needcount'] = $needcount;
                        $this->reward_scan($count, $reward_data, $qr, $join_info, $openid, $qrmember, $member_info, $poster);
                    }
                }
            }
        }
        if ($is_reward == 0) {
            $is_reward = 1;
            //奖励扫描人
            $poster['needcount'] = $needcount;
            $this->reward_scan($count, $reward_data, $qr, $join_info, $openid, $qrmember, $member_info, $poster);
        }
    }

    //奖励双方
    protected function reward_both($count, $reward_data, $qr, $join_info, $openid, $qrmember, $member_info, $poster)
    {

        global $_W;
        load()->func('logging');
        //更新任务推广人数
        $reward = serialize($reward_data['rec']);
        $sub_reward = serialize($reward_data['sub']);
        //奖励双方（推荐人和扫描人）并发送奖励通知
        $reward_log = array(
            'uniacid' => $_W['uniacid'],
            'openid' => $qr['openid'],
            'from_openid' => $openid,
            'join_id' => $join_info['join_id'],
            'taskid' => $qr['posterid'],
            'task_type' => 2,
            'subdata' => $sub_reward,
            'recdata' => $reward,
            'createtime' => time()
        );
        //更新任务进度
        pdo_update('ewei_shop_task_join', array('completecount' => $count), array('uniacid' => $_W['uniacid'], 'join_id' => $join_info['join_id'], 'join_user' => $qr['openid'], 'task_id' => $poster['id'], 'task_type' => 2));
        //插入日志
        pdo_insert('ewei_shop_task_log', $reward_log);
        $log_id = pdo_insertid();
        //插入扫描者
        $scaner = array(
            'uniacid' => $_W['uniacid'],
            'task_user' => $qr['openid'],
            'joiner_id' => $openid,
            'task_id' => $qr['posterid'],
            'join_id' => $join_info['join_id'],
            'task_type' => 2,
            'join_status' => 1,
            'addtime' => time()
        );
        pdo_insert('ewei_shop_task_joiner', $scaner);
        foreach ($reward_data as $key => $val) {
            if ($key == 'rec') {
                //积分
                if (isset($val['credit']) && $val['credit'] > 0) {
                    m('member')->setCredit($qr['openid'], 'credit1', $val['credit'], array(0, '推荐扫码关注积分+' . $val['credit']));
                }
                //现金
                if (isset($val['money']) && $val['money']['num'] > 0) {
                    // $val['money']['type'] 0:余额1：微信
                    $pay = $val['money']['num'];
                    if ($val['money']['type'] == 1) {
                        $pay *= 100;
                    }
                    m('finance')->pay($qr['openid'], $val['money']['type'], $pay, '', '任务活动推荐奖励', false);
                }
                //红包
                if (isset($val['bribery']) && $val['bribery'] > 0) {

                    //红包参数
                    $tid = rand(1, 1000) . time() . rand(1, 10000);//订单编号
                    $params = array(
                        'openid' => $qr['openid'],
                        'tid' => $tid,
                        'send_name' => '推荐奖励',
                        'money' => $val['bribery'],
                        'wishing' => '推荐奖励',
                        'act_name' => $poster['title'],
                        'remark' => '推荐奖励',
                    );
                    $err = m('common')->sendredpack($params);
                    if (!is_error($err)) {
                        $reward = unserialize($reward);
                        $reward['briberyOrder'] = $tid;
                        $reward = serialize($reward);
                        $upgrade = array(
                            'recdata' => $reward
                        );
                        pdo_update('ewei_shop_task_log', $upgrade, array('id' => $log_id));
                    }
                }
                //优惠券
                if (isset($val['coupon']) && !empty($val['coupon'])) {
                    //赠送优惠券
                    $cansendreccoupon = false;
                    $plugin_coupon = com('coupon');
                    unset($val['coupon']['total']);
                    foreach ($val['coupon'] as $k => $v) {
                        if ($plugin_coupon) {
                            //推荐者奖励
                            if (!empty($v['id']) && $v['couponnum'] > 0) {
                                $reccoupon = $plugin_coupon->getCoupon($v['id']);
                                if (!empty($reccoupon)) {
                                    $cansendreccoupon = true;
                                }
                            }
                        }

                        //优惠券通知
                        if ($cansendreccoupon) {
                            //发送优惠券
                            $plugin_coupon->taskposter($qrmember, $v['id'], $v['couponnum']);
                        }
                    }
                }
                //指定价格商品
//                    if(isset($val['goods'])&&!empty($val['goods'])){
//                    }
            } elseif ($key == 'sub') {
                //积分
                if ($val['credit'] > 0) {
                    m('member')->setCredit($openid, 'credit1', $val['credit'], array(0, '扫码关注积分+' . $val['credit']));
                }
                //现金
                if ($val['money']['num'] > 0) {
                    // $val['money']['type'] 0:余额1：微信
                    $pay = $val['money']['num'];
                    if ($val['money']['type'] == 1) {
                        $pay *= 100;
                    }
                    $res = m('finance')->pay($openid, $val['money']['type'], $pay, '', '任务活动奖励', false);
                    if (is_error($res)) {
                        logging_run($res['message']);
                    }
                }

                //优惠券
                if (isset($val['coupon']) && !empty($val['coupon'])) {
                    //赠送优惠券
                    $cansendreccoupon = false;
                    $plugin_coupon = com('coupon');
                    unset($val['coupon']['total']);
                    foreach ($val['coupon'] as $k => $v) {
                        if ($plugin_coupon) {
                            //推荐者奖励
                            if (!empty($v['id']) && $v['couponnum'] > 0) {
                                $reccoupon = $plugin_coupon->getCoupon($v['id']);
                                if (!empty($reccoupon)) {
                                    $cansendreccoupon = true;
                                }
                            }
                        }

                        //推荐人奖励通知
                        if ($cansendreccoupon) {
                            //发送优惠券
                            $plugin_coupon->taskposter($member_info, $v['id'], $v['couponnum']);
                        }
                    }
                }
                //指定价格商品
//                    if(isset($val['goods'])&&!empty($val['goods'])){
//                    }
            }
        }
        //推送通知
        $default_text = pdo_fetchcolumn("SELECT `data` FROM " . tablename('ewei_shop_task_default') . " WHERE uniacid=:uniacid limit 1", array(':uniacid' => $_W['uniacid']));

        if (!empty($default_text)) {
            $default_text = unserialize($default_text);
            //扫描人通知
            if (!empty($default_text['successscaner'])) {
                $poster['okdays'] = $join_info['failtime'];
                $poster['completecount'] = $join_info['completecount'];
                foreach ($default_text['successscaner'] as $key => $val) {
                    $default_text['successscaner'][$key]['value'] = $this->notice_complain($val['value'], $qrmember, $poster, $member_info, 1);
                }
                if ($default_text['templateid']) {
                    m('message')->sendTplNotice($openid, $default_text['templateid'], $default_text['successscaner'], '');
                } else {
                    m('message')->sendCustomNotice($openid, '感谢您的关注，恭喜您获得关注奖励');
                }
            } else {
                m('message')->sendCustomNotice($openid, '感谢您的关注，恭喜您获得关注奖励');
            }

            //任务人通知
            if (!empty($default_text['rankcomplete'])) {
                $poster['okdays'] = $join_info['failtime'];
                $poster['completecount'] = $count;
                $poster['needcount'] = $count;
                foreach ($default_text['rankcomplete'] as $key => $val) {
                    $default_text['rankcomplete'][$key]['value'] = $this->notice_complain($val['value'], $qrmember, $poster, $member_info, 2);
                }
                if ($default_text['templateid']) {
                    m('message')->sendTplNotice($qrmember['openid'], $default_text['templateid'], $default_text['rankcomplete'], mobileUrl('task/mytask', array('id' => $join_info['join_id']), true));
                } else {
                    m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '恭喜您完成任务获得奖励', mobileUrl('task/mytask', array('id' => $join_info['join_id']), true));
                }
            } else {
                m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '恭喜您完成任务获得奖励', mobileUrl('task/mytask', array('id' => $join_info['join_id']), true));
            }
        } else {
            m('message')->sendCustomNotice($openid, '感谢您的关注，恭喜您获得关注奖励');
            m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '恭喜您完成任务获得奖励', mobileUrl('task/mytask', array('id' => $join_info['join_id']), true));
        }
        if (p('lottery')) {
            //type 1:消费 2:签到 3:任务 4:其他
            $res = p('lottery')->getLottery($qrmember['openid'], 3, array('taskid' => $poster['id']));
            if ($res) {
                p('lottery')->getLotteryList($qrmember['openid'], array('lottery_id' => $res));
            }
        }
    }

    //奖励扫描人
    protected function reward_scan($count, $reward_data, $qr, $join_info, $openid, $qrmember, $member_info, $poster)
    {
        global $_W;
        load()->func('logging');
        $sub_reward = serialize($reward_data['sub']);
        $reward_log = array(
            'uniacid' => $_W['uniacid'],
            'openid' => $qr['openid'],
            'from_openid' => $openid,
            'join_id' => $join_info['join_id'],
            'taskid' => $qr['posterid'],
            'task_type' => 2,
            'subdata' => $sub_reward,
            'createtime' => time()
        );
        //更新任务进度
        pdo_update('ewei_shop_task_join', array('completecount' => $count), array('uniacid' => $_W['uniacid'], 'join_user' => $qr['openid'], 'task_id' => $poster['id'], 'task_type' => 2));
        //插入日志
        pdo_insert('ewei_shop_task_log', $reward_log);
        $log_id = pdo_insertid();
        //插入扫描者
        $scaner = array(
            'uniacid' => $_W['uniacid'],
            'task_user' => $qr['openid'],
            'joiner_id' => $openid,
            'task_id' => $qr['posterid'],
            'join_id' => $join_info['join_id'],
            'task_type' => 2,
            'join_status' => 1,
            'addtime' => time()
        );
        pdo_insert('ewei_shop_task_joiner', $scaner);
        foreach ($reward_data as $key => $val) {
            //至奖励扫描者
            if ($key == 'sub') {
                //积分
                if ($val['credit'] > 0) {
                    m('member')->setCredit($openid, 'credit1', $val['credit'], array(0, '扫码关注积分+' . $val['credit']));
                }
                //现金
                if ($val['money']['num'] > 0) {
                    // $val['money']['type'] 0:余额1：微信
                    $pay = $val['money']['num'];
                    if ($val['money']['type'] == 1) {
                        $pay *= 100;
                    }
                    $res = m('finance')->pay($openid, $val['money']['type'], $pay, '', '任务活动奖励', false);
                    if (is_error($res)) {
                        logging_run('submoney' . $res['message']);
                    }
                }
                //红包
                if ($val['bribery'] > 0) {

                    //红包参数
                    $tid = rand(1, 1000) . time() . rand(1, 10000);//订单编号
                    $params = array(
                        'openid' => $openid,
                        'tid' => $tid,
                        'send_name' => '推荐奖励',
                        'money' => $val['bribery'],
                        'wishing' => '推荐奖励',
                        'act_name' => $poster['title'],
                        'remark' => '推荐奖励',
                    );
                    $err = m('common')->sendredpack($params);
                    if (!is_error($err)) {
                        $sub_reward = unserialize($sub_reward);
                        $sub_reward['briberyOrder'] = $tid;
                        $sub_reward = serialize($sub_reward);
                        $upgrade = array(
                            'subdata' => $sub_reward
                        );
                        pdo_update('ewei_shop_task_log', $upgrade, array('id' => $log_id));
                    } else {
                        logging_run('bribery' . $err['message']);
                    }
                }
                //优惠券
                if (isset($val['coupon']) && !empty($val['coupon'])) {
                    //赠送优惠券
                    $cansendreccoupon = false;
                    $plugin_coupon = com('coupon');
                    unset($val['coupon']['total']);
                    foreach ($val['coupon'] as $k => $v) {
                        if ($plugin_coupon) {
                            //推荐者奖励
                            $cansendreccoupon = false;
                            if (!empty($v['id']) && $v['couponnum'] > 0) {
                                $reccoupon = $plugin_coupon->getCoupon($v['id']);
                                if (!empty($reccoupon)) {
                                    $cansendreccoupon = true;
                                }
                            }
                        }

                        //推荐人奖励通知
                        if ($cansendreccoupon) {
                            //发送优惠券
                            $plugin_coupon->taskposter($member_info, $v['id'], $v['couponnum']);
                        }
                    }
                }
                //指定价格商品
//                    if(isset($val['goods'])&&!empty($val['goods'])){
//                    }

            }
        }
        //推送通知
        $default_text = pdo_fetchcolumn("SELECT `data` FROM " . tablename('ewei_shop_task_default') . " WHERE uniacid=:uniacid limit 1", array(':uniacid' => $_W['uniacid']));
        if (!empty($default_text)) {
            $default_text = unserialize($default_text);
            //扫描人通知
            if (!empty($default_text['successscaner'])) {
                $poster['okdays'] = $join_info['failtime'];
                $poster['completecount'] = $join_info['completecount'];
                foreach ($default_text['successscaner'] as $key => $val) {
                    $default_text['successscaner'][$key]['value'] = $this->notice_complain($val['value'], $qrmember, $poster, $member_info, 1);
                }
                if ($default_text['templateid']) {
                    m('message')->sendTplNotice($openid, $default_text['templateid'], $default_text['successscaner'], '');
                } else {
                    m('message')->sendCustomNotice($openid, '感谢您的关注，恭喜您获得关注奖励');
                }
            } else {
                m('message')->sendCustomNotice($openid, '感谢您的关注，恭喜您获得关注奖励');
            }
            if ($poster['needcount'] < $count) {
                if ($default_text['is_completed'] == 1) {
                    if (!empty($default_text['completed'])) {
                        $poster['okdays'] = $join_info['failtime'];
                        $poster['completecount'] = $count;
                        foreach ($default_text['completed'] as $key => $val) {
                            $default_text['completed'][$key]['value'] = $this->notice_complain($val['value'], $qrmember, $poster, $member_info, 2);
                        }
                        if ($default_text['templateid']) {
                            m('message')->sendTplNotice($qrmember['openid'], $default_text['templateid'], $default_text['completed'], mobileUrl('task', array('tabpage' => 'complete'), true));
                        } else {
                            m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '恭喜您完成任务获得奖励', mobileUrl('task', array('tabpage' => 'complete'), true));
                        }
                    } else {
                        m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '恭喜您完成任务获得奖励', mobileUrl('task', array('tabpage' => 'complete'), true));
                    }
                }
            } else {
                //任务人通知
                if (!empty($default_text['successtasker'])) {
                    $poster['okdays'] = $join_info['failtime'];
                    $poster['completecount'] = $count;
                    foreach ($default_text['successtasker'] as $key => $val) {
                        $default_text['successtasker'][$key]['value'] = $this->notice_complain($val['value'], $qrmember, $poster, $member_info, 2);
                    }
                    if ($default_text['templateid']) {
                        m('message')->sendTplNotice($qrmember['openid'], $default_text['templateid'], $default_text['successtasker'], mobileUrl('task', array('tabpage' => 'runninga'), true));
                    } else {
                        m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '您的海报被' . $member_info['nickname'] . '关注,增加了1点人气值', mobileUrl('task', array('tabpage' => 'runninga'), true));
                    }

                } else {
                    m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '您的海报被' . $member_info['nickname'] . '关注,增加了1点人气值', mobileUrl('task', array('tabpage' => 'runninga'), true));
                }
            }
        } else {
            m('message')->sendCustomNotice($openid, '感谢您的关注，恭喜您获得关注奖励');
            m('message')->sendCustomNotice($qrmember['openid'], '亲爱的' . $qrmember['nickname'] . '您的海报被' . $member_info['nickname'] . '关注,增加了1点人气值', mobileUrl('task', array('tabpage' => 'runninga'), true));
        }
    }


    public $extension = '[{"id":"1","taskname":"\u63a8\u8350\u4eba\u6570","taskclass":"commission_member","status":"1","classify":"number","classify_name":"commission","verb":"\u63a8\u8350","unit":"\u4eba"},{"id":"2","taskname":"\u5206\u9500\u4f63\u91d1","taskclass":"commission_money","status":"1","classify":"number","classify_name":"commission","verb":"\u8fbe\u5230","unit":"\u5143"},{"id":"3","taskname":"\u5206\u9500\u8ba2\u5355","taskclass":"commission_order","status":"1","classify":"number","classify_name":"commission","verb":"\u8fbe\u5230","unit":"\u7b14"},{"id":"6","taskname":"\u8ba2\u5355\u6ee1\u989d","taskclass":"cost_enough","status":"1","classify":"number","classify_name":"cost","verb":"\u6ee1","unit":"\u5143"},{"id":"7","taskname":"\u7d2f\u8ba1\u91d1\u989d","taskclass":"cost_total","status":"1","classify":"number","classify_name":"cost","verb":"\u7d2f\u8ba1","unit":"\u5143"},{"id":"8","taskname":"\u8ba2\u5355\u6570\u91cf","taskclass":"cost_count","status":"1","classify":"number","classify_name":"cost","verb":"\u8fbe\u5230","unit":"\u5355"},{"id":"9","taskname":"\u6307\u5b9a\u5546\u54c1","taskclass":"cost_goods","status":"1","classify":"select","classify_name":"cost","verb":"\u8d2d\u4e70\u6307\u5b9a\u5546\u54c1","unit":"\u4ef6"},{"id":"10","taskname":"\u5546\u54c1\u8bc4\u4ef7","taskclass":"cost_comment","status":"1","classify":"number","classify_name":"cost","verb":"\u8bc4\u4ef7\u8ba2\u5355","unit":"\u6b21"},{"id":"11","taskname":"\u7d2f\u8ba1\u5145\u503c","taskclass":"cost_rechargetotal","status":"1","classify":"number","classify_name":"cost","verb":"\u8fbe\u5230","unit":"\u5143"},{"id":"12","taskname":"\u5145\u503c\u6ee1\u989d","taskclass":"cost_rechargeenough","status":"1","classify":"number","classify_name":"cost","verb":"\u6ee1","unit":"\u5143"},{"id":"13","taskname":"\u7ed1\u5b9a\u624b\u673a","taskclass":"member_info","status":"1","classify":"boole","classify_name":"member","verb":"\u7ed1\u5b9a\u624b\u673a\u53f7\uff08\u5fc5\u987b\u5f00\u542fwap\u6216\u5c0f\u7a0b\u5e8f\uff09","unit":""}]';

    /**
     * 返回全部指定状态的任务
     * @param int $status
     * @return array or false
     */
    function getAvailableTask($status = 1, $classify = true)
    {
        global $_W;
        $status = intval($status);
        $list = json_decode($this->extension, true);
        if (empty($list)) {
            return false;
        } elseif (empty($classify)) {
            return $list;
        }
        $return = array();
        foreach ($list as $ik => $item) {
            $return[$item['classify_name']][count($return[$item['classify_name']])] = $list[$ik];
        }
        return $return;
    }


    /**
     * 检查是否是可用任务
     * @param $taskclass
     * @return bool
     */
    function checkAvailableTask($taskclass)
    {
        global $_W;
        $tasks = json_decode($this->extension, true);
        foreach ($tasks as $key => $value) {
            if ($value['status'] == 1 && $value['taskclass'] == $taskclass) {
                return $value;
            }
        }
        return false;
    }


    /**
     * 检查任务是否已完成
     * 如果已经完成则发放奖励
     * 如果没有完成则返回boole值 false代表更新失败,
     */
    function checkTaskReward($taskclass = '', $num = 1, $openid = '')
    {
        global $_W;
        if (strpos('first', '1' . $taskclass)) {
            $this->firstTask . $taskclass($openid);
        }
        if (empty($openid)) $openid = $_W['openid'];
        if (empty($taskclass)) return false;
        //查询未完成的任务
        $sql = "SELECT * FROM " . tablename('ewei_shop_task_extension_join') .
            " WHERE openid = :openid AND uniacid = :uniacid AND completetime = 0 AND endtime > " . time();
        $allTask = pdo_fetchall($sql, array(':openid' => $openid, ':uniacid' => $_W['uniacid']));
        //遍历任务
        foreach ($allTask as $tk => $tv) {
            $a = $this->checktaskstatus($tv);
            if (!$a) continue;
            $require = unserialize($tv['require_data']);
            $progress = unserialize($tv['progress_data']);
            if (!array_key_exists($taskclass, $require)) continue;
            if (intval($progress[$taskclass]['num']) < intval($require[$taskclass]['num']))
                $progress[$taskclass]['num'] = intval($progress[$taskclass]['num']) + $num;
            $progress_data = serialize($progress);
            pdo_update('ewei_shop_task_extension_join', array('progress_data' => $progress_data), array('uniacid' => $_W['uniacid'], 'id' => $tv['id']));

            //进度
            foreach ($progress as $k => $v) {
                if ($v < $require[$k]) {
                    $isreward = false;
                    break;
                } else {
                    $isreward = true;
                }
            }
            if ($isreward) {
                pdo_update('ewei_shop_task_extension_join', array('completetime' => time()), array('uniacid' => $_W['uniacid'], 'id' => $tv['id']));
                $reward_data = unserialize($tv['reward_data']);
                $this->sendReward($reward_data, 0, $openid, $tv['id']);
            }
        }
        return true;
    }

    function firstTaskfirst_recharge($openid)
    {//首次充值
        global $_W;
        return 1;
    }

    function firstTaskfirst_order($openid)
    {//首次下单
        global $_W;
        return 1;
    }

    function checktaskstatus($task)
    {
        global $_W;
        $time = time();
        if ($task['endtime'] < $time || $task['completetime'] > 0) {
            return false;
        }
        return true;
    }


    function sendReward($reward_data = array(), $btn = 0, $openid = null, $rewardid = 0)
    {//发送奖励
        global $_W;
        if (empty($openid)) $openid = $_W['openid'];
        if (empty($rewardid)) return false;
        if (!$btn) {
            $data = array('balance' => $reward_data['balance'], 'score' => $reward_data['score'], 'coupon' => count($reward_data['coupon']));
            $this->sendmessage($data);
        }
        if (empty($reward_data)) return false;
        $rewarded = array();
        if (!empty($reward_data['balance'])) {
            //充值，如果失败则存入rewarded
            m('member')->setCredit($openid, 'credit2', $reward_data['balance'], array(0, '完成任务余额+' . $reward_data['balance']));
        }
        if (!empty($reward_data['score'])) {
            //充值，如果失败则存入rewarded
            m('member')->setCredit($openid, 'credit1', $reward_data['score'], array(0, '完成任务积分+' . $reward_data['score']));
        }
        if (!empty($reward_data['redpacket'])) {
            if ($btn) {
                //红包参数
                $tid = rand(1, 1000) . time() . rand(1, 10000);//订单编号
                $params = array(
                    'openid' => $openid,
                    'tid' => $tid,
                    'send_name' => '任务完成奖励',
                    'money' => floatval($reward_data['redpacket']),
                    'wishing' => '任务完成奖励',
                    'act_name' => '任务完成奖励',
                    'remark' => '任务完成奖励',
                );
                $err = m('common')->sendredpack($params);
                if (is_error($err)) {
                    $rewarded['redpacket'] = $reward_data['redpacket'];
                    show_json(0, $err['message']);
                }
            } else {
                $rewarded['redpacket'] = $reward_data['redpacket'];
            }
        }
        if (!empty($reward_data['coupon']) && is_array($reward_data['coupon'])) {
            //发优惠券
            foreach ($reward_data['coupon'] as $k => $v) {
                $data = array(
                    'uniacid' => $_W['uniacid'],
                    'merchid' => 0,
                    'openid' => $openid,
                    'couponid' => $v['id'],
                    'gettype' => 7,
                    'gettime' => time(),
                    'senduid' => $_W['uid'],
                );
                pdo_insert('ewei_shop_coupon_data', $data);
            }
        }
        if (!empty($reward_data['goods'])) {
            //商品和价格写到获奖记录中
            $rewarded['goods'] = $reward_data['goods'];
        }
        $rewarded = serialize($rewarded);
        pdo_update('ewei_shop_task_extension_join', array('rewarded' => $rewarded), array('id' => $rewardid, 'uniacid' => $_W['uniacid']));
    }

    function getNewTask($id)
    {//接任务
        global $_W;
        $openid = $_W['openid'];
        $member = m('member')->getInfo($openid);
        $nowtime = time();
        $sql = "SELECT * FROM " . tablename("ewei_shop_task") . " WHERE id = :id AND status = 1 AND starttime < {$nowtime} AND endtime >{$nowtime} AND uniacid = :uniacid";
        $task = pdo_fetch($sql, array(":id" => $id, ":uniacid" => $_W['uniacid']));
        if (empty($task)) {
            return '任务不存在';
        }
        $can = $this->taskFilter($task);//检测是否可以接任务
        if (is_string($can)) return $can;
        $data = array();
        $data['uniacid'] = $_W['uniacid'];
        $data['uid'] = $member['id'];
        $data['title'] = $task['title'];
        $data['taskid'] = $id;
        $data['openid'] = $_W['openid'];
        $progress = unserialize($task['require_data']);
        foreach ($progress as $p => $v) {
            $progress[$p]['num'] = 0;
        }
        $progress = serialize($progress);
        $data['progress_data'] = $progress;
        $data['require_data'] = $task['require_data'];
        $data['reward_data'] = $task['reward_data'];
        $data['pickuptime'] = time();
        $data['endtime'] = $task['endtime'];
        //如果设置了限时,单位小时
        //结束时间是接任务时间+限时
        if ($task['timelimit'] > 0) {
            $data['endtime'] = $data['pickuptime'] + intval($task['timelimit'] * 3600);
        }
        $data['dotime'] = $task['dotime'];
        $data['logo'] = $task['logo'];
        pdo_insert("ewei_shop_task_extension_join", $data);
        return intval(pdo_insertid());
    }

    /**web
     * @param $action
     * @param $page
     * @return array|bool
     */
    function getTaskLixt($action, $page)
    {//获得全部任务列表
        global $_W;
        switch ($action) {
            case 'single':
                $type = 1;
                break;
            case 'repeat':
                $type = 2;
                break;
            case 'first':
                $type = 3;
                break;
            case 'period':
                $type = 4;
                break;
            case 'point':
                $type = 5;
                break;
            default :
                return false;
        }
        $psize = 20;
        $pstart = ($page - 1) * $psize;
        $sql = "SELECT id,title,starttime,endtime,status FROM " . tablename("ewei_shop_task") . " WHERE `type` = :type AND uniacid = :uniacid ORDER BY endtime DESC LIMIT {$pstart},{$psize}";
        return pdo_fetchall($sql, array(':uniacid' => $_W['uniacid'], ':type' => $type));
    }

    /**mobile
     * @param $task
     * @return string
     */
    function taskFilter($task)
    {//检测时间等领取条件函数
        global $_W;
        $type = $task['type'];
        if ($task['starttime'] > time() || $task['endtime'] < time() || empty($task['status'])) return '不是接任务的时间';

        switch ($type) {
            case 1://单次任务
                $sql = "SELECT COUNT(*) FROM " . tablename('ewei_shop_task_extension_join') . " WHERE taskid = :taskid AND openid = :openid AND uniacid = :uniacid";
                $all = pdo_fetchcolumn($sql, array(':taskid' => $task['id'], ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
                if (!empty($all)) return '已参加过';
                break;
            case 2://重复任务
                $sql = "SELECT COUNT(*) FROM " . tablename('ewei_shop_task_extension_join') . " WHERE taskid = :taskid AND openid = :openid AND completetime = 0 AND uniacid = :uniacid";
                $res = pdo_fetchcolumn($sql, array(':taskid' => $task['id'], ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
                if (!empty($res)) {
                    return '任务未完成不能继续领';
                }
                $sql1 = "SELECT completetime FROM " . tablename('ewei_shop_task_extension_join') . " WHERE taskid = :taskid AND openid = :openid AND uniacid = :uniacid ORDER BY completetime DESC";
                $completetime = pdo_fetchcolumn($sql1, array(':taskid' => $task['id'], ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
                $cantime = $task['repeat'] + $completetime;
                if ($cantime > time()) return '请在' . $cantime - time() . "秒后领取";
                $hourl = date('Y-m-d H:00:00', time());
                $hourr = date('Y-m-d H:59:59', time());
                $hourl = strtotime($hourl);
                $hourr = strtotime($hourr);
                $sql2 = "SELECT COUNT(*) FROM " . tablename('ewei_shop_task_extension_join') . " WHERE taskid = :taskid AND uniacid = :uniacid AND openid = :openid AND completetime > {$hourl} AND completetime < {$hourr} AND completetime != 0";
                $num = pdo_fetchcolumn($sql2, array(':taskid' => $task['id'], ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
                if ($num > $task['maxtimes']) {
                    return '每' . $task['everyhours'] . '小时只能接' . $task['maxtimes'] . '次任务';
                }
                break;
            case 3://新手任务
                $sql = "SELECT COUNT(*) FROM " . tablename('ewei_shop_task_extension_join') . " WHERE taskid = :taskid AND openid = :openid AND  uniacid = :uniacid";
                $all = pdo_fetchcolumn($sql, array(':taskid' => $task['id'], ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
                if (!empty($all)) return '已参加过';
                break;
            case 4://周期任务
                return '周期任务可由重复任务替代';
                break;
            case 5://目标任务
                return '目标任务暂不开放';
                break;
            default:
                return '任务类型不存在';
        }
    }

    function getRecordsList($page, $taskid)
    {
        global $_W;
        $psize = 20;
        $pstart = ($page - 1) * $psize;
        $sql = "SELECT * FROM " . tablename("ewei_shop_task_log") . " WHERE taskid = :taskid AND uniacid = :uniacid ORDER BY id DESC LIMIT {$pstart},{$psize}";
        return pdo_fetch($sql, array(':taskid' => $taskid, ':uniacid' => $_W['uniacid']));
    }

    function checkFirst($taskclass)
    {
        global $_W;
        $funcname = 'first' . $taskclass;
        return $this->$funcname();
    }

    function firstcommission_member()
    {

    }

    /**
     * 获得全部任务列表
     */
    function getUserTaskList($type)
    {
        global $_W;
        $time = time();
        $condition = ' AND `type` = 2 ';
        if ($type == 1) $condition = "AND ( `type` = 3 OR `type` = 1) ";
        $sql = "SELECT * FROM " . tablename('ewei_shop_task') . " WHERE status = 1 {$condition} AND starttime < {$time} AND endtime > {$time} AND uniacid = :uniacid";
        return pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
    }

    /**
     * @param string $condition
     * @return array
     */
    function getMyTaskList($condition = '=')
    {
        global $_W;
        $condition2 = '';
        if ($condition == '=') $condition2 .= ' AND  a.endtime > ' . time();
        $sql = "SELECT a.* FROM " . tablename("ewei_shop_task_extension_join") . " a JOIN " . tablename('ewei_shop_task') . " b ON a.taskid = b.id WHERE a.openid = :openid AND a.completetime {$condition} 0 {$condition2} AND a.uniacid = :uniacid";
        return pdo_fetchall($sql, array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
    }

    function failTask()
    {
        global $_W;
        $sql = "SELECT a.* FROM " . tablename("ewei_shop_task_extension_join") . " a JOIN " . tablename('ewei_shop_task') . " b ON a.taskid = b.id WHERE a.openid = :openid AND a.completetime = 0 AND a.endtime < " . time() . " AND a.uniacid = :uniacid";
        return pdo_fetchall($sql, array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
    }

    function returnName($taskclass)
    {
        if (strpos('1' . $taskclass, 'cost_goods')) {
            return '购买指定商品';
        }
        $sql = "SELECT taskname FROM " . tablename('ewei_shop_task_extension') . " WHERE taskclass = :taskclass";
        return pdo_fetchcolumn($sql, array(':taskclass' => $taskclass));
    }

    function returnGoodsName($id)
    {
        global $_W;
        $sql = "SELECT title FROM " . tablename('ewei_shop_goods') . " WHERE id = :id AND uniacid = :uniacid";
        $res = pdo_fetchcolumn($sql, array(':id' => $id, ':uniacid' => $_W['uniacid']));
        return $res;
    }

    function returnTaskname($taskclass)
    {
        $sql = "SELECT taskname FROM " . tablename('ewei_shop_task_extension') . " WHERE taskclass = :taskclass";
        return pdo_fetchcolumn($sql, array(':taskclass' => $taskclass));
    }

    function sendmessage($data)
    {
        global $_W;
        if ($data['score']) {
            $score = "已发放{$data['score']}积分，";
        }
        if ($data['balance']) {
            $balance = "{$data['balance']}余额，";
        }
        if ($data['coupon']) {
            $coupon = "{$data['coupon']}种优惠券，";
        }
        $url = mobileUrl('task.mytask', null, 1);
        if (strexists($url, '/addons/ewei_shopv2/')) {
            $url = str_replace("/addons/ewei_shopv2/", '/', $url);
        }
        $message = "任务完成通知\n\r\n\r任务已完成，{$score}{$balance}{$coupon}剩余未发放奖励请到我的任务中领取\n\r\n\r<a href='" . $url . "'>点击查看详情</a>";
        m('message')->sendCustomNotice($_W['openid'], $message);
    }


    //新版


    public $taskType = array();
    /**
     * 数据库注册字段
     * @var array
     */
    public $ewei_shop_task_list = array(
        'id', 'uniacid', 'displayorder', 'title', 'image', 'type', 'status',
        'picktype', 'starttime', 'endtime', 'stop_type', 'stop_limit', 'stop_time',
        'stop_cycle', 'repeat_type', 'repeat_interval', 'repeat_cycle', 'demand',
        'reward', 'followreward', 'design_data', 'design_bg', 'goods_limit', 'notice', 'requiregoods', 'native_data', 'native_data2', 'native_data3', 'reward3', 'reward2', 'level2', 'level3', 'member_group', 'auto_pick', 'keyword_pick','verb','unit','member_level');

    public $ewei_shop_task_set = array('uniacid', 'entrance', 'keyword', 'cover_title', 'cover_img', 'cover_desc', 'msg_pick', 'msg_progress', 'msg_finish', 'msg_follow', 'isnew', 'bg_img', 'top_notice');
    public $ewei_shop_task_record = array('id', 'uniacid', 'taskid', 'tasktitle', 'tasktype', 'task_demand', 'openid', 'nickname', 'picktime', 'stoptime', 'finishtime', 'task_progress', 'reward_data', 'followreward_data', 'taskimage', 'design_data', 'design_bg', 'require_goods', 'level1', 'level2', 'reward_data1', 'reward_data2', 'member_group', 'auto_pick');

    function __construct($name = '')
    {
        parent::__construct($name);
        $this->taskType = pdo_getall('ewei_shop_task_type');
       //处理分销商名称修改之后不生效的问题
        $commission = p('commission');
        if($commission){
            $set = m('common')->getPluginset('commission');
            if(!empty($this->taskType)){
                foreach($this->taskType as &$value){
                    if($value['type_key'] =='pyramid_num'){
                        $value['verb'] = $set['texts']['agent'].'推荐下级人数达';
                    }
                }
            }
        }
    }

    /**
     * 得到task_type详情
     * @param string $type
     * @return array|mixed
     */
    public function getTaskType($type = '')
    {
        if (empty($type)) return $this->taskType;
        foreach ($this->taskType as $tasktype) {
            if ($tasktype['type_key'] == $type) {
                return $tasktype;
            }
        }
        return false;
    }


    /**
     * web 分页查询所有任务
     * @param $page
     * @param int $psize
     * @return array|boolean
     */
    function getAllTask(&$page, $psize = 15)
    {
        global $_W, $_GPC;
        $page = max(1, $page);
        $pstart = ($page - 1) * $psize;
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );

        $type = trim($_GPC['type']);
        $keyword = trim($_GPC['keyword']);
        $condition = '';
        if (!empty($type) || !empty($keyword)) {
            $condition = " and `title` like '%{$keyword}%' and `type` like '%{$type}%' ";
        }
        $field = "id,displayorder,title,image,type,starttime,endtime,status";
        $sql = "select {$field} from " . tablename('ewei_shop_task_list') . " where uniacid = :uniacid {$condition}  order by displayorder desc,id desc limit {$pstart} , {$psize}";
        $return = pdo_fetchall($sql, $params);
        $countsql = substr($sql, 0, strpos($sql, 'order by'));
        $countsql = str_replace($field, 'count(*)', $countsql);
        $count = pdo_fetchcolumn($countsql, $params);
        $page = pagination2($count, $page, $psize);
        return $return;
    }

    /**
     * task插件公用save方法,添加/编辑任务表
     * @param $table
     * @param $task
     * @param bool $Update 是否支持更新
     * @return bool
     */
    public function taskSave($table, $task, $Update = true)
    {
        global $_W;
        //校验数据与数据库存储格式是否兼容
        $this->checkDbFormat($table, $task);
        if (!is_array($task)){
            return false;
        }
        $task['uniacid'] = intval($_W['uniacid']);
        $isIdKey = in_array('id', $this->$table);
        if (empty($task['id']) && $isIdKey) {
            //新增
            pdo_insert($table, $task);
            return pdo_insertid();
        } elseif (!$isIdKey) {//ID不是主键
            $countSql = "select count(*) from " . tablename($table) . " where uniacid = :uniacid";
            $ifExist = pdo_fetchcolumn($countSql, array('uniacid' => $_W['uniacid']));
            if ($ifExist && $Update) {
                pdo_update($table, $task, array('uniacid' => $_W['uniacid']));
                if (empty($task['id'])){
                    return $_W['uniacid'];
                }
                return $task['id'];
            } elseif (!$ifExist) {
                pdo_insert($table, $task);
                return pdo_insertid();
            }
            return false;
        } elseif ($Update) {//编辑
            pdo_update($table, $task, array('id' => $task['id']));
            if (empty($task['id'])){
                return $_W['uniacid'];
            }
            return $task['id'];
        }
        return false;
    }

    /**
     * 校验数据库字段
     * @param $task
     */
    protected function checkDbFormat($table, &$task)
    {

        if (!is_array($task) || !is_array($this->$table)) {
            return $task = false;
        }
        //检查字段
        $field = array_flip($this->$table);
        $diff = array_diff_key($task, $field);
        if (!empty($diff)) {
            return $task = false;
        }
        //数据json化
        foreach ($task as &$t) {
            if (is_array($t)) {
                $t = json_encode($t);
            }
        }
        return true;
    }

    /**
     * 删除任务
     * @param $ids
     * @return bool
     */
    public function deleteTask($ids)
    {
        $isArr = is_array($ids);
        $isNum = is_numeric($ids);
        if ($isNum) {
            $ids = array($ids);
            $isArr = true;
        }

        $idString = implode(',', $ids);
        // 获取要删除的所有任务,如果是海报类型并且有关联的回复关键字,则也要删除对应的回复关键字
        $tasks = pdo_fetchall("select `id`,`type`,`we7_rule_keyword_id` from ". tablename('ewei_shop_task_list') . " where id in ({$idString})");
        
        if ($tasks) {
            foreach ($tasks as $task) {
                if ($task['type'] == 'poster') {
                    $hasKeywordTask[] = $task['we7_rule_keyword_id'];
                }
            }
        }

        if ($isArr) {
            $condition = " id = '" . implode(" ' or id = '", $ids) . "'";
            
            if ($hasKeywordTask) {
                $hasKeywordTaskIds = implode(',', $hasKeywordTask);
                pdo_query("delete from ". tablename('rule_keyword') . " where id in ({$hasKeywordTaskIds})");
            }

            return pdo_query("delete from " . tablename('ewei_shop_task_list') . " where {$condition}");
        }
        if (!$isArr && !$isNum) {
            return false;
        }
    }

    /**
     * 得到任务By id
     * @param $id
     * @return bool
     */
    public function getThisTask($id)
    {
        global $_W;
        if (empty($id)) return false;
        return pdo_get('ewei_shop_task_list', array('id' => $id, 'uniacid' => $_W['uniacid']));
    }

    /**
     * 分页获取任务记录
     * @param $page
     * @param int $psize
     * @return array
     */
    public function getAllRecords(&$page, $psize = 20)
    {
        global $_W, $_GPC;
        $page = max(1, $page);
        $pstart = ($page - 1) * $psize;
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $condition = '';
        $keyword = trim($_GPC['keyword']);
        !empty($keyword) && $condition .= " and tasktitle like '%{$keyword}%' ";
        $starttime = $_GPC['time']['start'];
        !empty($starttime) && $condition .= " and picktime > '{$starttime}' ";
        $endtime = $_GPC['time']['end'];
        !empty($endtime) && $condition .= " and picktime < '{$endtime}' ";
        $field = '*';
        $sql = "select {$field} from " . tablename('ewei_shop_task_record') . "where uniacid = :uniacid {$condition} order by id desc limit {$pstart} , {$psize}";
        $return = pdo_fetchall($sql, $params);
        $countsql = substr($sql, 0, strpos($sql, 'order by'));
        $countsql = str_replace($field, 'count(*)', $countsql);
        $count = pdo_fetchcolumn($countsql, $params);
        $page = pagination2($count, $page, $psize);
        return $return;
    }

    /**
     * 分页获取奖励记录
     * @param $page
     * @param int $psize
     * @return array
     */
    public function getAllRewards(&$page, $psize = 20)
    {
        global $_W, $_GPC;
        $page = max(1, $page);
        $pstart = ($page - 1) * $psize;
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $condition = '';
        $keyword = trim($_GPC['keyword']);
        !empty($keyword) && $condition .= " and tasktitle like '%{$keyword}%' ";
        $starttime = $_GPC['time']['start'];
        !empty($starttime) && $condition .= " and gettime > '{$starttime}' ";
        $endtime = $_GPC['time']['end'];
        !empty($endtime) && $condition .= " and gettime < '{$endtime}' ";
        $field = '*';
        $sql = "select {$field} from " . tablename('ewei_shop_task_reward') . " where uniacid = :uniacid {$condition} and `get` = 1 order by id desc limit {$pstart} , {$psize}";
        $return = pdo_fetchall($sql, $params);
        $countsql = substr($sql, 0, strpos($sql, 'order by'));
        $countsql = str_replace($field, 'count(*)', $countsql);
        $count = pdo_fetchcolumn($countsql, $params);
        $page = pagination2($count, $page, $psize);
        return $return;
    }

    /**
     * 分页获取商品
     * @param string $keyword
     * @param int $page
     */
    public function getGoods_new($keyword = '', $page = 1)
    {
        global $_W;
        $psize = 10;
        $pstart = ($page - 1) * $psize;
        $field = 'id,title,thumb,marketprice,total';
        $like = $keyword === '' ? $keyword : " and title like %{$keyword}%";
        $sql = "select {$field} from " . tablename('ewei_shop_goods') . "where uniacid = :uniacid and status = 1 and deleted = 0{$like} limit {$pstart} , $psize";
        $countsql = substr($sql, 0, strpos($sql, 'limit'));
        $countsql = str_replace($field, 'count(*)', $countsql);
        $params = array(':uniacid' => $_W['uniacid']);
        $count = pdo_fetchcolumn($countsql, $params);
        $list = pdo_fetchall($sql, $params);
        show_json($count, $list);
    }

    /**
     * 分页获取优惠券
     * @param string $keyword
     * @param int $page
     */
    public function getCoupon($keyword = '', $page = 1)
    {
        global $_W;
        $psize = 10;
        $pstart = ($page - 1) * $psize;
        $field = 'id,couponname';
        $like = $keyword === '' ? $keyword : " and title like %{$keyword}%";
        $sql = "select {$field} from " . tablename('ewei_shop_coupon') . "where uniacid = :uniacid {$like} limit {$pstart} , $psize";
        $countsql = substr($sql, 0, strpos($sql, 'limit'));
        $countsql = str_replace($field, 'count(*)', $countsql);
        $params = array(':uniacid' => $_W['uniacid']);
        $count = pdo_fetchcolumn($countsql, $params);
        $list = pdo_fetchall($sql, $params);
        show_json($count, $list);
    }

    protected function stoptime($task)
    {
        global $_W;
        $time = time();
        $stoptime = '0000-00-00 00:00:00';
        if ($task['picktype'] == 1) return $task['endtime'];
        if ($task['stop_type'] == 1) {
            $stoptime = date('Y-m-d H:i:s', $time + $task['stop_limit']);
        } elseif ($task['stop_type'] == 2) {
            $stoptime = $task['stop_time'];
        } elseif ($task['stop_type'] == 3) {
            switch ($task['stop_cycle']) {
                case '0'://明天凌晨
                    $stoptime = date("Y-m-d 00:00:00", strtotime("+1 day"));
                    break;
                case '1'://下周第一天凌晨
                    $stoptime = date("Y-m-d 00:00:00", strtotime("next Monday"));
                    break;
                case '2'://下个月第一天凌晨
                    $stoptime = date("Y-m-d 00:00:00", mktime(0, 0, 0, date('n') + 1, 1, date('Y')));
                    break;
            }
        }
        return $stoptime;
    }

    /**
     * 接任务
     * @param $listid
     * @param $openid
     * @return int
     */
    public function pickTask($taskid, $openid)
    {
        global $_W;
        empty($openid) && $openid = $_W['openid'];
        //获取任务
        $task = $this->getThisTask($taskid);
        $info = m('member')->getInfo($openid);
        if (empty($task)) return error(-1, '任务不存在');
        //检查是否可以接任务
        $canPick = $this->checkCanPick($task, $openid);
        if (is_error($canPick)) {
            if($canPick['message'] && $task['type'] == 'poster'){
                m('message')->sendCustomNotice($openid, $canPick['message']);
                return error(-1, $canPick['message']);
            }
            return $canPick;
        }

        $stoptime = $this->stoptime($task);
        $taskArr = array(
            'uniacid' => $_W['uniacid'],
            'taskid' => $task['id'],
            'tasktitle' => $task['title'],
            'tasktype' => $task['type'],
            'openid' => $openid,
            'nickname' => $info['nickname'],
            'picktime' => date('Y-m-d H:i:s'),
            'task_demand' => max((int)$task['demand'], (int)$task['level2'], (int)$task['level3']),
            'taskimage' => $task['image'],
            'reward_data' => $task['reward'],
            'followreward_data' => $task['followreward'],
            'design_data' => $task['design_data'],
            'design_bg' => $task['design_bg'],
            'stoptime' => $stoptime,
            'require_goods' => $task['requiregoods'],
            'member_group' => $task['member_group'],//会员组
            'auto_pick' => $task['auto_pick'],//自动接取任务
        );

        //多级海报
        if ($task['type'] == 'poster' && $task['level2'] > 0) {
            $taskArr['level1'] = $task['demand'];
            $taskArr['reward_data1'] = $task['reward'];
            $taskArr['level2'] = $task['level2'];
            $taskArr['reward_data2'] = $task['reward2'];
            if ($task['level3'] > 0) {
                $taskArr['reward_data'] = $task['reward3'];
            } else {
                $taskArr['reward_data'] = $task['reward2'];
            }
        }

        //新增任务记录且不允许update
        $table = 'ewei_shop_task_record';
        $recordid = $this->taskSave($table, $taskArr, false);
        if (!$recordid) {
            return error(1, '任务接取失败了');

        }
        //1级奖励数据
        $reward = json_decode($task['reward'], true);
        $level = 0;
        if ($task['type'] === 'poster') {
            if ($task['level3'] > 0 || $task['level2'] > 0) {
                $level = 1;
            }
        }
        if (is_array($reward))
            foreach ($reward as $ke => $re) {
                if (is_array($re)) {
                    foreach ($re as $r) {
                        while ($r['num'] > 0) {
                            pdo_insert('ewei_shop_task_reward', array('uniacid' => $_W['uniacid'], 'taskid' => $task['id'], 'tasktitle' => $task['title'], 'tasktype' => $task['type'], 'taskowner' => $openid, 'ownernickname' => $info['nickname'], 'recordid' => $recordid, 'reward_type' => $ke, 'reward_data' => $r['id'], 'nickname' => $info['nickname'], 'openid' => $info['openid'], 'headimg' => $info['avatar'], 'reward_title' => $ke == 'coupon' ? $r['couponname'] : $r['title'], 'price' => $task['type'] == 'coupon' ? 0 : $r['price'], 'level' => $level));
                            $r['num']--;
                        }
                    }
                } else {
                    if ($re) {
                        if ($ke == 'credit') {
                            $reward_title = '积分';
                        } elseif ($ke == 'balance') {
                            $reward_title = '元余额';
                        } elseif ($ke == 'redpacket') {
                            $reward_title = '元微信红包';
                        }

                        pdo_insert('ewei_shop_task_reward', array('uniacid' => $_W['uniacid'], 'taskid' => $task['id'], 'tasktitle' => $task['title'], 'tasktype' => $task['type'], 'taskowner' => $openid, 'ownernickname' => $info['nickname'], 'recordid' => $recordid, 'reward_type' => $ke, 'reward_data' => $re, 'nickname' => $info['nickname'], 'openid' => $info['openid'], 'headimg' => $info['avatar'], 'reward_title' => $re . $reward_title, 'level' => $level));
                    }
                }
            }

        //2级奖励数据
        $level = 2;
        if ($task['type'] === 'poster') {
            if ($task['level3'] == 0) {
                $level = 0;
            }
        }

        $reward2 = json_decode($task['reward2'], true);
        if (is_array($reward2))
            foreach ($reward2 as $ke => $re) {
                if (is_array($re)) {
                    foreach ($re as $r) {
                        while ($r['num'] > 0) {
                            pdo_insert('ewei_shop_task_reward', array('uniacid' => $_W['uniacid'], 'taskid' => $task['id'], 'tasktitle' => $task['title'], 'tasktype' => $task['type'], 'taskowner' => $openid, 'ownernickname' => $info['nickname'], 'recordid' => $recordid, 'reward_type' => $ke, 'reward_data' => $r['id'], 'nickname' => $info['nickname'], 'openid' => $info['openid'], 'headimg' => $info['avatar'], 'reward_title' => $ke == 'coupon' ? $r['couponname'] : $r['title'], 'price' => $task['type'] == 'coupon' ? 0 : $r['price'], 'level' => $level));
                            $r['num']--;
                        }
                    }
                } else {
                    if ($re) {
                        if ($ke == 'credit') {
                            $reward_title = '积分';
                        } elseif ($ke == 'balance') {
                            $reward_title = '元余额';
                        } elseif ($ke == 'redpacket') {
                            $reward_title = '元微信红包';
                        }
                        pdo_insert('ewei_shop_task_reward', array('uniacid' => $_W['uniacid'], 'taskid' => $task['id'], 'tasktitle' => $task['title'], 'tasktype' => $task['type'], 'taskowner' => $openid, 'ownernickname' => $info['nickname'], 'recordid' => $recordid, 'reward_type' => $ke, 'reward_data' => $re, 'nickname' => $info['nickname'], 'openid' => $info['openid'], 'headimg' => $info['avatar'], 'reward_title' => $re . $reward_title, 'level' => $level));
                    }
                }
            }

        //3级奖励数据
        $reward3 = json_decode($task['reward3'], true);
        if (is_array($reward3))
            foreach ($reward3 as $ke => $re) {
                if (is_array($re)) {
                    foreach ($re as $r) {
                        while ($r['num'] > 0) {
                            pdo_insert('ewei_shop_task_reward', array('uniacid' => $_W['uniacid'], 'taskid' => $task['id'], 'tasktitle' => $task['title'], 'tasktype' => $task['type'], 'taskowner' => $openid, 'ownernickname' => $info['nickname'], 'recordid' => $recordid, 'reward_type' => $ke, 'reward_data' => $r['id'], 'nickname' => $info['nickname'], 'openid' => $info['openid'], 'headimg' => $info['avatar'], 'reward_title' => $ke == 'coupon' ? $r['couponname'] : $r['title'], 'price' => $task['type'] == 'coupon' ? 0 : $r['price'], 'level' => 0));
                            $r['num']--;
                        }
                    }
                } else {
                    if ($re) {
                        if ($ke == 'credit') {
                            $reward_title = '积分';
                        } elseif ($ke == 'balance') {
                            $reward_title = '元余额';
                        } elseif ($ke == 'redpacket') {
                            $reward_title = '元微信红包';
                        }
                        pdo_insert('ewei_shop_task_reward', array('uniacid' => $_W['uniacid'], 'taskid' => $task['id'], 'tasktitle' => $task['title'], 'tasktype' => $task['type'], 'taskowner' => $openid, 'ownernickname' => $info['nickname'], 'recordid' => $recordid, 'reward_type' => $ke, 'reward_data' => $re, 'nickname' => $info['nickname'], 'openid' => $info['openid'], 'headimg' => $info['avatar'], 'reward_title' => $re . $reward_title, 'level' => 0));
                    }
                }
            }
        $taskArr['id'] = $recordid;
        $taskArr['stoptime'] = $stoptime;

        //如果是海报任务
        if ($task['type'] == 'poster') {
            //判断任务时间
            $task_start = strtotime($task['starttime']);
            $task_end = strtotime($task['endtime']);
            if($task_start && ($task_start > time())){
                m('message')->sendCustomNotice($openid, '客官别急 , 活动'.$task['starttime'].'之后才开始!');
                return error(1, '客官别急 , 活动'.$task['starttime'].'之后才开始!');
            }
            if($task_end && ($task_end < time())){
                m('message')->sendCustomNotice($openid, '你来晚了 , 活动已经结束啦!');
                return error(1, '你来晚了 , 活动已经结束啦!');
            }
            $this->posterPickMessage($openid, $taskArr);
            //生成海报
            $taskArr['design_data'] = $task['design_data'];
            $taskArr['design_bg'] = $task['design_bg'];
            if ($_W['ispost'] && !empty($_POST['openid'])){
                //生成
                $poster = $this->create_poster(array(
                    'id'=>$taskArr['id'],
                    'design_data'=>$taskArr['design_data'],
                    'design_bg'=>$taskArr['design_bg'],
                    'stoptime'=>$taskArr['stoptime'],
                    'poster_version'=>$task['poster_version']
                ));
                if (is_error($poster) && $poster['message']){
                    m('message')->sendCustomNotice($openid, $poster['message']);
                    return error(1, $poster['message']);
                }
                $this->send2wechat($recordid, $openid);
            }
        }else{
            $this->taskPickMessage($openid, $taskArr);
        }
        return $recordid;
    }

    public function send2wechat($recordid, $openid = '')
    {
        global $_W,$_GPC;
        if (empty($openid)) $openid = $_W['openid'];
        if(empty($openid)) show_json(0, '缺少用户身份标识');
        $sql = "SELECT `follow` FROM ".tablename('mc_mapping_fans')." WHERE openid = :openid AND uniacid = :uniacid";
        $isFollowed = pdo_fetchcolumn($sql,array(':openid'=>$openid, ':uniacid'=>$_W['uniacid']));
        if(!$isFollowed) show_json(0, '未关注公众号无法接收海报');
        // 获取当前用户海报信息
        $posterInfo = pdo_fetch("select * from ".tablename('ewei_shop_task_qr')." where openid=:openid and recordid=:recordid", array(':openid'=>$openid, ':recordid'=>$recordid));
        // 用户当前任务记录
        $taskRecord = pdo_fetch("select * from ". tablename('ewei_shop_task_record'). ' where openid=:openid and id=:recordid', array(':openid'=>$openid, ':recordid'=>$recordid));
        // 当前任务
        $taskInfo = pdo_fetch("select * from ". tablename('ewei_shop_task_list') . " where id=:id", array(':id'=>$taskRecord['taskid']));
        // 两边不一样说明海报已经改变了,要重新上传海报素材
        if($posterInfo['poster_version'] != $taskInfo['poster_version']) {
            $uploadResult = $this->createPoster2(array(
                'id'=>$posterInfo['recordid'],
                'design_data'=>$taskInfo['design_data'],
                'design_bg'=>$taskInfo['design_bg'],
                'stoptime'=>$taskInfo['stoptime'],
                'poster_version'=>$taskInfo['poster_version']
            ),0, true, true);

            $mediaid = $uploadResult['mediaid'];
            $posterInfo['mediaid'] = $mediaid;

            // 把现在的海报版本和任务版本同步
            pdo_update('ewei_shop_task_qr', array(
                'mediaid' => $mediaid,
                'poster_version'=> $taskInfo['poster_version']
            ), array(
                'id'=>$posterInfo['id']
            ));
        }
        // 获取当前海报的多媒体id
        $mediaid = $posterInfo['mediaid'];
		// 发射
        $ret = m('message')->sendImage($openid, $mediaid);
        if (is_error($ret)) {
            show_json(0, $ret['message']);
        }
        show_json(1);
    }

    //生成海报
    public function create_poster($poster, $openid = '')
    {
        global $_W;
        if(empty($openid)){
            $openid = $_W['openid'];
        }
        //获取会员信息
        $info = m('member')->getInfo($openid);
        //生成海报
        $img = $this->createPoster2($poster, $info);
        if (is_error($img)) {
            return error(-1, $img['message']);
        } else {
            return error(0, $img);
        }
    }

    /**
     * 检查是否可以接取任务
     * @param $task
     * @param $openid
     * @return bool
     */
    protected function checkCanPick($task, $openid)
    {
        global $_W, $_GPC;
        //检查登陆
        if (empty($openid)) $openid = $_W['openid'];
        if (empty($openid)) return error(1, '请先登录');
        if (substr($task['type'], 0, 7) == 'pyramid') {
            //如果不是分销商则结束
            if (!p('commission')->isAgent($openid)) {
                return error(1, '只有分销商能接此任务');
            }
        }
        //检查开放时间
        $time = time();
        $task_start = strtotime($task['starttime']);
        $task_end = strtotime($task['endtime']);
        if ($task_start > $time) {
            //m('message')->sendCustomNotice($openid, '客官别急 , 活动'.$task['starttime'].'之后才开始!');
            return error(-1, '客官别急 , 活动'.$task['starttime'].'之后才开始!');
        } elseif ($task_end < $time) {
            //m('message')->sendCustomNotice($openid, '你来晚了 , 活动已经结束啦!');
            return error(-1, '你来晚了 , 活动已经结束啦!');
        } else {//检查重复接取时间间隔

            //任务不可重复领取
            $sql = "select * from " . tablename('ewei_shop_task_record') . " where openid = :openid and taskid = :taskid and uniacid = :uniacid order by id desc";
            //上一条记录
            $lastRecord = pdo_fetch($sql, array(':openid' => $openid, ':taskid' => $task['id'], ':uniacid' => $_W['uniacid']));
            //检测商品库存
            if($task['type'] == 'goods'){
                $res = $this->checkGoodsock($task);
               if(!$res['enough']){
                   return error(-1, '任务商品库存不足');
               }
                $ret = $this->checkRewardStock($task);
                if(!$ret['enough']){
                    return error(-1, '奖励商品库存不足');
                }
            }
            if (empty($lastRecord)) return true;
            if ($task['repeat_type'] == 1) {
                return error(-1, '不能重复接此任务');
            }
            //如果有进行中的任务 false
            $finishtime = strtotime($lastRecord['finishtime']);
            $stoptime = strtotime($lastRecord['stoptime']);
            if ($finishtime < 0 && ($stoptime > $time || $stoptime < 0)) {//任务进行中
                if ($_W['ispost'] && !empty($_GPC['openid'])) {
                    $this->send2wechat($lastRecord['id'], $_GPC['openid']);
                }
                return error(-1, '任务未完成，不能重复领取');
            } elseif ($finishtime > 0) {//任务已完成
                $compareTime = $finishtime;
            } elseif ($stoptime > $time) {//任务已过期
                $compareTime = $stoptime;
            }
            //不限制重复领取
            if ($task['repeat_type'] == 0) {
                return true;
            }
            //重复领取类型
            if ($task['repeat_type'] == 2) {//按间隔
                if ($task['repeat_interval'] < $time - $compareTime) {
                    return true;
                } else {
                    return error(-1, $task['repeat_interval'] . '秒后才能再接此任务');
                }
            } elseif ($task['repeat_type'] == 3) {//按周期
                if ($task['repeat_cycle'] == 1) {//按日
                    if ((int)strtotime(date('Ymd')) - (int)strtotime(date('Ymd', $compareTime)) > 86400) {
                        return true;
                    }
                    return error(-1, '明天才能再接此任务');
                } elseif ($task['repeat_cycle'] == 2) {//按周
                    $w = date('w', $compareTime);//上次是周几
                    $w == 0 && $w = 7;
                    $between = $this->diffBetweenTwoDays($compareTime, $time);
                    if ($w + $between > 7) {//已经是下个周
                        return true;
                    }
                    return error(-1, '下个周才能再接此任务');
                } elseif ($task['repeat_cycle'] == 3) {//按月
                    if (date('Ym') - date('Ym', $compareTime) > 0) {
                        return true;
                    } else {
                        return error(-1, '下个月才可以再接此任务');
                    }
                }
            } else {
                return error(-1, '重复领取类型不详');
            }
        }
        return true;
    }

    /**
     * 两天相差的天数
     * @param $day1
     * @param $day2
     * @return float|int
     */
    protected function diffBetweenTwoDays($day1, $day2)
    {

        $second1 = strtotime(date('Y-m-d', $day1));
        $second2 = strtotime(date('Y-m-d', $day2));
        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }

    //生成海报图片
    protected function createPoster2($poster, $member = 0, $upload = true, $update = false)
    {
        global $_W;
        if(empty($member['openid'])){
            $member = m('member')->getMember($_W['openid']);
        }
        $path = IA_ROOT . "/addons/ewei_shopv2/data/task/poster/" . $_W['uniacid'] . "/";
        PATH:
        if (!is_dir($path)) {
            load()->func('file');
            mkdirs($path);
            goto PATH;
        }
        //获取二维码图片
        $qr = $this->getQR2($poster, $member);
        if (is_error($qr)) {
            return error(-1, '生成二维码图片失败');
        }
        //文件名称，如果参数有变动，重新生成
        $md5 = md5(json_encode(array(
            'recordid' => $poster['id'],
            'openid' => $member['openid'],
            'bg' => $poster['design_bg'],
            'data' => $poster['design_data']
        )));
        $file = $md5 . '.png';
        $NoFile = !is_file($path . $file);
        if ($NoFile) {
            //未生成过，或二维码变化
            //生成背景
            set_time_limit(0);
            @ini_set("memory_limit", "256M");
            //海报尺寸
            $width = 640;
            $height = 1008;
            $target = imagecreatetruecolor($width, $height);
            $color = imagecolorallocate($target, 255, 255, 255);//分配一个白色
            imagefill($target, 0, 0, $color);

            $bg = $this->createImage2(tomedia($poster['design_bg']));

            if (empty($poster['design_bg'])) {
                $width_orig = $width;
                $height_orig = $height;
            } else {
                list($width_orig, $height_orig) = getimagesize(tomedia($poster['design_bg']));
            }

            if ($width && ($width_orig < $height_orig)) {
                $width = ($height / $height_orig) * $width_orig;
            } else {
                $height = ($width / $width_orig) * $height_orig;
            }
            //把背景缩放到海报中
            imagecopyresampled($target, $bg, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
            imagedestroy($bg);
            $data = json_decode(str_replace('&quot;', "'", $poster['design_data']), true);
            if (empty($data)) return error(-1, '数据不完整,处理失败');
            foreach ($data as $d) {
                $d = $this->getRealData2($d);
                if ($d['type'] == 'head') {
                    $avatar = preg_replace('/\/0$/i', '/96', $member['avatar']);
                    $target = $this->mergeImage2($target, $d, $avatar);
                } else if ($d['type'] == 'time') {
                    if ($poster['stoptime'] == '0000-00-00 00:00:00') {
                        $time = '无限制';
                    } else {
                        $time = date('Y-m-d H:i', strtotime($poster['stoptime']));
                    }
                    $target = $this->mergeText2($target, $d, '到期时间:' . $time);
                } else if ($d['type'] == 'img') {
                    $target = $this->mergeImage2($target, $d, $d['src']);
                } else if ($d['type'] == 'qr') {
                    $target = $this->mergeImage2($target, $d, 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $qr['ticket']);
                } else if ($d['type'] == 'nickname') {
                    $target = $this->mergeText2($target, $d, $member['nickname']);
                }
            }
            imagepng($target, $path . $file);
            imagedestroy($target);
        }
        $img = $_W['siteroot'] . "addons/ewei_shopv2/data/task/poster/" . $_W['uniacid'] . "/" . $file;
        if (!$upload) {
            return $img;
        }

        if($update) {
            $qr['mediaid'] = null;
        }

        if (empty($qr['mediaid'])) {
            //没上传或mediaid过期
            $mediaid = $this->uploadImage2($path . $file);
            $qr['mediaid'] = $mediaid;
            $qr['img'] = $mediaid;
            pdo_update('ewei_shop_task_qr', array('mediaid' => $mediaid), array('id' => $qr['id']));
        }

        return array('img' => $img, 'mediaid' => $qr['mediaid']);
    }

    protected function getRealData2($data)
    {

        $data['left'] = intval(str_replace('px', '', $data['left'])) * 2;
        $data['top'] = intval(str_replace('px', '', $data['top'])) * 2;
        $data['width'] = intval(str_replace('px', '', $data['width'])) * 2;
        $data['height'] = intval(str_replace('px', '', $data['height'])) * 2;
        $data['size'] = intval(str_replace('px', '', $data['size'])) * 2;
        $data['src'] = tomedia($data['src']);
        return $data;
    }

    protected function getQR2($poster, $member)
    {
        global $_W;
        //二维码过期时间
        $time = time();
        $expire = strtotime($poster['stoptime']) - $time;
        if ($expire > 86400 * 30 - 15) {
            //永久
            $scene_id = 't' . time() . rand(100000, 999999);
        }
        $this->createRule();
        //查找用户二维码
        $qr = pdo_fetch('select * from ' . tablename('ewei_shop_task_qr') . ' where openid = :openid and uniacid = :uniacid and recordid = :recordid limit 1', array(':openid' => $member['openid'], ':uniacid' => $_W['uniacid'], ':recordid' => $poster['id']));
        if (empty($qr)) {//生成二维码
            empty($scene_id) && $scene_id = $this->getSceneID2();
            $result = $this->getSceneTicket2($expire, $scene_id);
            if (is_error($result)) {
                return $result;
            }
            $ims_qrcode = array(
                'uniacid' => $_W['uniacid'],
                'acid' => $_W['acid'],
                'qrcid' => $scene_id[0] != 't' ? $scene_id : 0,
                "model" => 0,
                "name" => "EWEI_SHOPV2_TASKNEW_POSTER",
                "keyword" => 'EWEI_SHOPV2_TASKNEW',
                "expire" => $expire,
                "createtime" => time(),
                "status" => 1,
                'url' => $result['url'],
                "ticket" => $result['ticket'],
                "scene_str" => $scene_id[0] == 't' ? $scene_id : '',
            );
            pdo_insert('qrcode', $ims_qrcode);

            $qr = array(
                'uniacid' => $_W['uniacid'],
                'openid' => $member['openid'],
                'sceneid' => $scene_id,
                'ticket' => $result['ticket'],
                'recordid' => $poster['id'],
                'poster_version'    => $poster['poster_version']
            );

            pdo_insert('ewei_shop_task_qr', $qr);
            $qr['id'] = pdo_insertid();
        }
        return $qr;
    }

    /**
     * 创建TASKNEW回复规则
     */
    protected function createRule()
    {
        global $_W;
        $ruleSql = "select id from " . tablename('rule') . " where `name` = 'ewei_shopv2:task' and `module` = 'ewei_shopv2' and uniacid = {$_W['uniacid']}";
        $ruleCount = pdo_fetchcolumn($ruleSql);

        if (empty($ruleCount)) {
            $rule = array('uniacid' => $_W['uniacid'], 'name' => 'ewei_shopv2:task', 'module' => 'ewei_shopv2', 'status' => 1);
            if ((float)IMS_VERSION > 1) {
                $rule['reply_type'] = 1;
            }
            pdo_insert('rule', $rule);
            $ruleCount = pdo_insertid();
            if (empty($ruleCount)) {
                $rule = array('uniacid' => $_W['uniacid'], 'name' => 'ewei_shopv2:task', 'module' => 'ewei_shopv2', 'status' => 1);
                pdo_insert('rule', $rule);
                $ruleCount = pdo_insertid();
            }
        }
        $keywordSql = "select COUNT(*) from " . tablename('rule_keyword') . " where `content` = 'EWEI_SHOPV2_TASKNEW' and uniacid = {$_W['uniacid']}";
        $keywordCount = pdo_fetchcolumn($keywordSql);
        if (empty($keywordCount)) {
            $keyword = array('rid' => $ruleCount, 'uniacid' => $_W['uniacid'], 'module' => 'ewei_shopv2', 'content' => 'EWEI_SHOPV2_TASKNEW', 'type' => 1, 'status' => 1);
            pdo_insert('rule_keyword', $keyword);
        }
    }

    public function checkMember2($openid = '', $acc = '')
    {
        global $_W;
        $redis = redis();

        if (empty($acc)) {
            $acc = WeiXinAccount::create();
        }
        $userinfo = $acc->fansQueryInfo($openid);
        $userinfo['avatar'] = $userinfo['headimgurl'];

        load()->model('mc');
        $uid = mc_openid2uid($openid);
        if (!empty($uid)) {
            pdo_update('mc_members', array(
                'nickname' => $userinfo['nickname'],
                'gender' => $userinfo['sex'],
                'nationality' => $userinfo['country'],
                'resideprovince' => $userinfo['province'],
                'residecity' => $userinfo['city'],
                'avatar' => $userinfo['headimgurl']), array('uid' => $uid)
            );
        }

        pdo_update('mc_mapping_fans', array(
            'nickname' => $userinfo['nickname']
        ), array('uniacid' => $_W['uniacid'], 'openid' => $openid));

        $model = m('member');
        $member = $model->getMember($openid);
        if (empty($member)) {

            if (!is_error($redis)) {
                $member = $redis->get($openid . '_task_checkMember');

                if (!empty($member)) {
                    return json_decode($member, true);
                }
            }
            $mc = mc_fetch($uid, array('realname', 'nickname', 'mobile', 'avatar', 'resideprovince', 'residecity', 'residedist'));
            $member = array(
                'uniacid' => $_W['uniacid'],
                'uid' => $uid,
                'openid' => $openid,
                'realname' => $mc['realname'],
                'mobile' => $mc['mobile'],
                'nickname' => !empty($mc['nickname']) ? $mc['nickname'] : $userinfo['nickname'],
                'avatar' => !empty($mc['avatar']) ? $mc['avatar'] : $userinfo['avatar'],
                'gender' => !empty($mc['gender']) ? $mc['gender'] : $userinfo['sex'],
                'province' => !empty($mc['resideprovince']) ? $mc['resideprovince'] : $userinfo['province'],
                'city' => !empty($mc['residecity']) ? $mc['residecity'] : $userinfo['city'],
                'area' => $mc['residedist'],
                'createtime' => time(),
                'status' => 0
            );
            pdo_insert('ewei_shop_member', $member);
            $member['id'] = pdo_insertid();
            $member['isnew'] = true;
            if(method_exists(m('member'),'memberRadisCountDelete')) {
                m('member')->memberRadisCountDelete(); //清除会员统计radis缓存
            }
            if (!is_error($redis)) {
                $redis->set($openid . '_task_checkMember', json_encode($member), 20);
            }
        } else {
            $member['nickname'] = $userinfo['nickname'];
            $member['avatar'] = $userinfo['headimgurl'];
            $member['province'] = $userinfo['province'];
            $member['city'] = $userinfo['city'];
            pdo_update('ewei_shop_member', $member, array('id' => $member['id']));
            $member['isnew'] = false;
        }
        return $member;
    }

    protected function createImage2($imgurl)
    {
        load()->func('communication');
        $resp = ihttp_request($imgurl);
        if ($resp['code'] == 200 && !empty($resp['content'])) {
            return imagecreatefromstring($resp['content']);
        }
        $i = 0;
        while ($i < 3) {
            $resp = ihttp_request($imgurl);
            if ($resp['code'] == 200 && !empty($resp['content'])) {
                return imagecreatefromstring($resp['content']);
            }
            $i++;
        }
        return "";
    }

    protected function mergeImage2($target, $data, $imgurl)
    {
        $img = $this->createImage2($imgurl);
        $w = imagesx($img);
        $h = imagesy($img);
        imagecopyresized($target, $img, $data['left'], $data['top'], 0, 0, $data['width'], $data['height'], $w, $h);
        imagedestroy($img);
        return $target;
    }

    protected function mergeHead2($target, $data, $imgurl)
    {
        if ($data['head_type'] == 'default') {
            $img = $this->createImage2($imgurl);
            $w = imagesx($img);
            $h = imagesy($img);
            imagecopyresized($target, $img, $data['left'], $data['top'], 0, 0, $data['width'], $data['height'], $w, $h);
            imagedestroy($img);
            return $target;
        } elseif ($data['head_type'] == 'circle') {

        } elseif ($data['head_type'] == 'rounded') {

        }

    }

    protected function mergeText2($target, $data, $text)
    {
        $font = IA_ROOT . "/addons/ewei_shopv2/static/fonts/msyh.ttf";
        $colors = $this->hex2rgb2($data['color']);
        $color = imagecolorallocate($target, $colors['red'], $colors['green'], $colors['blue']);
        @imagettftext($target, $data['size'], 0, $data['left'], $data['top'] + $data['size'], $color, $font, $text);
        return $target;
    }

    protected function hex2rgb2($colour)
    {
        if ($colour[0] == '#') {
            $colour = substr($colour, 1);
        }
        if (strlen($colour) == 6) {
            list($r, $g, $b) = array($colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]);
        } elseif (strlen($colour) == 3) {
            list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
        } else {
            return false;
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array('red' => $r, 'green' => $g, 'blue' => $b);
    }

    protected function uploadImage2($img)
    {
        load()->func('communication');
        $account = m('common')->getAccount();
        $access_token = $account->fetch_token();
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type=image";
        $ch1 = curl_init();
        $data = array("media" => "@" . $img);
        if (version_compare(PHP_VERSION, '5.5.0', '>')) {
            $data = array("media" => curl_file_create($img));
        }
        curl_setopt($ch1, CURLOPT_URL, $url);
        curl_setopt($ch1, CURLOPT_POST, 1);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch1, CURLOPT_POSTFIELDS, $data);
        $content = @json_decode(curl_exec($ch1), true);
        if (!is_array($content)) {
            $content = array('media_id' => '');
        }
        curl_close($ch1);
        return $content['media_id'];
    }

    protected function getSceneID2()
    {

        global $_W;
        $acid = $_W['acid'];
        //$start  = -2147483648;
        $start = 1;
        $end = 2147483647;
        $scene_id = rand($start, $end);
        if (empty($scene_id)) {
            $scene_id = rand($start, $end);
        }
        while (1) {

            $count = pdo_fetchcolumn('select count(*) from ' . tablename('qrcode') . ' where qrcid=:qrcid and acid=:acid and model=0 limit 1', array(':qrcid' => $scene_id, ":acid" => $acid));
            if ($count <= 0) {
                break;
            }
            $scene_id = rand($start, $end);
            if (empty($scene_id)) {
                $scene_id = rand($start, $end);
            }
        }
        return $scene_id;
    }

    protected function getSceneTicket2($expire, $scene_id)
    {
        global $_W, $_GPC;
        $account = m('common')->getAccount();
        $bb = "{\"expire_seconds\":" . $expire . ",\"action_info\":{\"scene\":{\"scene_id\":" . $scene_id . "}},\"action_name\":\"QR_SCENE\"}";
        if ($scene_id[0] == 't') {//永久二维码scene_str以t开头
            $bb = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "' . $scene_id . '"}}}';
        }
        $token = $account->fetch_token();
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $token;
        $ch1 = curl_init();
        curl_setopt($ch1, CURLOPT_URL, $url);
        curl_setopt($ch1, CURLOPT_POST, 1);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch1, CURLOPT_POSTFIELDS, $bb);
        $c = curl_exec($ch1);
        $result = @json_decode($c, true);
        if (!is_array($result)) {
            return false;
        }
        if (!empty($result['errcode'])) {
            return error(-1, $result['errmsg']);
        }
        $ticket = $result['ticket'];
        return array('barcode' => json_decode($bb, true), 'ticket' => $ticket);
    }


    /**
     * 任务进度检查公用方法
     * @param $num
     * @param $typeKey
     */
    public function checkTaskProgress($num, $typeKey, $recordid = 0, $openid = '', $goodsid = 0)
    {
        global $_W;
        if (empty($openid)) $openid = $_W['openid'];
        if (empty($openid)) return false;
        //任务类型
        $type = $this->getTaskType($typeKey);
        $time = date('Y-m-d H:i:s');

        //被动任务先接任务
        $sqlPassive = "select * from " . tablename('ewei_shop_task_list') . " where uniacid = :uniacid and picktype = 1 and starttime < :starttime and endtime > :endtime";
        $paramsPassive = array(':uniacid' => $_W['uniacid'], ':starttime' => $time, ':endtime' => $time);
        $passiveTask = pdo_fetchall($sqlPassive, $paramsPassive);//全部被动任务

        if (!empty($passiveTask))
            foreach ($passiveTask as $task) {
                if (!pdo_fetchcolumn("select count(*) from " . tablename('ewei_shop_task_record') . " where taskid = {$task['id']} and uniacid = {$_W['uniacid']}"))
                    $this->pickTask($task['id'], $openid);
            }
        //查询所有进行中的我的任务
        $condtion = '';
        if (!empty($recordid)) $condtion = " and id = {$recordid} ";
        if (!empty($goodsid)) $condtion .= " and FIND_IN_SET('{$goodsid}',require_goods) ";
        $sql = "select * from " . tablename('ewei_shop_task_record') . " where uniacid = :uniacid {$condtion} and openid = :openid and tasktype = :tasktype and (stoptime = '0000-00-00 00:00:00' or stoptime >'{$time}') and finishtime = '0000-00-00 00:00:00'";
        $params = array(':uniacid' => $_W['uniacid'], ':openid' => $openid, ':tasktype' => $typeKey);
        $allRecord = pdo_fetchall($sql, $params);//全部我的任务
        if (!empty($allRecord))
            foreach ($allRecord as $record) {
                //防止并发
                $cache_key = $record['id'] . 'tasknew_' . $openid . '_pro' . $record['task_progress'];
                $height = m('cache')->get($cache_key);
                if (!empty($height)) {
                    m('cache')->del($cache_key);
                    return;
                } else {
                    m('cache')->set($cache_key, time());
                }
                if ($typeKey == 'recharge_full') {//单笔充值满额
                    if ($record['task_demand'] > $num) {
                        continue;
                    }
                }
                $record['task_progress'] = $record['task_progress'] + $num;

                //任务完成
                if ($record['task_progress'] >= $record['task_demand']) {
                    $update_arr = array('task_progress' => $record['task_demand']);
                    $update_arr['finishtime'] = date('Y-m-d H:i:s');//已完成
                    $ret = pdo_update('ewei_shop_task_record', $update_arr, array('id' => $record['id']));
                    if ($ret) {
                        //发奖励
                        $this->sentReward($record['id'], $openid);
                        if ($type['type_key'] === 'poster') {
                            $this->posterFinishMessage($record['openid'], $record);
                            //发送关注奖励
                            $this->followReward($record['id']);
                        }else{
                            //任务完成通知
                            $this->taskFinishMessage($record['openid'], $record);
                        }

                    }
                } else {
                    //海报
                    if ($type['type_key'] === 'poster') {
                        if ($record['level1'] > 0 && $record['task_progress'] == $record['level1']) {
                            //发送1级奖励
                            $this->sentReward($record['id'], $openid, 1);
                        }
                        if ($record['level2'] > 0 && $record['task_progress'] == $record['level2']) {
                            //发送2级奖励
                            $this->sentReward($record['id'], $openid, 2);
                        }
                    }
                    if ($type['once'] != 1) {
                        pdo_update('ewei_shop_task_record', array('task_progress' => $record['task_progress']), array('id' => $record['id']));

                        //发送关注奖励
                        if ($type['type_key'] === 'poster') {
                            //海报进度通知
                            $this->posterProgressMessage($record['openid'], $record);
                            //发送关注奖励
                            $this->followReward($record['id']);
                        }else{
                            //任务进度通知
                            $this->taskProgressMessage($record['openid'], $record);
                        }
                    }
                }
            }
    }

    public function sentReward($recordid, $openid, $level = 0)
    {
        global $_W;
        $time = date('Y-m-d H:i:s');
        //发送余额奖励
        //积分奖励
        //优惠券奖励
        //更新红包和商品奖励
        $sql = "select * from " . tablename('ewei_shop_task_reward') . " where recordid = :recordid and openid = :openid and uniacid = :uniacid and `get` = 0 and sent = 0 and `level` = {$level} ";
        $param = array(':recordid' => $recordid, ':uniacid' => $_W['uniacid'], ':openid' => $openid);
        $rewards = pdo_fetchall($sql, $param);
        if (!empty($rewards))
            foreach ($rewards as $k => $reward) {
                switch ($reward['reward_type']) {
                    case 'credit':
                        //积分充值
                        m('member')->setCredit($openid, 'credit1', floatval($reward['reward_data']), array($_W['uid'], '任务中心奖励'));
                        //置状态
                        pdo_update('ewei_shop_task_reward', array('sent' => 1, 'get' => 1, 'gettime' => $time, 'senttime' => $time), array('id' => $reward['id']));
                        break;
                    case 'balance':
                        //余额充值
                        m('member')->setCredit($openid, 'credit2', floatval($reward['reward_data']), array($_W['uid'], '任务中心奖励'));
                        //置状态
                        pdo_update('ewei_shop_task_reward', array('sent' => 1, 'get' => 1, 'gettime' => $time, 'senttime' => $time), array('id' => $reward['id']));
                        break;
                    case 'redpacket':
                        //置状态
                        pdo_update('ewei_shop_task_reward', array('get' => 1, 'gettime' => $time), array('id' => $reward['id']));
                        break;
                    case 'coupon':
                        //发优惠券
                        $data = array(
                            'uniacid' => $_W['uniacid'],
                            'merchid' => 0,
                            'openid' => $openid,
                            'couponid' => $reward['reward_data'],
                            'gettype' => 7,
                            'gettime' => time(),
                            'senduid' => $_W['uid'],
                        );
                        pdo_insert('ewei_shop_coupon_data', $data);
                        pdo_update('ewei_shop_task_reward', array('sent' => 1, 'get' => 1, 'gettime' => $time, 'senttime' => $time), array('id' => $reward['id']));
                        break;
                    case 'goods':
                        //商品置状态
                        pdo_update('ewei_shop_task_reward', array('get' => 1, 'gettime' => $time), array('id' => $reward['id']));
                        break;
                }
            }
    }

    /**
     * 关注奖励发放
     * @param $recordid
     * @return bool
     */
    public function followReward($recordid)
    {
        global $_W;

        $time = date('Y-m-d H:i:s');
        //发送余额奖励
        //积分奖励
        //优惠券奖励
        //更新红包和商品奖励
        $info = m('member')->getInfo($_W['openid']);
        $record = pdo_fetch("select * from " . tablename('ewei_shop_task_record') . " where id = :id and uniacid = :uniacid", array(':id' => $recordid, ':uniacid' => $_W['uniacid']));
        $frewards = json_decode($record['followreward_data'], true);
        if (empty($frewards)) {
            return false;
        }
        foreach ($frewards as $k => $reward) {
            switch ($k) {
                case 'credit':

                    //置状态
                    if ($reward > 0) {
                        //积分充值
                        m('member')->setCredit($_W['openid'], 'credit1', floatval($reward), array($_W['uid'], '任务中心关注海报奖励'));
                        pdo_insert('ewei_shop_task_reward', array('uniacid' => $_W['uniacid'], 'taskid' => $record['taskid'], 'tasktitle' => $record['tasktitle'], 'tasktype' => $record['tasktype'], 'taskowner' => $record['openid'], 'ownernickname' => $record['nickname'], 'recordid' => $record['id'], 'nickname' => $info['nickname'], 'headimg' => $info['avatar'], 'openid' => $_W['openid'], 'reward_type' => 'credit', 'reward_title' => $reward . '积分', 'reward_data' => $reward, 'sent' => 1, 'get' => 1, 'gettime' => $time, 'senttime' => $time, 'isjoiner' => 1));
                    }

                    break;
                case 'balance':

                    //置状态
                    if ($reward > 0) {
                        //余额充值
                        m('member')->setCredit($_W['openid'], 'credit2', floatval($reward), array($_W['uid'], '任务中心关注海报奖励'));
                        pdo_insert('ewei_shop_task_reward', array('uniacid' => $_W['uniacid'], 'taskid' => $record['taskid'], 'tasktitle' => $record['tasktitle'], 'tasktype' => $record['tasktype'], 'taskowner' => $record['openid'], 'ownernickname' => $record['nickname'], 'recordid' => $record['id'], 'nickname' => $info['nickname'], 'headimg' => $info['avatar'], 'openid' => $_W['openid'], 'reward_type' => 'balance', 'reward_title' => $reward . '元余额', 'reward_data' => $reward, 'sent' => 1, 'get' => 1, 'gettime' => $time, 'senttime' => $time, 'isjoiner' => 1));
                    }

                    break;
                case 'redpacket':
                    //置状态
                    if ($reward > 0)
                        pdo_insert('ewei_shop_task_reward', array('uniacid' => $_W['uniacid'], 'taskid' => $record['taskid'], 'tasktitle' => $record['tasktitle'], 'tasktype' => $record['tasktype'], 'taskowner' => $record['openid'], 'ownernickname' => $record['nickname'], 'recordid' => $record['id'], 'nickname' => $info['nickname'], 'headimg' => $info['avatar'], 'openid' => $_W['openid'], 'reward_type' => 'redpacket', 'reward_title' => $reward . '元微信红包', 'reward_data' => $reward, 'get' => 1, 'gettime' => $time, 'isjoiner' => 1));
                    break;
                case 'coupon':

                    //发优惠券
                    if (!empty($reward) && is_array($reward)) {
                        //发优惠券
                        foreach ($reward as $ck => $cv) {
                            $data = array(
                                'uniacid' => $_W['uniacid'],
                                'merchid' => 0,
                                'openid' => $_W['openid'],
                                'couponid' => $cv['id'],
                                'gettype' => 7,
                                'gettime' => time(),
                                'senduid' => $_W['uid'],
                            );
                            for ($i = 0; $i < $cv['num']; $i++) {
                                pdo_insert('ewei_shop_coupon_data', $data);
                                pdo_insert('ewei_shop_task_reward', array('uniacid' => $_W['uniacid'], 'taskid' => $record['taskid'], 'tasktitle' => $record['tasktitle'], 'tasktype' => $record['tasktype'], 'taskowner' => $record['openid'], 'ownernickname' => $record['nickname'], 'recordid' => $record['id'], 'nickname' => $info['nickname'], 'headimg' => $info['avatar'], 'openid' => $_W['openid'], 'reward_type' => 'coupon', 'reward_title' => $cv['couponname'], 'reward_data' => $cv['id'], 'sent' => 1, 'get' => 1, 'gettime' => $time, 'senttime' => $time, 'isjoiner' => 1));
                            }
                        }
                    }
                    break;
            }
            $sign = 1;
        }
        if (!empty($sign)) {//关注海报消息通知
            $this->taskPosterFollowMessage($_W['openid'], $record, $info['nickname']);
        }
    }

    /**
     * 接取任务通知
     * @param $openid
     */
    public function taskPickMessage($openid, $record)
    {
        //标识
        $tag = 'task_pick';
        $type = $this->getTaskType($record['tasktype']);
        //默认模板消息
        $message = array(
            'first' => array('value' => "亲爱的{$record['nickname']}，恭喜您成功领取任务。", "color" => "#ff0000"),
            'keyword1' => array('title' => '业务类型', 'value' => '会员通知', "color" => "#000000"),
            'keyword2' => array('title' => '业务内容', 'value' => $type['type_name'], "color" => "#000000"),
            'keyword3' => array('title' => '处理结果', 'value' => $record['tasktitle'], "color" => "#000000"),
            'keyword4' => array('title' => '操作结果', 'value' => date('Y-m-d H:i:s', time()), "color" => "#000000"),
            'remark' => array('value' => "截止时间：".$record['stoptime'] == '0000-00-00 00:00:00'?'无限制':substr($record['stoptime'],0,16)."\n完成任务可获得丰厚奖励，赶快去完成任务吧~~", "color" => "#000000")
        );

        //默认客服消息
        $url = mobileUrl('task.detail', array('rid' => $record['id']), 1);
        if (strexists($url, '/addons/ewei_shopv2/')) {
            $url = str_replace("/addons/ewei_shopv2/", '/', $url);
        }

        $text = "亲爱的[任务所有者昵称]，恭喜您成功领取[任务名称]\n\n领取时间：[接取时间]\n截止时间：[截止时间]\n完成任务可获得丰厚奖励，快去完成任务吧~~\n\n";
        $remark = "<a href='{$url}'>点击查看详情</a>";
        $text .= $remark;
        //4597
        $notice_data = m('common')->getSysset('notice', false);
        //变量替换
        $datas = $this->getNoticeDatas($record);

        //发射!
        m('notice')
            ->sendNotice(array(
                "openid" => $openid,//openid
                'tag' => $tag,//tag名称
                'default' => $message,//模板消息
                'cusdefault' => $text,//默认客服消息
                'url' => empty($notice_data['task_pick_template'])?$url:'',//链接
                'datas' => $datas,//替换
            ));
    }

    /**
     * 任务进度通知
     * @param $openid
     */
    public function taskProgressMessage($openid, $record)
    {
        //标识
        $tag = 'task_progress';
        $type = $this->getTaskType($record['tasktype']);
        //默认模板消息
        $message = array(
            'first' => array('value' => "任务最新进度", "color" => "#ff0000"),
            'keyword1' => array('title' => '业务类型', 'value' => '会员通知', "color" => "#000000"),
            'keyword2' => array('title' => '业务内容', 'value' => $type['type_name'], "color" => "#000000"),
            'keyword3' => array('title' => '处理结果', 'value' => $record['tasktitle'], "color" => "#000000"),
            'keyword4' => array('title' => '操作时间', 'value' => date('Y-m-d H:i:s', time()), "color" => "#000000"),
            'remark' => array('value' => "接取时间：".date('Y-m-d H:i')."\n当前进度：".$record['task_progress'] .'/'.$record['task_demand']."\n完成任务可获得丰厚奖励，赶快去完成任务吧~~", "color" => "#000000")
        );
        //默认客服消息
        $url = mobileUrl('task.detail', array('rid' => $record['id']), 1);
        if (strexists($url, '/addons/ewei_shopv2/')) {
            $url = str_replace("/addons/ewei_shopv2/", '/', $url);
        }

        $text = "恭喜，您的任务：[任务名称]当前进度为[分数进度]，任务完成后可以获得丰厚奖励，再接再厉~~\n";
        $remark = "<a href='{$url}'>点击查看详情</a>";
        $text .= $remark;
        //4597
        $notice_data = m('common')->getSysset('notice', false);
        //变量替换
        $datas = $this->getNoticeDatas($record);

        //发射!
        m('notice')
            ->sendNotice(array(
                "openid" => $openid,//openid
                'tag' => $tag,//tag名称
                'default' => $message,//模板消息
                'cusdefault' => $text,//默认客服消息
                'url' => empty($notice_data['task_progress_template'])?$url:'',//链接
                'datas' => $datas,//替换
            ));
    }

    /**
     * 任务完成通知
     * @param $openid
     */
    public function taskFinishMessage($openid, $record)
    {
        //标识
        $tag = 'task_finish';
        $type = $this->getTaskType($record['tasktype']);
        //默认模板消息
        $message = array(
            'first' => array('value' => "恭喜！您的任务已完成", "color" => "#ff0000"),
            'keyword1' => array('title' => '业务类型', 'value' => '会员通知', "color" => "#000000"),
            'keyword2' => array('title' => '业务内容', 'value' => $type['type_name'], "color" => "#000000"),
            'keyword3' => array('title' => '处理结果', 'value' => $record['tasktitle'], "color" => "#000000"),
            'keyword4' => array('title' => '操作时间', 'value' => date('Y-m-d H:i:s', time()), "color" => "#000000"),
            'remark' => array('value' => "如有疑问请联系在线客服", "color" => "#000000")
        );
        //默认客服消息
        $url = mobileUrl('task.reward', array(), 1);
        if (strexists($url, '/addons/ewei_shopv2/')) {
            $url = str_replace("/addons/ewei_shopv2/", '/', $url);
        }
        $text = "亲爱的[任务所有者昵称]，您的任务已经完成！快去查看您的奖励吧~~\n";
        $remark = "<a href='{$url}'>点击查看详情</a>";
        $text .= $remark;
         //4597
        $notice_data = m('common')->getSysset('notice', false);
        //变量替换
        $datas = $this->getNoticeDatas($record);

        //发射!
        m('notice')
            ->sendNotice(array(
                "openid" => $openid,//openid
                'tag' => $tag,//tag名称
                'default' => $message,//模板消息
                'cusdefault' => $text,//默认客服消息
                'url' => empty($notice_data['task_finish_template'])?$url:'',//链接
                'datas' => $datas,//替换
            ));
    }

    /**
     * 海报接取通知
     * @param $openid
     */
    public function posterPickMessage($openid, $record)
    {
        //当前会员
        $this_member = m('member')->getInfo($openid);
        //标识
        $tag = 'task_poster_pick';
        //默认模板消息
        $message = array(
            'first' => array('value' => "任务接取通知", "color" => "#ff0000"),
            'keyword1' => array('title' => '业务类型', 'value' => '会员通知', "color" => "#000000"),
            'keyword2' => array('title' => '业务内容', 'value' => '海报任务', "color" => "#000000"),
            'keyword3' => array('title' => '处理结果', 'value' => $record['tasktitle'], "color" => "#000000"),
            'keyword4' => array('title' => '操作时间', 'value' => date('Y-m-d H:i:s', time()), "color" => "#000000"),
            'remark' => array('value' => "领取时间：".substr($record['picktime'],0,16)."\n截止时间：".substr($record['stoptime'],0,16)."\n这是您的专属任务海报，快推出去让大家知道吧~~", "color" => "#000000")
        );
        //默认客服消息
        $url = mobileUrl('task.detail', array('rid' => $record['id']), 1);
        if (strexists($url, '/addons/ewei_shopv2/')) {
            $url = str_replace("/addons/ewei_shopv2/", '/', $url);
        }
        $text = "亲爱的[任务所有者昵称]，这是您的专属任务海报，快推出去让大家知道吧~~\n";
        $remark = "\n<a href='{$url}'>点击查看详情</a>";
        $text .= $remark;
        //4597
        $notice_data = m('common')->getSysset('notice', false);

        //变量替换
        $datas = $this->getNoticeDatas($record,$this_member['nickname']);

        //发射!
        m('notice')
            ->sendNotice(array(
                "openid" => $openid,//openid
                'tag' => $tag,//tag名称
                'default' => $message,//模板消息
                'cusdefault' => $text,//默认客服消息
                'url' => empty($notice_data['task_poster_pick_template'])?$url:'',//链接
                'datas' => $datas,//替换
            ));
    }

    /**
     * 海报进度通知
     * @param $openid
     */
    public function posterProgressMessage($openid, $record)
    {
        global $_W;
        //当前会员
        $this_member = m('member')->getInfo($_W['openid']);
        //标识
        $tag = 'task_poster_progress';
        //默认模板消息
        $message = array(
            'first' => array('value' => "{$record['niackname']}关注了您的海报，为您增加了1 点人气。", "color" => "#ff0000"),
            'keyword1' => array('title' => '业务类型', 'value' => '会员通知', "color" => "#000000"),
            'keyword2' => array('title' => '处理进度', 'value' => '海报任务', "color" => "#000000"),
            'keyword3' => array('title' => '处理内容', 'value' => $record['tasktitle'], "color" => "#000000"),
            'keyword4' => array('title' => '操作时间', 'value' => date('Y-m-d H:i:s', time()), "color" => "#000000"),
            'remark' => array('value' => "当前进度：".$record['task_progress'] . '/' . $record['task_demand']."\n扫描关注：".$this_member['nickname']."\n扫描时间：".date('Y-m-d H:i')."如有疑问请联系在线客服", "color" => "#000000")
        );
        //默认客服消息
        $url = mobileUrl('task.reward', null, 1);
        if (strexists($url, '/addons/ewei_shopv2/')) {
            $url = str_replace("/addons/ewei_shopv2/", '/', $url);
        }
        $text = "您的海报被{$this_member['nickname']}扫描，人气值+1！\n";
        $remark = "\n <a href='{$url}'>点击查看详情</a>";
        $text .= $remark;
        //4597
        $notice_data = m('common')->getSysset('notice', false);
        //变量替换
        $datas = $this->getNoticeDatas($record,$this_member['nickname']);
        //发射!
        m('notice')->sendNotice(array(
            "openid" => $openid,//openid
            'tag' => $tag,//tag名称
            'default' => $message,//模板消息
            'cusdefault' => $text,//默认客服消息
            'url' => empty($notice_data['task_poster_progress_template'])?$url:'',//链接
            'datas' => $datas,//替换
        ));
    }

    /**
     * 海报完成通知
     * @param $openid
     */
    public function posterFinishMessage($openid, $record)
    {
        //当前会员
        $this_member = m('member')->getInfo($openid);
        //标识
        $tag = 'task_poster_finish';
        //默认模板消息
        $message = array(
            'first' => array('value' => "您的任务已经完成！", "color" => "#ff0000"),
            'keyword1' => array('title' => '业务类型', 'value' => '会员通知', "color" => "#000000"),
            'keyword2' => array('title' => '业务内容', 'value' => '海报任务', "color" => "#000000"),
            'keyword3' => array('title' => '处理结果', 'value' => $record['tasktitle'], "color" => "#000000"),
            'keyword4' => array('title' => '操作时间', 'value' => date('Y-m-d H:i:s', time()), "color" => "#000000"),
            'remark' => array('value' => "快去查看您的奖励吧~~", "color" => "#000000")
        );
        //默认客服消息
        $url = mobileUrl('task.reward', null, 1);
        if (strexists($url, '/addons/ewei_shopv2/')) {
            $url = str_replace("/addons/ewei_shopv2/", '/', $url);
        }
        $text = "亲爱的[任务所有者昵称]，您的任务已经完成！\n";
        $remark = "\n感谢您的支持 <a href='{$url}'>点击查看详情</a>";
        $text .= $remark;
        //4597
        $notice_data = m('common')->getSysset('notice', false);
        //变量替换
        $datas = $this->getNoticeDatas($record,$this_member['nickname']);

        //发射!
        m('notice')
            ->sendNotice(array(
                "openid" => $openid,//openid
                'tag' => $tag,//tag名称
                'default' => $message,//模板消息
                'cusdefault' => $text,//默认客服消息
                'url' => empty($notice_data['task_poster_finish_template'])?$url:'',//链接
                'datas' => $datas,//替换
            ));
    }

    /**
     * 关注海报奖励通知
     * @param $openid
     */
    public function taskPosterFollowMessage($openid, $record, $nickname = '')
    {
        //当前会员
        $this_member = m('member')->getInfo($openid);
        //标识
        $tag = 'task_poster_scan';
        //默认模板消息
        $message = array(
            'first' => array('value' => "您关注了{$record['niackname']}的海报", "color" => "#ff0000"),
            'keyword1' => array('title' => '业务类型', 'value' => '会员通知', "color" => "#000000"),
            'keyword2' => array('title' => '业务内容', 'value' => '海报任务', "color" => "#000000"),
            'keyword3' => array('title' => '处理结果', 'value' => $record['tasktitle'], "color" => "#000000"),
            'keyword4' => array('title' => '操作时间', 'value' => date('Y-m-d H:i:s', time()), "color" => "#000000"),
            'remark' => array('value' => "快去查看您的奖励吧~~", "color" => "#000000")
        );

        //默认客服消息
        $url = mobileUrl('task.reward', null, 1);
        if (strexists($url, '/addons/ewei_shopv2/')) {
            $url = str_replace("/addons/ewei_shopv2/", '/', $url);
        }
        $text = "您关注了[任务所有者昵称]的海报，快去查看您的奖励吧~~\n";
        $remark = "\n <a href='{$url}'>点击查看详情</a>";
         $text .= $remark;
         //4597
        $notice_data = m('common')->getSysset('notice', false);
        //变量替换
        $datas = $this->getNoticeDatas($record,$this_member['nickname']);

        //发射!
        m('notice')
            ->sendNotice(array(
                "openid" => $openid,//openid
                'tag' => $tag,//tag名称
                'default' => $message,//模板消息
                'cusdefault' => $text,//默认客服消息
                'url' => empty($notice_data['task_poster_scan_template'])?$url:'',//链接
                'datas' => $datas,//替换
            ));
    }

    function getNoticeDatas($record,$nickname='')
    {
        global $_W;
        $datas = array();

        $datas[] = array('name' => '当前时间', 'value' => date('Y-m-d H:i'));
        $datas[] = array('name' => '接取时间', 'value' => substr($record['picktime'],0,16));
        $datas[] = array('name' => '截止时间', 'value' => $record['stoptime'] == '0000-00-00 00:00:00'?'无限制':substr($record['stoptime'],0,16));
        $datas[] = array('name' => '任务名称', 'value' => $record['tasktitle']);
        $datas[] = array('name' => '分数进度', 'value' => $record['task_progress'].'/'.$record['task_demand']);
        $datas[] = array('name' => '任务所有者昵称', 'value' => $record['nickname']);
        $datas[] = array('name' => '已完成数', 'value' => $record['task_progress']);
        $datas[] = array('name' => '待完成数', 'value' => $record['task_demand'] - $record['task_progress']);
        $datas[] = array('name' => '总需求数', 'value' => $record['task_demand']);
        if ($record['tasktype'] == 'poster'){
            $datas[] = array('name' => '一级海报需求', 'value' => 1);
            $datas[] = array('name' => '二级海报需求', 'value' => 1);
            $datas[] = array('name' => '三级级海报需求', 'value' => 1);
            $datas[] = array('name' => '关注者昵称', 'value' => $nickname);
        }
        return $datas;
    }


    /**
     * 特惠商品下单检测
     * @param int $taskrewardgoodsid
     * @return bool
     */
    public function getTaskRewardGoodsInfo($taskrewardgoodsid = 0)
    {
        global $_W;
        if (empty($taskrewardgoodsid)) {
            return false;
        }
        $reward = pdo_get('ewei_shop_task_reward', array('id' => $taskrewardgoodsid, 'openid' => $_W['openid'], 'uniacid' => $_W['uniacid'], 'get' => 1, 'sent' => 0, 'reward_type' => 'goods'));
        if (empty($reward)) {
            return false;
        }
        return $reward;
    }

    /**
     * 特惠商品下单完成
     * @param int $taskrewardgoodsid
     * @return bool
     */
    public function setTaskRewardGoodsSent($taskrewardgoodsid = 0)
    {
        global $_W;
        if (empty($taskrewardgoodsid)) {
            return false;
        }
        pdo_update('ewei_shop_task_reward', array('sent' => 1, 'senttime' => date('Y-m-d H:i:s')), array('id' => $taskrewardgoodsid));
        $_SESSION['taskcut'] = null;
        return true;
    }


    /**
     * 会员中心入口是否开启
     */
    public function TasknewEntrance()
    {
        global $_W;
        $sql = "select entrance from " . tablename('ewei_shop_task_set') . " where uniacid = {$_W['uniacid']}";
        return pdo_fetchcolumn($sql);
    }

    /**
     * 会员中心入口是否开启
     */
    public function TaskTopNotice()
    {
        global $_W;
        $sql = "select top_notice from " . tablename('ewei_shop_task_set') . " where uniacid = {$_W['uniacid']}";
        return pdo_fetchcolumn($sql);
    }

    /**
     * 获取消息通知设置
     * @param array $field
     * @return bool
     */
    public function getMessageSet($field = array('msg_pick', 'msg_progress', 'msg_finish', 'msg_follow'))
    {
        global $_W;
        $isString = is_string($field);
        if ($isString) {
            $field2 = array($field);
        } else {
            $field2 = $field;
        }
        if (!is_array($field2)) {
            return false;
        }
        $msg = pdo_get('ewei_shop_task_set', array('uniacid' => $_W['uniacid']), $field2);
        if ($isString) {
            return $msg[$field];
        }
        return $msg;
    }
    /**
     * 获取商品的库存
     * @param array $field
     * @return bool
     */
    public function checkGoodsock($task){
        global $_W;
        $enough = false;
        $getmesg = array();
        //领取前查询是否商品的库存充足
       if(!empty($task['requiregoods'])){
           $goodsids = explode(',',$task['requiregoods']);
           foreach($goodsids as $key=>$val){
               $goods = pdo_fetch('select id,hasoption,total,title from ' . tablename('ewei_shop_goods') . ' where uniacid=:uniacid and id=:id limit 1', array(':uniacid' => $_W['uniacid'], ':id' =>$val));
               //如果有多规格
               if($goods['hasoption']>0){
                   $goodsoption = pdo_fetchall('select * from ' . tablename('ewei_shop_goods_option') . ' where uniacid=:uniacid and goodsid=:id ', array(':uniacid' => $_W['uniacid'], ':id' =>$val));
                   foreach($goodsoption as $k =>$v){
                       if($v['stock']>0||$v['stock'] == -1 ){
                           $enough = true;
                       }else{
                           $getmesg[$key] = $goods['title'].'库存可能不充足.';
                       }
                   }
               }else{
                   if($goods['total']>0||$goods['total'] == -1 ){
                       $enough = true;
                   }else{
                       $getmesg[$key] = $goods['title'].'库存可能不充足.';
                   }
               }
           }
           return array('enough'=>$enough,'getmesg'=>$getmesg);
       }
    }
    /**
     * 获取奖励商品的库存
     * @param array $field
     * @return bool
     */
    public function checkRewardStock($task){
        global $_W;
        $enough = false;
        $getmesg = array();
        //领取前查询是否商品的库存充足
        $reward = json_decode($task['reward'],true);
        if(!empty($reward['goods'])){
                foreach($reward['goods'] as $key=>$val){
                    $goods = pdo_fetch('select id,hasoption,total,title from ' . tablename('ewei_shop_goods') . ' where uniacid=:uniacid and id=:id limit 1', array(':uniacid' => $_W['uniacid'], ':id' =>$val['id']));
                    //如果有多规格
                    if($goods['hasoption']>0){
                        $goodsoption = pdo_fetchall('select * from ' . tablename('ewei_shop_goods_option') . ' where uniacid=:uniacid and goodsid=:id ', array(':uniacid' => $_W['uniacid'], ':id' =>$val['id']));
                        foreach($goodsoption as $k =>$v){
                            if($v['stock']>0||$v['stock'] == -1 ){
                                $enough = true;
                            }else{
                                $getmesg[$key] = $goods['title'].'库存不充足.';
                            }
                        }
                    }else{
                        if($goods['total']>0||$goods['total'] == -1 ){
                            $enough = true;
                        }else{
                            $getmesg[$key] = $goods['title'].'库存不充足.';
                        }
                    }
                }
        }
        if($reward['credit']>0||$reward['balance']>0||$reward['redpacket']>0||!empty($reward['coupon'])){
            $enough = true;
        }
        return array('enough'=>$enough,'getmesg'=>$getmesg);
    }

    public function getRewardStock($reward){
        global $_W;

        if(!empty($reward['goods'])){
            foreach($reward['goods'] as $key=>$val){
                $val['all'] =0;
                $goods = pdo_fetch('select id,hasoption,total,title from ' . tablename('ewei_shop_goods') . ' where uniacid=:uniacid and id=:id limit 1', array(':uniacid' => $_W['uniacid'], ':id' =>$val['id']));
                //如果有多规格
                if($goods['hasoption']>0){

                    $goodsoption = pdo_fetchall('select id,stock from ' . tablename('ewei_shop_goods_option') . ' where uniacid=:uniacid and goodsid=:id ', array(':uniacid' => $_W['uniacid'], ':id' =>$val['id']));

                    foreach($goodsoption as $k =>$v){
                        if($v['stock']>0){
                            $reward['goods'][$key]['all'] +=$v['stock'];
                        }elseif($v['stock'] == -1 ){
                            $reward['goods'][$key]['all'] = -1;
                        }
                    }
                }else{
                    if($goods['total']>=0){
                        $reward['goods'][$key]['all'] += $goods['total'];
                    }elseif($goods['total'] == -1 ){
                        $reward['goods'][$key]['all'] = -1;
                    }
                }
            }

        }

        return $reward;
    }

}
