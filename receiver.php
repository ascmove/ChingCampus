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


require IA_ROOT . '/addons/ching_leeing/version.php';
require IA_ROOT . '/addons/ching_leeing/defines.php';
require CHING_LEEING_INC . 'functions.php';
require CHING_LEEING_INC . 'receiver.php';

class Ching_LeeingModuleReceiver extends Receiver
{
    public function receive()
    {
        parent::receive();
    }
}


?>