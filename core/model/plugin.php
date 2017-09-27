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

class Plugin_ChingLeeingModel
{
    public function exists($pluginName = '')
    {
        $dbplugin = pdo_fetchall('select * from ' . tablename('ching_leeing_plugin') . ' where identity=:identyty limit  1', array(':identity' => $pluginName));
        if (empty($dbplugin)) {
            return false;
        }
        return true;
    }

    public function refreshCache($status = '', $iscom = false)
    {
        if ($status !== '') {
            $status = 'and status = ' . intval($status);
        }
        $com = pdo_fetchall('select * from ' . tablename('ching_leeing_plugin') . ' where iscom=1 and deprecated=0 ' . $status . ' order by displayorder asc');
        m('cache')->set('coms2', $com, 'global');
        $plugins = pdo_fetchall('select * from ' . tablename('ching_leeing_plugin') . ' where iscom=0 and deprecated=0 ' . $status . ' order by displayorder asc');
        m('cache')->set('plugins2', $plugins, 'global');
        if ($iscom) {
            return $com;
        }
        return $plugins;
    }

    public function getList($status = '')
    {
        $list = $this->getCategory();
        $plugins = $this->getAll(false, $status);
        foreach ($list as $ck => &$cv) {
            $ps = array();
            foreach ($plugins as $p) {
                if ($p['category'] == $ck) {
                    $ps[] = $p;
                }
            }
            $cv['plugins'] = $ps;
        }
        unset($cv);
        return $list;
    }

    public function getCategory()
    {
        return array('biz' => array('name' => '业务类'), 'sale' => array('name' => '营销类'), 'tool' => array('name' => '工具类'), 'help' => array('name' => '辅助类'));
    }

    public function getAll($iscom = false, $status = '')
    {
        global $_W;
        $plugins = '';
        if ($status !== '') {
            $status = 'and status = ' . intval($status);
        }
        if ($iscom) {
            $plugins = m('cache')->getArray('coms2', 'global');
            if (empty($plugins)) {
                $plugins = pdo_fetchall('select * from ' . tablename('ching_leeing_plugin') . ' where iscom=1 and deprecated=0 ' . $status . ' order by displayorder asc');
                m('cache')->set('coms2', $plugins, 'global');
            }
        } else {
            $plugins = m('cache')->getArray('plugins2', 'global');
            if (empty($plugins)) {
                $plugins = pdo_fetchall('select * from ' . tablename('ching_leeing_plugin') . ' where iscom=0 and deprecated=0 ' . $status . ' order by displayorder asc');
                m('cache')->set('plugins2', $plugins, 'global');
            }
        }
        return $plugins;
    }

    public function getName($identity = '')
    {
        $plugins = $this->getAll();
        foreach ($plugins as $p) {
            if (!($p['identity'] == $identity)) {
                continue;
            }
            return $p['name'];
        }
        return '';
    }

    public function loadModel($pluginname = '')
    {
        static $_model;
        if (!$_model) {
            $modelfile = IA_ROOT . '/addons/ching_leeing/plugin/' . $pluginname . '/core/model.php';
            if (is_file($modelfile)) {
                $classname = ucfirst($pluginname) . 'Model';
                require_once CHING_LEEING_CORE . 'inc/plugin_model.php';
                require_once $modelfile;
                $_model = new $classname($pluginname);
            }
        }
        return $_model;
    }
}

?>