<?php

error_reporting(0);
require '../../../../../framework/bootstrap.inc.php';
require '../../../../../addons/ewei_shopv2/defines.php';
require '../../../../../addons/ewei_shopv2/core/inc/functions.php';

ignore_user_abort(); //忽略关闭浏览器
set_time_limit(0); //永远执行

//获取商城所有的公众号ID
$sets = pdo_fetchall('select uniacid from ' . tablename('ewei_shop_sysset'));

if (!empty($sets)) {
    foreach ($sets as $set) {
        //查询已过期多商户sql
        $sql = 'SELECT id,accounttime FROM ' . tablename('ewei_shop_merch_user') . ' WHERE TIMESTAMPDIFF(DAY,now(),FROM_UNIXTIME(accounttime)) <= 0 AND uniacid = :uniacid';
        $params = array(
            ':uniacid' => $set['uniacid']
        );
        //查询结果
        $merchUsers = pdo_fetchall($sql, $params);

        //存在已过期多商户时，处理多商户商品下架
        if (!empty($merchUsers)) {
            foreach ($merchUsers as $merchUser) {
                pdo_update('ewei_shop_goods', array('status' => 0), array('merchid' => $merchUser['id'], 'uniacid' => $set['uniacid']));
            }
        }
    }
}


