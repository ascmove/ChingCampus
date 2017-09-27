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

class Account_ChingLeeingModel
{
    public function checkLogin()
    {
        global $_W;
        global $_GPC;
        if (empty($_W['openid'])) {
            $openid = $this->checkOpenid();
            if (!empty($openid)) {
                return $openid;
            }
            $url = urlencode(base64_encode($_SERVER['QUERY_STRING']));
            $loginurl = mobileUrl('account/login', array('mid' => $_GPC['mid'], 'backurl' => ($_W['isajax'] ? '' : $url)));
            if ($_W['isajax']) {
                show_json(0, array('url' => $loginurl, 'message' => '请先登录!'));
            }
            header('location: ' . $loginurl);
            exit();
        }
    }

    public function checkOpenid()
    {
        global $_W;
        global $_GPC;
        $key = '__ching_leeing_member_session_' . $_W['uniacid'];
        if (isset($_GPC[$key])) {
            $session = json_decode(base64_decode($_GPC[$key]), true);
            if (is_array($session)) {
                $member = m('member')->getMember($session['openid']);
                if (is_array($member) && ($session['ching_leeing_member_hash'] == md5($member['pwd'] . $member['salt']))) {
                    $GLOBALS['_W']['ching_leeing_member_hash'] = md5($member['pwd'] . $member['salt']);
                    $GLOBALS['_W']['ching_leeing_member'] = $member;
                    return $member['openid'];
                }
                isetcookie($key, false, -100);
            }
        }
    }

    public function setLogin($member)
    {
        global $_W;
        if (!is_array($member)) {
            $member = m('member')->getMember($member);
        }
        if (!empty($member)) {
            $member['ching_leeing_member_hash'] = md5($member['pwd'] . $member['salt']);
            $key = '__ching_leeing_member_session_' . $_W['uniacid'];
            $cookie = base64_encode(json_encode($member));
            isetcookie($key, $cookie, 7 * 86400);
        }
    }

    public function getSalt()
    {
        $salt = random(16);
        while (1) {
            $count = pdo_fetchcolumn('select count(*) from ' . tablename('ching_leeing_member') . ' where salt=:salt limit 1', array(':salt' => $salt));
            if ($count <= 0) {
                break;
            }
            $salt = random(16);
        }
        return $salt;
    }
}

?>