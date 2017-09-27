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

class DataModel
{
    public function read($key = '')
    {
        global $_W;
        global $_GPC;
        return m('cache')->getArray('data_' . $_W['uniacid'] . '_' . $key);
    }

    public function write($key, $data)
    {
        global $_W;
        global $_GPC;
        m('cache')->set('data_' . $_W['uniacid'] . '_' . $key, $data);
    }
}

?>