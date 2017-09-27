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

class WebPage extends Page
{
    public function __construct($_init = true)
    {
        if ($_init) {
            $this->init();
        }
        global $_W;
        $platform = m('common')->getSysset('platform');
        $_W['appname'] = empty($platform['name'])?'芝麻校园':$platform['name'];
        $_W['appicon'] = empty($platform['logo'])?$_W['siteroot'].'addons/ching_leeing/static/images/favicon.png':tomedia($platform['logo']);
        if($platform['switch'] == 1){
            $_W['appfee'] = empty($platform['fee'])?0:$platform['fee'];
        }
        $_W['apptel'] = empty($platform['tel'])?010-68882388:$platform['tel'];
    }

    private function init()
    {
        global $_W;
        if (($_W['role'] != 'manager') && ($_W['role'] != 'founder') && ($_W['routes'] != 'shop')) {
            $perm = cv($_W['routes']);
            $perm_type = com('perm')->getLogTypes(true);
            $perm_type_value = array();
            foreach ($perm_type as $val) {
                $perm_type_value[] = $val['value'];
            }
            $is_xxx = com('perm')->check_xxx($_W['routes']);
            if ($is_xxx) {
                if (!($perm)) {
                    foreach ($is_xxx as $item) {
                        if (in_array($item, $perm_type_value)) {
                            $this->message('你没有相应的权限查看1');
                        }
                    }
                }
            } else {
                if (strexists($_W['routes'], 'edit')) {
                    if (!(cv($_W['routes']))) {
                        $view = str_replace('edit', 'view', $_W['routes']);
                        $perm_view = cv($view);
                    }
                } else {
                    $main = $_W['routes'] . '.main';
                    $perm_main = cv($main);
                    if (!($perm_main) && in_array($main, $perm_type_value)) {
                        $this->message('你没有相应的权限查看2');
                    } else if (!($perm) && in_array($_W['routes'], $perm_type_value)) {
                        //$this->message('你没有相应的权限查看3');
                    }
                }
                if (isset($perm_view) && !($perm_view)) {
                    $this->message('你没有相应的权限查看4');
                }
            }
        }
        if ($_W['ispost']) {
            rc();
        }
    }

    public function isOpenPlugin()
    {
        $name = com_run('perm::allPerms');
        unset($name['shop']);
        unset($name['goods']);
        unset($name['member']);
        unset($name['order']);
        unset($name['finance']);
        unset($name['statistics']);
        unset($name['sysset']);
        unset($name['sale']);
        $name_keys = array_keys($name);
        return implode('|', $name_keys);
    }

    public function frame_menus()
    {
        global $_GPC;
        global $_W;
        if ($_W['plugin']) {
            include $this->template($_W['plugin'] . '/tabs');
            return;
        }
        if ($_W['controller'] == 'system') {
            $routes = explode('.', $_W['routes']);
            $tabs = $routes[0] . ((isset($routes[1]) ? '/' . $routes[1] : '')) . '/tabs';
            include $this->template($tabs);
            return;
        }
        include $this->template($_W['controller'] . '/tabs');
    }

    public function show_funbar()
    {
        global $_W;
        $funbardata = pdo_fetch('select * from ' . tablename('ching_leeing_funbar') . ' where uid=:uid and uniacid=:uniacid limit 1', array(':uid' => $_W['uid'], ':uniacid' => $_W['uniacid']));
        if (!(empty($funbardata['datas']))) {
            $funbardata['datas'] = json_decode($funbardata['datas'], true);
        }
        include $this->template('funbar');
    }
}

?>