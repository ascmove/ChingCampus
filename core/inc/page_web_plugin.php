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

class PluginWebPage extends WebPage
{
    public $pluginname;
    public $model;
    public $plugintitle;
    public $set;

    public function __construct($_init = true)
    {
        parent::__construct($_init);
        global $_W;
        if (!com('perm')->check_plugin($_W['plugin'])) {
            $this->message('你没有相应的权限查看');
        }
        $this->pluginname = $_W['plugin'];
        $this->modulename = 'ching_leeing';
        $this->plugintitle = m('plugin')->getName($this->pluginname);
        $this->model = m('plugin')->loadModel($this->pluginname);
        $this->set = $this->model->getSet();
        if ($_W['ispost']) {
            rc($this->pluginname);
        }
    }

    public function getSet()
    {
        return $this->set;
    }

    public function updateSet($data = array())
    {
        $this->model->updateSet($data);
    }
}

?>