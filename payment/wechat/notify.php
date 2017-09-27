<?php
/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */
error_reporting(0);
define('IN_MOBILE', true);
$input = file_get_contents('php://input');
libxml_disable_entity_loader(true);
if (!(empty($input)) && empty($_GET['out_trade_no'])) {
    $obj = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);
    if (empty($data)) {
        exit('fail');
    }
    if (($data['result_code'] != 'SUCCESS') || ($data['return_code'] != 'SUCCESS')) {
        $result = array('return_code' => 'FAIL', 'return_msg' => (empty($data['return_msg']) ? $data['err_code_des'] : $data['return_msg']));
        echo array2xml($result);
        exit();
    }
    $get = $data;
} else {
    $get = $_GET;
}
require dirname(__FILE__) . '/../../../../framework/bootstrap.inc.php';
require IA_ROOT . '/addons/ching_leeing/defines.php';
require IA_ROOT . '/addons/ching_leeing/core/inc/functions.php';
require IA_ROOT . '/addons/ching_leeing/core/inc/plugin_model.php';
require IA_ROOT . '/addons/ching_leeing/core/inc/com_model.php';
new ChingLeeingWechatPay($get);
exit('fail');

class ChingLeeingWechatPay
{
    public $get;
    public $type;
    public $transid;
    public $total_fee;
    public $set;
    public $setting;
    public $sec;
    public $sign;
    public $isapp = false;

    public function __construct($get)
    {
        global $_W;
        $this->get = $get;
        $strs = explode(':', $this->get['attach']);
        $this->transid = trim($this->get['transaction_id']);
        $this->type = intval($strs[1]);
        $this->total_fee = round($this->get['total_fee'] / 100, 2);
        $_W['uniacid'] = $_W['weid'] = intval($strs[0]);
        $this->init();
    }

    public function init()
    {
        if ($this->type == '4300012') {
            $this->order();
        } else if ($this->type == '4900011') {
            $this->addExtraFee();
        }
        $this->success();
    }

