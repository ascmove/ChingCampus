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


require_once IA_ROOT . '/addons/ching_leeing/version.php';
require_once IA_ROOT . '/addons/ching_leeing/defines.php';
require_once CHING_LEEING_INC . 'functions.php';
require_once CHING_LEEING_INC . 'processor.php';
require_once CHING_LEEING_INC . 'plugin_model.php';
require_once CHING_LEEING_INC . 'com_model.php';

class Ching_LeeingModuleProcessor extends Processor
{
    public function respond()
    {
        return parent::respond();
    }
}


?>