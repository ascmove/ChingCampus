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

class SystemPage extends WebPage
{
    public function __construct()
    {
        global $_W;
        define('IS_CHING_LEEING_SYSTEM', true);
        $routes = explode('.', $_W['routes']);
        $_W['current_menu'] = (isset($routes[1]) ? $routes[1] : '');
    }
}

?>