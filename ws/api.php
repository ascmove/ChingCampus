<?php
/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */
require('../../../framework/bootstrap.inc.php');
require('../../../addons/ching_leeing/core/inc/functions.php');

//20170420
require '../../../addons/ching_leeing/defines.php';
require '../../../addons/ching_leeing/core/inc/plugin_model.php';
require '../../../addons/ching_leeing/core/inc/com_model.php';
//20170420

global $_W,$_GPC;
if($_GPC['ticket'] != 'AedtbOdfbUdrfbBearFbVaerbA'){
    echo 'Auth Fail!';exit;
}
$_W['siteroot'] = str_replace('addons/ching_leeing/ws/','',$_W['siteroot']);
$openid = trim($_GPC['openid']);
$tradeNo = trim($_GPC['extent']);
$role = intval($_GPC['role']);
$type = intval($_GPC['type']);
$label = intval($_GPC['label']);
$pdo_insertid = intval($_GPC['pdo_insertid']);
$token = trim($_GPC['content']);
$this_token = md5($tradeNo.date("Ymd"));
$orderinfo = getOrderInfo($tradeNo);
$uniacid = $orderinfo['uniacid'];
$microtime=explode(".",strval(microtime(true)*1000));
$microtime = $microtime[0];
//订单验证
//20170420
if(!in_array(m('order')->OrderStatusAuthentication($orderinfo),array('ORDER_RECEIVED','SERVICE_START','SERVICE_FINISH','SERVICE_CONFIRMED','PUBLISH_EVALUATED','SERVANT_EVALUATED','SERVANT_APPLYING_FOR_ARBITRATION','PUBLISH_APPLYING_FOR_REFUND','PUBLISH_APPLYING_ARBITRATION'))){
    $data['code'] = 404;
    send($data);
    exit;
}
//会话验证
if(empty($token)){
    if($orderinfo['chattoken'] != $this_token){
        $data['type'] = 1;//要求认证
        send($data);
        exit;
    }
}else{
    if((strlen($token) == 32)&&($orderinfo['chattoken'] != $this_token)){
        if($this_token != $token){
            $data['code'] = 500;//要求认证
            send($data);
            exit;
        }else{
            pdo_update('ching_leeing_order',array('chattoken'=>$this_token),array('id'=>$orderinfo['id']));
            $data['code'] = 200;//在线
        }
    }
}
if($role == 1){
    $openid = $orderinfo['servant_openid'];
    $to_openid = $orderinfo['openid'];
    $header = "服务者";
    $to_role = 0;
}else{
    $openid = $orderinfo['openid'];
    $to_openid = $orderinfo['servant_openid'];
    $header = "发布者";
    $to_role = 1;
}
if($label == 1){
    pdo_insert('ching_leeing_chat_log',array(
        'uniacid'=>$orderinfo['uniacid'],
        'ordersn'=>$tradeNo,
        'openid'=>$openid,
        'to_openid'=>$to_openid,
        'pushToWeixin'=>0,
        'role'=>$role,
        'content'=>$token,
        'isread'=>1,
        'createtime'=>$microtime
    ));
    exit;
}
if($label == 2){
    $pushToWeixin = 0;
    $usage = pdo_fetchall('select createtime from '.tablename('ching_leeing_chat_log').' where uniacid=:uniacid and ordersn=:ordersn and pushToWeixin=1 and openid=:openid order by createtime desc limit 2',array(':uniacid'=>$orderinfo['uniacid'],':ordersn'=>$tradeNo,':openid'=>$openid));
    if((time()-intval($usage[1]['createtime']/1000)) > 60){
        $acc = WeAccount::create($orderinfo['uniacid']);
        $send['touser'] = $to_openid;
        $send['msgtype'] = 'text';
        if($header == '发布者'){
            $nickname = $orderinfo['postname'];
        }else{
            $nickname = pdo_fetchcolumn('select realname from '.tablename('ching_leeing_member').' where uniacid=:uniacid and openid=:openid',array(':uniacid'=>$orderinfo['uniacid'],':openid'=>$openid));
        }
        $send['text'] = array('content' => urlencode($header." ".$nickname." 发来的消息：\n\n“".trim($_GPC['content'])."”\n\n").'<a href="'.$_W['siteroot'].'app/index.php?i='.$orderinfo['uniacid'].'&c=entry&m=ching_leeing&do=mobile&r=chat&role='.$to_role.'&tradeNo='.$tradeNo.'">'.urlencode('点此查看消息并回复Ta').'</a>');
        $ret = $acc->sendCustomNotice($send);
        if(!is_error($ret)){
            $pushToWeixin = 1;
            $data['pushToWeixinContent'] = '此消息已推送至对方微信';
        }else{
            $data['pushToWeixinContent'] = '推送至对方微信失败';
        }
    }else{
        $data['pushToWeixinContent'] = '同一订单每分钟只可推送2次';
    }
    pdo_insert('ching_leeing_chat_log',array(
        'uniacid'=>$orderinfo['uniacid'],
        'ordersn'=>$tradeNo,
        'openid'=>$openid,
        'to_openid'=>$to_openid,
        'pushToWeixin'=>$pushToWeixin,
        'role'=>$role,
        'content'=>$token,
        'isread'=>0,
        'createtime'=>$microtime
    ));
    send($data);
    exit;
}
function send ($data){
    header('Content-type:text/json');
    echo json_encode($data);exit;
}
function getOrderInfo($ordersn)
{
    $ret = pdo_fetch('select * from ' . tablename('ching_leeing_order') . ' where ordersn=:ordersn', array(':ordersn'=>$ordersn));
    if(!empty($ret)){
        return $ret;
    }else{
        exit('订单不存在!');
    }
}
?>