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

class Index_ChingLeeingPage extends MobilePage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        if(!beta($_W['openid'])){
            header('location:'.mobileUrl('index_v1'));exit;
        }
        $outTradeNo = trim($_GPC['outTradeNo']);
        if(!empty($outTradeNo)){
            $orderinfo = m('order')->getOrderInfo($outTradeNo);
        }
        $treaty = m('common')->getSysset('treaty');
        if(empty($treaty['url'])){
            $treaty['url'] = mobileLink('treaty');
        }
        $colleage_info = m('service')->getColleageList();
        foreach ($colleage_info as &$c_i)
        {
            $c_i['value'] = $c_i['id'];
            $c_i['text'] = $c_i['title'];
        }
        $service_info = m('service')->getServiceList();
        foreach ($service_info as &$s_i)
        {
            $s_i['value'] = $s_i['id'];
            $s_i['text'] = $s_i['title'];
        }
        $banner_info = m('common')->getSysset('banner');
        $banner = unserialize($banner_info['release_banner']);
        $member = m('member')->getInfo($_W['openid']);
        $uid = $member['id'];
        if (!empty($orderinfo)){
            $isnew = 0;
            $postname = $orderinfo['postname'];
            $mobile = $orderinfo['mobile'];
            $colleageid = $orderinfo['schoolid'];
            $colleage = $orderinfo['school'];
            $address = $orderinfo['address'];
            $servicetype = $orderinfo['servicetypeid'];
        }else if(empty($member['ordertempdata'])){
            $isnew = 1;
        }else{
            $isnew = 0;
            $ordertempdata = unserialize($member['ordertempdata']);
            $postname = $ordertempdata['postname'];
            $mobile = $ordertempdata['mobile'];
            $colleageid = $ordertempdata['colleageid'];
            $colleage = $ordertempdata['colleage'];
            $address = $ordertempdata['address'];
            $servicetype = $ordertempdata['servicetype'];
        }
        include $this->template();
    }

    public function treaty()
    {
        $treaty = m('common')->getSysset('treaty');
        include $this->template();
    }
    public function create()
    {
        global $_W;
        global $_GPC;
        $serviceTypeId = intval($_GPC['serviceTypeId']);
        $serviceTypeName = strval($_GPC['serviceTypeName']);
        $serviceAbstract = strval($_GPC['serviceAbstract']);
        $serviceTime = $_GPC['serviceTime'];
        $totalFee = floatval($_GPC['totalFee']);
        $expireTime = intval($_GPC['expireTime']);
        if($expireTime == -1){
            $expireTime = intval((129600-time()+strtotime('today'))/60);
        }
        if($expireTime == -2){
            $expireTime = intval((154800-time()+strtotime('today'))/60);
        }
        $serviceSchoolId = intval($_GPC['serviceSchoolId']);
        $serviceSchool = strval($_GPC['serviceSchool']);
        $sexRequire = intval($_GPC['sexRequire']);
        $serviceDetail = strval($_GPC['serviceDetail']);
        $newAddress = intval($_GPC['newAddress']);
        $userAddressId = intval($_GPC['userAddressId']);
        $username = strval($_GPC['username']);
        $phone = trim($_GPC['phone']);
        $schoolId = intval($_GPC['schoolId']);
        $school = strval($_GPC['school']);
        $address = strval($_GPC['address']);
        $allowSeeAddress = intval($_GPC['allowSeeAddress']);
        $recServiceNotify = intval($_GPC['recServiceNotify']);
        $serviceInfo = m('service')->getService($serviceTypeId);

        if(empty($serviceInfo)){
            die_json(1);
        }
        if($serviceInfo['title'] != $serviceTypeName){
            die_json(1);
        }

        //缓存数据
        $ordertempdata['postname'] = $username;
        $ordertempdata['mobile'] = $phone;
        $ordertempdata['colleageid'] = $schoolId;
        $ordertempdata['colleage'] = $school;
        $ordertempdata['address'] = $address;
        $ordertempdata['servicetype'] = $serviceTypeId;
        m('member')->updateInfo($_W['openid'],array('ordertempdata'=>serialize($ordertempdata)));
        $outTradeNo = m('common')->createNO('order', 'ordersn', 'CH');
        pdo_insert('ching_leeing_order',array(
            'uniacid'=>$_W['uniacid'],
            'openid'=>$_W['openid'],
            'ordersn'=>$outTradeNo,
            'price'=>$totalFee,
            'status'=>0,
            'ispay'=>0,
            'createtime'=>time(),
            'servicetime'=>intval($serviceTime/1000),
            'servicetypeid'=>$serviceTypeId,
            'servicetypename'=>$serviceTypeName,
            'serviceabstract'=>$serviceAbstract,
            'serviceschoolid'=>$serviceSchoolId,
            'serviceschool'=>$serviceSchool,
            'sexrequire'=>$sexRequire,
            'servicedetail'=>$serviceDetail,
            'address'=>$address,
            'recservicenotify'=>$recServiceNotify,
            'allowseeaddress'=>$allowSeeAddress,
            'school'=>$school,
            'schoolid'=>$schoolId,
            'postname'=>$username,
            'mobile'=>$phone,
            'expiretime'=>$expireTime
        ));
        $oid = pdo_insertid();

        $log = array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid'],'tid' => $outTradeNo, 'oid'=>$oid,'tag'=>'price','money' => $totalFee, 'status' => 0,'createtime'=>time());
        pdo_insert('ching_leeing_paylog', $log);
        $plid = pdo_insertid();

        die_json(0,array(
            'url'=>mobileLink('order/pay',array('l'=>$plid,'t'=>'pay','p'=>sha1(XCRYPT_KEY.$plid.'pay'))),
        ));
    }

    public function saveAddress()
    {
        global $_W;
        global $_GPC;
        //缓存数据
        $ordertempdata['postname'] = strval($_GPC['username']);
        $ordertempdata['mobile'] = trim($_GPC['phone']);
        $ordertempdata['colleageid'] = intval($_GPC['schoolId']);
        $ordertempdata['colleage'] = strval($_GPC['school']);
        $ordertempdata['address'] = strval($_GPC['address']);
        $ordertempdata['servicetype'] = intval($_GPC['serviceId']);
        $ret = m('member')->updateInfo($_W['openid'],array('ordertempdata'=>serialize($ordertempdata)));
        if($ret){
            die_json(0);
        }
        die_json(1);
    }

    public function tip()
    {
        include $this->template('tip');
    }
}

?>