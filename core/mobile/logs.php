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

class Logs_ChingLeeingPage extends MobilePage
{
    public function pay()
    {
        global $_W,$_GPC;

        $data['uniacid'] = $_W['uniacid'];
        $data['openid'] = $_W['openid'];
        $data['errorMessage'] = trim($_GPC['errorMessage']);
        $data['scriptURI'] = trim($_GPC['scriptURI']);
        $data['lineNumber'] = trim($_GPC['lineNumber']);
        $data['columnNumber'] = trim($_GPC['columnNumber']);
        $data['errorObj'] = trim($_GPC['errorObj']);
        $data['from'] = 'Pay';
        $data['createtime'] = time();

        pdo_insert('ching_leeing_logs',$data);

        show_json(1);
    }
}

?>