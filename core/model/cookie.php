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

class Cookie_ChingLeeingModel
{
    private $prefix;

    public function __construct()
    {
        global $_W;
        $this->prefix = CHING_LEEING_PREFIX . '_cookie_' . $_W['uniacid'] . '_';
    }

    public function set($key, $value)
    {
        setcookie($this->prefix . $key, iserializer($value), time() + (3600 * 24 * 365));
    }

    public function get($key)
    {
        if (!isset($_COOKIE[$this->prefix . $key])) {
            return false;
        }
        return iunserializer($_COOKIE[$this->prefix . $key]);
    }
}

?>