<?php
/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Chat_ChingLeeingPage extends MobilePage
{
    public function main()
    {
        global $_W,$_GPC;
        $tradeNo = sec_trim($_GPC['tradeNo']);
        $orderinfo = m('order')->getOrderInfo($tradeNo);
        $microtime=explode(".",strval(microtime(true)));
        $microtime = $microtime[0];
        $data = m('common')->getSysset('platform');
        //print_r($data);exit;
        $memberinfo = m('member')->getInfo($orderinfo['openid']);
        $servant_memberinfo = m('member')->getInfo($orderinfo['servant_openid']);
        if($_W['openid'] == $orderinfo['openid']){
            $role = 0;
        }
        if($_W['openid'] == $orderinfo['servant_openid']){
            $role = 1;
        }
        $list = pdo_fetchall('select * from '.tablename('ching_leeing_chat_log').' where uniacid=:uniacid and ordersn=:ordersn order by createtime asc',array(':uniacid'=>$_W['uniacid'],':ordersn'=>$tradeNo));
        pdo_update('ching_leeing_chat_log',array('isread'=>1),array('uniacid'=>$_W['uniacid'],'ordersn'=>$tradeNo,'to_openid'=>$_W['openid']));
        if ($role == 1) { //服务者
            $toname = $orderinfo['postname'];
            $my_openid = $servant_memberinfo['openid'];
            $to_openid = $orderinfo['openid'];
            $myavatar = $servant_memberinfo['avatar'];
            $youravatar = $memberinfo['avatar'];
            $mobile = $servant_memberinfo['mobile'];
        } else {  //发布者
            $toname = $servant_memberinfo['nickname'];
            $my_openid = $memberinfo['openid'];
            $to_openid = $orderinfo['servant_openid'];
            $myavatar = $memberinfo['avatar'];
            $youravatar = $servant_memberinfo['avatar'];
            $mobile = $memberinfo['mobile'];
        }
        $token = md5($tradeNo.date("Ymd"));
        include $this->template('chat_new');
    }
}

?>