    public function order()
    {
        global $_W;
        if (!($this->publicMethod())) {
            exit('Auth Fail!');
        }
        $tid = $this->get['out_trade_no'];
        $sql = 'SELECT * FROM ' . tablename('ching_leeing_paylog') . ' WHERE `uniacid`=:uniacid and `tag`="price" AND `tid`=:tid  limit 1';
        $params = array();
        $params[':tid'] = $tid;
        $params[':uniacid'] = $_W['uniacid'];
        $log = pdo_fetch($sql, $params);
        if($log['openid'] == 'oCBMrwimrar9O_qpzFEX5Rb8_9Vw')
        {
            $this->fail();
        }
        if (!(empty($log)) && ($log['status'] == '0') && ($log['money']-$log['deduct'] == $this->total_fee)) {
            pdo_update('ching_leeing_paylog', array('status'=>1,'transid'=>$this->transid), array('plid' => $log['plid']));
            $orderinfo = pdo_fetch('select * from ' . tablename('ching_leeing_order') . ' where uniacid=:uniacid and ordersn=:ordersn', array(':uniacid' => $_W['uniacid'],':ordersn'=>$log['tid']));
            if($orderinfo['paytime'] == 0){
                pdo_update('ching_leeing_order',array('ispay'=>1,'transid'=>$this->transid,'paytime'=>time()),array('id'=>$orderinfo['id']));
            }
            if(($orderinfo['recservicenotify'] == 1)&&(m('service')->notifyStatus($orderinfo['servicetypeid']))){
                //群发通知
                unset($params);
                $params = array();
                $params[':uniacid'] = $_W['uniacid'];
                $params[':openid'] = $log['openid'];
                if($orderinfo['serviceschoolid'] != ''){
                    $extra = ' and schoolid=:schoolid';
                    $params[':schoolid'] = $orderinfo['serviceschoolid'];
                }
                $receiver = pdo_fetchall('select openid,servicenoticeblack,membernoticeblack,servicepushbutton,usersex from '.tablename('ching_leeing_member').' where serviceapplied=2 and uniacid=:uniacid and openid<>:openid'.$extra ,$params);
                $count = 0;
                $shouldpush = 0;
                foreach ($receiver as $rec){
                    if($rec['servicepushbutton'] != 1){
                        continue;
                    }
                    $service_block = unserialize($rec['servicenoticeblack']);
                    if(in_array($orderinfo['servicetypeid'],$service_block)){
                        continue;
                    }
                    $member_block = explode(',',$rec['membernoticeblack']);
                    if(in_array($orderinfo['openid'],$member_block)){
                        continue;
                    }
                    if($orderinfo['sexrequire'] == 1){
                        if($rec['usersex'] != 1){
                            continue;
                        }
                    }
                    if($orderinfo['sexrequire'] == 2){
                        if($rec['usersex'] != 2){
                            continue;
                        }
                    }
                    if($orderinfo['allowseeaddress'] == 1){
                        $address = $orderinfo['serviceschool'].' '.$orderinfo['address'];
                    }else{
                        $address = '接单后可见';
                    }
                    $shouldpush += 1;
                    //LOG为下单的人 , $rec 为接单人
                    //if((in_array($log['openid'],explode(',',CHING_LEEING_DEV_USER_OPENIDS)))&&(!in_array($rec['openid'],explode(',',CHING_LEEING_DEV_USER_OPENIDS)))){
                    if(debug_group($log['openid'])&&!debug_group($rec['openid'])){
                        $push = true;
                    }else{
                        $push = m('notice')->sendNewOrderMessage($rec['openid'],$orderinfo['servicetypename'],getTime($orderinfo['servicetime']),$address,$orderinfo['price'],$orderinfo['serviceabstract'],$orderinfo['ordersn'],$_W['uniacid']);
                    }
                    if($push){
                        //if((in_array($log['openid'],explode(',',CHING_LEEING_DEV_USER_OPENIDS)))&&(!in_array($rec['openid'],explode(',',CHING_LEEING_DEV_USER_OPENIDS)))){
                        if(debug_group($log['openid'])&&!debug_group($rec['openid'])){
                            $push = false;
                        }else{
                            $count += 1;
                            $push = false;
                        }
                    }
                }
                if($count > 0){
                    $extrastr = '\n您的服务单已推送到'.$count.'位服务者';
                }
            }
            $platform = m('common')->getSysset('platform',$_W['uniacid']);
            $_W['apptel'] = empty($platform['tel'])?'010-88886666':$platform['tel'];
            $link = str_replace("addons/ching_leeing/","",mobileLink('order/detail',array('outTradeNo'=>$log['tid'])));
            $articles[] = array('title' => urlencode('您提交的服务单已成功付款'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n完成时间：'.getTime($orderinfo['servicetime']).' 前\n联系信息：'.$orderinfo['postname'].' '.$orderinfo['mobile'].'\n支付酬金：'.$log['money'].'元\n订单号：'.$log['tid'].$extrastr.'\n\n如有疑问或需要帮助请直接拨打我们的客服电话:'.$_W['apptel']), 'url' => $link);
            $account = m('common')->getAccount();
            m('message')->sendNews($orderinfo['openid'], $articles ,$account);
            pdo_update('ching_leeing_order',array('pushed'=>$count,'shouldpush'=>$shouldpush),array('id'=>$orderinfo['id']));
            $this->success();
        } else {
            $this->fail();
        }
    }

    public function addExtraFee()
    {
        global $_W;
        if (!($this->publicMethod())) {
            exit('Auth Fail!');
        }
        $tid = $this->get['out_trade_no'];
        $sql = 'SELECT * FROM ' . tablename('ching_leeing_paylog') . ' WHERE `uniacid`=:uniacid and `tag`="extraprice" AND `tid`=:tid  limit 1';
        $params = array();
        $params[':tid'] = $tid;
        $params[':uniacid'] = $_W['uniacid'];
        $log = pdo_fetch($sql, $params);
        if($log['status'] == 1)
        {
            $this->success();
        }
        $orderinfo = pdo_fetch('select * from '.tablename('ching_leeing_order').' where id=:id',array(':id'=>$log['oid']));
        if (!(empty($log)) && ($log['status'] == '0') && ($log['money']-$log['deduct'] == $this->total_fee)) {
            $link = str_replace("addons/ching_leeing/","",mobileLink('order/detail',array('outTradeNo'=>$orderinfo['ordersn'])));
            $articles[] = array('title' => urlencode('您的追加酬金已成功付款'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n完成时间：'.getTime($orderinfo['servicetime']).' 前\n联系信息：'.$orderinfo['postname'].' '.$orderinfo['mobile'].'\n支付酬金：'.$log['money'].'元\n订单号：'.$log['tid'].'\n\n如有疑问或需要帮助请直接拨打我们的客服电话:'.$_W['apptel']), 'url' => $link);
            $account = m('common')->getAccount();
            m('message')->sendNews($orderinfo['openid'], $articles,$account);
            $order_info = pdo_fetch('SELECT extraprice,extraprice_coupon_deduct FROM ' . tablename('ching_leeing_order') . ' WHERE `id`=:id', array(':id' => $log['oid']));
            pdo_update('ching_leeing_order',array('extraprice'=>$order_info['extraprice']+$log['money'],'extraprice_coupon_deduct'=>$order_info['extraprice_coupon_deduct']+$log['deduct']),array('id'=>$log['oid']));
            pdo_update('ching_leeing_paylog', array('status'=>1,'transid'=>$this->transid), array('plid' => $log['plid']));
            $this->success();
        } else {
            $this->fail();
        }
    }

    public function fail()
    {
        $result = array('return_code' => 'FAIL');
        echo array2xml($result);
        exit();
    }


    public function success()
    {
        $result = array('return_code' => 'SUCCESS', 'return_msg' => 'OK');
        echo array2xml($result);
        exit();
    }

    public function publicMethod()
    {
        global $_W;
        $this->set = m('common')->getSysset(array('app', 'pay'),$_W['uniacid']);
        $this->setting = uni_setting($_W['uniacid'], array('payment'));
        if (is_array($this->setting['payment']) ) {
            $sec_yuan = m('common')->getSec($_W['uniacid']);
            $this->sec = iunserializer($sec_yuan['sec']);
            $wechat = $this->setting['payment']['wechat'];
            if (!(empty($wechat))) {
                ksort($this->get);
                $string1 = '';
                foreach ($this->get as $k => $v) {
                    if (($v != '') && ($k != 'sign')) {
                        $string1 .= $k . '=' . $v . '&';
                    }
                }
                $wechat['signkey'] = (($wechat['version'] == 1 ? $wechat['key'] : $wechat['signkey']));
                $this->sign = strtoupper(md5($string1 . 'key=' . $wechat['signkey']));
                if ($this->sign == $this->get['sign']) {
                    return true;
                }
            }
        }
        return false;
    }
}

?>