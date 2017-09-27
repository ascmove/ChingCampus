<?php
/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

if (!defined('TOP_SDK_WORK_DIR')) {
    define('TOP_SDK_WORK_DIR', '/tmp/');
}


if (!defined('TOP_SDK_DEV_MODE')) {
    define('TOP_SDK_DEV_MODE', false);
}


if (!defined('TOP_AUTOLOADER_PATH')) {
    define('TOP_AUTOLOADER_PATH', dirname(__FILE__));
}


require 'Autoloader.php';

?>