<?php
/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

if (!defined('IN_IA')) {
    exit('Access Denied');
}

class User_ChingLeeingModel
{
    private $sessionid;

    public function __construct()
    {
        global $_W;
        $this->sessionid = '__cookie_ching_leeing_201507200000_' . $_W['uniacid'];
    }

    public function getOpenid()
    {
        $userinfo = $this->getInfo(false, true);
        return $userinfo['openid'];
    }

    public function getInfo($base64 = false, $debug = false)
    {
        global $_W;
        global $_GPC;
        $userinfo = array();
        if (CHING_LEEING_DEBUG) {
            $userinfo = array('openid' => 'oT-ihv9XGkJbX9owJiLZcZPAJcog', 'nickname' => '狸小狐', 'headimgurl' => 'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png', 'province' => '山东', 'city' => '青岛');
        } else {
            load()->model('mc');
            $userinfo = mc_oauth_userinfo();
            $need_openid = true;
            if ($_W['container'] != 'wechat') {
                if (($_GPC['do'] == 'order') && ($_GPC['p'] == 'pay')) {
                    $need_openid = false;
                }
                if (($_GPC['do'] == 'member') && ($_GPC['p'] == 'recharge')) {
                    $need_openid = false;
                }
                if (($_GPC['do'] == 'plugin') && ($_GPC['p'] == 'article') && ($_GPC['preview'] == '1')) {
                    $need_openid = false;
                }
            }
        }
        if ($base64) {
            return urlencode(base64_encode(json_encode($userinfo)));
        }
        return $userinfo;
    }

    public function followed($openid = '')
    {
        global $_W;
        $followed = !empty($openid);
        if ($followed) {
            $mf = pdo_fetch('select follow from ' . tablename('mc_mapping_fans') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $openid, ':uniacid' => $_W['uniacid']));
            $followed = $mf['follow'] == 1;
        }
        return $followed;
    }
}

?>