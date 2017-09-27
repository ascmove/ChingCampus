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

class Processor extends WeModuleProcessor
{
    public function respond()
    {
        $rule = pdo_fetch('select * from ' . tablename('rule') . ' where id=:id limit 1', array(':id' => $this->rule));
        if (empty($rule)) {
            return false;
        }
        $names = explode(':', $rule['name']);
        $plugin = ((isset($names[1]) ? $names[1] : ''));
        $processname = $plugin;
        if (!empty($plugin)) {
            if ($plugin == 'com') {
                $com = ((isset($names[2]) ? $names[2] : ''));
                if (empty($com)) {
                    return false;
                }
                $processname = $com;
                $processor_file = CHING_LEEING_PROCESSOR . $com . '.php';
            } else {
                $processor_file = CHING_LEEING_PLUGIN . $plugin . '/core/processor.php';
            }
            if (is_file($processor_file)) {
                require $processor_file;
                $processor_class = ucfirst($processname) . 'Processor';
                $proc = new $processor_class($plugin);
                if (method_exists($proc, 'respond')) {
                    return $proc->respond($this);
                }
            }
        }
    }
}

?>