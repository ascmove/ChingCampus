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

class Cache_ChingLeeingModel
{
    public function getArray($key = '', $uniacid = '')
    {
        return $this->get($key, $uniacid);
    }

    public function get($key = '', $uniacid = '')
    {
        if (extension_loaded('redis') && function_exists('redis') && !(is_error(redis()))) {
            $redis = redis();
            $prefix = '__iserializer__format__::';
            $value = $redis->get($this->get_key($key, $uniacid));
            if (empty($value)) {
                return false;
            }
            if (stripos($value, $prefix) === 0) {
                return iunserializer(substr($value, strlen($prefix)));
            }
            return $value;
        }
        return cache_read($this->get_key($key, $uniacid));
    }

    public function get_key($key = '', $uniacid = '')
    {
        global $_W;
        if (empty($uniacid)) {
            $uniacid = $_W['uniacid'];
        }
        if (extension_loaded('redis') && function_exists('redis') && !(is_error(redis()))) {
            return 'ching_leeing_syscache_' . $_W['setting']['site']['key'] . '_' . $_W['uniacid'] . '_' . $_W['account']['key'] . '_' . $key;
        }
        return CHING_LEEING_PREFIX . md5($uniacid . '_new_' . $key);
    }

    public function getString($key = '', $uniacid = '')
    {
        return $this->get($key, $uniacid);
    }

    public function set($key = '', $value = NULL, $uniacid = '')
    {
        if (extension_loaded('redis') && function_exists('redis') && !(is_error(redis()))) {
            $redis = redis();
            $prefix = '__iserializer__format__::';
            if (is_array($value)) {
                $value = $prefix . iserializer($value);
            }
            $redis->set($this->get_key($key, $uniacid), $value);
            return;
        }
        cache_write($this->get_key($key, $uniacid), $value);
    }
}

?>