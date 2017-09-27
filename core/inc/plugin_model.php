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

class PluginModel
{
    private $pluginname;
    private $set;

    public function __construct($name = '')
    {
        $this->pluginname = $name;
        $this->set = $this->getSet();
    }

    public function getSet()
    {
        if (empty($GLOBALS['_S'][$this->pluginname])) {
            return m('common')->getPluginset($this->pluginname);
        }
        return $GLOBALS['_S'][$this->pluginname];
    }

    public function updateSet($data = array())
    {
        m('common')->updatePluginset(array($this->pluginname => $data));
    }

    public function getName()
    {
        return pdo_fetchcolumn('select name from ' . tablename('ching_leeing_plugin') . ' where identity=:identity limit 1', array(':identity' => $this->pluginname));
    }
}

?>