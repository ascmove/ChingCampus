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

class Route_ChingLeeingModel
{
    public function run($isweb = true)
    {
        global $_GPC;
        global $_W;
        require_once IA_ROOT . '/addons/ching_leeing/core/inc/page.php';
        if ($isweb) {
            require_once CHING_LEEING_CORE . 'inc/page_web.php';
            require_once CHING_LEEING_CORE . 'inc/page_web_com.php';
        } else {
            require_once CHING_LEEING_CORE . 'inc/page_mobile.php';
            require_once CHING_LEEING_CORE . 'inc/page_mobile_login.php';
        }
        $r = str_replace('//', '/', trim($_GPC['r'], '/'));
        $routes = explode('.', $r);
        $segs = count($routes);
        $method = 'main';
        $root = (($isweb ? CHING_LEEING_CORE_WEB : CHING_LEEING_CORE_MOBILE));
        $isMerch = false;
        if (strexists($_W['siteurl'], 'web/merchant.php')) {
            if (empty($r)) {
                $r = 'merch.manage';
                $routes = explode('.', $r);
            }
            $isMerch = true;
            $isplugin = true;
        } else {
            $isplugin = !empty($r) && is_dir(CHING_LEEING_PLUGIN . $routes[0]);
        }
        if ($isplugin) {
            if ($isweb) {
                require_once CHING_LEEING_CORE . 'inc/page_web_plugin.php';
            } else {
                require_once CHING_LEEING_CORE . 'inc/page_mobile_plugin.php';
                require_once CHING_LEEING_CORE . 'inc/page_mobile_plugin_login.php';
                require_once CHING_LEEING_CORE . 'inc/page_mobile_plugin_pf.php';
            }
            $_W['plugin'] = $routes[0];
            $root = CHING_LEEING_PLUGIN . $routes[0] . '/core/' . (($isweb ? 'web' : 'mobile')) . '/';
            if ($isMerch) {
                $_W['plugin'] = 'merch';
                $root = CHING_LEEING_PLUGIN . 'merch/core/web/manage/';
            } else {
                $routes = array_slice($routes, 1);
            }
            $segs = count($routes);
        } else if ($routes[0] == 'system') {
            require_once CHING_LEEING_CORE . 'inc/page_system.php';
        }
        switch ($segs) {
            case 0:
                $file = $root . 'index.php';
                $class = 'Index';
            case 1:
                $file = $root . $routes[0] . '.php';
                if (is_file($file)) {
                    $class = ucfirst($routes[0]);
                } else if (is_dir($root . $routes[0])) {
                    $file = $root . $routes[0] . '/index.php';
                    $class = 'Index';
                } else {
                    $method = $routes[0];
                    $file = $root . 'index.php';
                    $class = 'Index';
                }
                $_W['action'] = $routes[0];
                break;
            case 2:
                $_W['action'] = $routes[0] . '.' . $routes[1];
                $file = $root . $routes[0] . '/' . $routes[1] . '.php';
                if (is_file($file)) {
                    $class = ucfirst($routes[1]);
                } else if (is_dir($root . $routes[0] . '/' . $routes[1])) {
                    $file = $root . $routes[0] . '/' . $routes[1] . '/index.php';
                    $class = 'Index';
                } else {
                    $file = $root . $routes[0] . '.php';
                    if (is_file($file)) {
                        $method = $routes[1];
                        $class = ucfirst($routes[0]);
                    } else if (is_dir($root . $routes[0])) {
                        $method = $routes[1];
                        $file = $root . $routes[0] . '/index.php';
                        $class = 'Index';
                    } else {
                        $file = $root . 'index.php';
                        $class = 'Index';
                    }
                }
                $_W['action'] = $routes[0] . '.' . $routes[1];
                break;
            case 3:
                $_W['action'] = $routes[0] . '.' . $routes[1] . '.' . $routes[2];
                $file = $root . $routes[0] . '/' . $routes[1] . '/' . $routes[2] . '.php';
                if (is_file($file)) {
                    $class = ucfirst($routes[2]);
                } else if (is_dir($root . $routes[0] . '/' . $routes[1] . '/' . $routes[2])) {
                    $file = $root . $routes[0] . '/' . $routes[1] . '/' . $routes[2] . '/index.php';
                    $class = 'Index';
                } else {
                    $method = $routes[2];
                    $file = $root . $routes[0] . '/' . $routes[1] . '.php';
                    if (is_file($file)) {
                        $class = ucfirst($routes[1]);
                    } else if (is_dir($root . $routes[0] . '/' . $routes[1])) {
                        $file = $root . $routes[0] . '/' . $routes[1] . '/index.php';
                        $class = 'Index';
                    }
                    $_W['action'] = $routes[0] . '.' . $routes[1];
                }
                break;
            case 4:
                $_W['action'] = $routes[0] . '.' . $routes[1] . '.' . $routes[2];
                $method = $routes[3];
                $class = ucfirst($routes[2]);
                $file = $root . $routes[0] . '/' . $routes[1] . '/' . $routes[2] . '.php';
                break;
            //default:
        }
        if (!is_file($file)) {
            show_message('未找到控制器 ' . $r);
        }
        $_W['routes'] = $r;
        $_W['isplugin'] = $isplugin;
        $_W['controller'] = $routes[0];
        $global_set = m('cache')->getArray('globalset', 'global');
        if (empty($global_set)) {
            $global_set = m('common')->setGlobalSet();
        }
        if (!is_array($global_set)) {
            $global_set = array();
        }
        empty($global_set['trade']['credittext']) && $global_set['trade']['credittext'] = '积分';
        empty($global_set['trade']['moneytext']) && $global_set['trade']['moneytext'] = '余额';
        $GLOBALS['_S'] = $_W['shopset'] = $global_set;
        include $file;
        $class = ucfirst($class) . '_ChingLeeingPage';
        $instance = new $class();
        if (!method_exists($instance, $method)) {
            show_message('控制器 ' . $_W['controller'] . ' 方法 ' . $method . ' 未找到!');
        }
        $instance->$method();
        exit();

    }
}

?>