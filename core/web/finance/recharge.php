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

class Recharge_ChingLeeingPage extends WebPage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $profile = m('member')->getMember($id, true);

        if ($_W['ispost']) {
            $num = floatval($_GPC['num']);
            $remark = trim($_GPC['remark']);

            if ($num <= 0) {
                show_json(0, array('message' => '请填写大于0的数字!'));
            }
            m('balance')->add($num,$profile['openid']);
            $logno = m('common')->createNO('member_log', 'logno', 'RC');
            pdo_insert('ching_leeing_member_log', array(
                'openid' => $profile['openid'],
                'logno' => $logno,
                'uniacid' => $_W['uniacid'],
                'datatype' =>1,
                'createtime' => TIMESTAMP,
                'status' => '1',
                'title' => $set['name'] . '会员充值',
                'money' => $num,
                'remark' => $remark
            ));
            $logid = pdo_insertid();
            m('notice')->sendMemberLogMessage($logid);
            show_json(1, array('url' => referer()));
        }
        include $this->template();
    }
}


?>