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
if (defined('CHING_LEEING_OPCACHE_REFRESH')) {
    opcache_reset();
}


require_once IA_ROOT . '/addons/ching_leeing/version.php';
require_once IA_ROOT . '/addons/ching_leeing/defines.php';
require_once CHING_LEEING_INC . 'functions.php';

class Ching_LeeingModuleSite extends WeModuleSite
{
    public function getMenus()
    {
        global $_W;
        return array(
            array('title' => '管理后台', 'icon' => 'fa fa-shopping-cart', 'url' => webUrl())
        );
    }

    public function doWebWeb()
    {
        m('route')->run();
    }

    public function doMobileMobile()
    {
        m('route')->run(false);
    }

    public function payResult($params)
    {
        return m('order')->payResult($params);
    }
}


?>