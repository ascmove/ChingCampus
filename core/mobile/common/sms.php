<?php
/**
 * Created by PhpStorm.
 * User: Projects
 * Date: 2017/1/19
 * Time: 17:05
 */

if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Sms_ChingLeeingPage extends MobilePage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $memberinfo = m('member')->getInfo($_W['openid']);
        if(($_SESSION[$_W['openid']]['hour-c']>=10)&&(time()-$_SESSION[$_W['openid']]['hour']<86400)){
            die_json(603);
        }
        if(($_SESSION[$_W['openid']]['hour-c']>=8)&&(time()-$_SESSION[$_W['openid']]['hour']<3600)){
            die_json(601);
        }
        if(time()-$_SESSION[$_W['openid']]['min']<60){
            die_json(602);
        }
        $phonecheck = pdo_fetch('select id from '.tablename('ching_leeing_member').' where uniacid=:uniacid and mobile=:mobile and serviceapplied =2 ',array(':uniacid'=>$_W['uniacid'],':mobile'=>trim($_GPC['phoneNum'])));
        if(!empty($phonecheck)){
            die_json(304);
        }
        $set = m('common')->getSysset('apply');
        $mobile = trim($_GPC['phoneNum']);
        $sms_id = intval($set['sms_reg']);
        if (empty($mobile)) {
            die_json(0);
        }
        if (empty($sms_id)) {
            die_json(0);
        }
        $key = '__ching_leeing_member_verifycodesession_' . $_W['uniacid'] . '_' . $mobile;
        @session_start();
        $code = random(4, true);
        $ret = com('sms')->send($mobile, $sms_id, array('验证码' => $code, '有效期' => '10分钟'));
        if ($ret) {
            $_SESSION[$_W['openid']]['key'] = $code;
            $_SESSION[$_W['openid']]['min'] = time();
            if(time()-$_SESSION[$_W['openid']]['hour']>=3600){
                $_SESSION[$_W['openid']]['hour'] = time();
                $_SESSION[$_W['openid']]['hour-c'] = 1;
            }else{
                $_SESSION[$_W['openid']]['hour-c'] += 1;
            }
            if(time()-$_SESSION[$_W['openid']]['hour']>=86400){
                $_SESSION[$_W['openid']]['day'] = time();
                $_SESSION[$_W['openid']]['day-c'] = 1;
            }else{
                $_SESSION[$_W['openid']]['day-c'] += 1;
            }
            die_json(1);
        }
        die_json(0);
    }
    public function check()
    {
        global $_W;
        global $_GPC;
        $code = intval($_GPC['code']);
        $memberinfo = m('member')->getInfo($_W['uniacid']);
        $_SESSION[$_W['openid']]['auth'] = false;
        if((time()-$_SESSION[$_W['openid']]['min'] < 600)&&($_SESSION[$_W['openid']]['key'] == $code)){
            $_SESSION[$_W['openid']]['auth'] = true;
            die_json(1);
        }
        die_json(0);
    }
}

?>