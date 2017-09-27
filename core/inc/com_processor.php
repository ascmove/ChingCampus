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
require MODULE_ROOT . '/defines.php';

class ComProcessor extends WeModuleProcessor
{
    public $model;
    public $modulename;
    public $message;

    public function __construct($name = '')
    {
        $this->modulename = 'ching_leeing';
        $this->pluginname = $name;
        $this->loadModel();
    }

    private function loadModel()
    {
        $modelfile = IA_ROOT . '/addons/' . $this->modulename . '/core/com/' . $this->pluginname . '.php';
        if (is_file($modelfile)) {
            $classname = ucfirst($this->pluginname) . '_ChingLeeingComModel';
            require $modelfile;
            $this->model = new $classname($this->pluginname);
        }
    }

    public function respond()
    {
        $this->message = $this->message;
    }
}

?>