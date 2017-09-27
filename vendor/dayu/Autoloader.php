<?php
/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

spl_autoload_register('Autoloader::autoload');

class Autoloader
{
    /**
     * 类库自动加载，已做删减修改，仅用于阿里大鱼短信发送
     * @param string $class 对象类名
     * @return void
     */
    static public function autoload($class)
    {
        $name = $class;

        if (false !== strpos($name, '\\')) {
            $name = strstr($class, '\\', true);
        }


        $filename = TOP_AUTOLOADER_PATH . '/top/' . $name . '.php';

        if (is_file($filename)) {
            include $filename;
            return NULL;
        }


        $filename = TOP_AUTOLOADER_PATH . '/top/request/' . $name . '.php';

        if (is_file($filename)) {
            include $filename;
            return NULL;
        }

    }
}


?>