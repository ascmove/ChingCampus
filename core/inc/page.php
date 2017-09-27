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

class Page extends WeModuleSite
{

    public function runTasks()
    {
        global $_W;
        load()->func('communication');
        $lasttime = strtotime(m('cache')->getString('closeorder', 'global'));
        $interval = intval(m('cache')->getString('closeorder_time', 'global'));
        if (empty($interval)) {
            $interval = 60;
        }
        $interval *= 60;
        $current = time();
        if (($lasttime + $interval) <= $current) {
            m('cache')->set('closeorder', date('Y-m-d H:i:s', $current), 'global');
            ihttp_request(CHING_LEEING_TASK_URL . 'order/close.php', NULL, NULL, 1);
            ihttp_request(CHING_LEEING_TASK_URL . 'order/confirm.php', NULL, NULL, 1);
        }
        exit('run finished.');
    }

    public function template($filename = '', $type = TEMPLATE_INCLUDEPATH, $account = false)
    {
        global $_W;
        global $_GPC;
        if (empty($filename)) {
            $filename = str_replace('.', '/', $_W['routes']);
        }
        if ($_GPC['do'] == 'web') {
            $filename = str_replace('/add', '/post', $filename);
            $filename = str_replace('/edit', '/post', $filename);
            $filename = 'web/' . $filename;
        } else if ($_GPC['do'] == 'mobile') {
        }
        $name = 'ching_leeing';
        $moduleroot = IA_ROOT . '/addons/ching_leeing';
        if (defined('IN_SYS')) {
            $compile = IA_ROOT . '/data/tpl/web/' . $_W['template'] . '/' . $name . '/' . $filename . '.tpl.php';
            $source = $moduleroot . '/template/' . $filename . '.html';
            if (!(is_file($source))) {
                $source = $moduleroot . '/template/' . $filename . '/index.html';
            }
            if (!(is_file($source))) {
                $explode = array_slice(explode('/', $filename), 1);
                $temp = array_slice($explode, 1);
                $source = $moduleroot . '/plugin/' . $explode[0] . '/template/web/' . implode('/', $temp) . '.html';
                if (!(is_file($source))) {
                    $source = $moduleroot . '/plugin/' . $explode[0] . '/template/web/' . implode('/', $temp) . '/index.html';
                }
            }
        } else {
            if ($account) {
                $template = $_W['shopset']['wap']['style'];
                if (empty($template)) {
                    $template = 'default';
                }
                if (!(is_dir($moduleroot . '/template/account/' . $template))) {
                    $template = 'default';
                }
                $compile = IA_ROOT . '/data/tpl/app/' . $name . '/' . $template . '/account/' . $filename . '.tpl.php';
                $source = IA_ROOT . '/addons/' . $name . '/template/account/' . $template . '/' . $filename . '.html';
                if (!(is_file($source))) {
                    $source = IA_ROOT . '/addons/' . $name . '/template/account/default/' . $filename . '.html';
                }
                if (!(is_file($source))) {
                    $source = IA_ROOT . '/addons/' . $name . '/template/account/default/' . $filename . '/index.html';
                }
            } else {
                $template = m('cache')->getString('template_shop');
                if (empty($template)) {
                    $template = 'default';
                }
                if (!(is_dir($moduleroot . '/template/mobile/' . $template))) {
                    $template = 'default';
                }
                $compile = IA_ROOT . '/data/tpl/app/' . $name . '/' . $template . '/mobile/' . $filename . '.tpl.php';
                $source = IA_ROOT . '/addons/' . $name . '/template/mobile/' . $template . '/' . $filename . '.html';
                if (!(is_file($source))) {
                    $source = IA_ROOT . '/addons/' . $name . '/template/mobile/' . $template . '/' . $filename . '/index.html';
                }
                if (!(is_file($source))) {
                    $source = IA_ROOT . '/addons/' . $name . '/template/mobile/default/' . $filename . '.html';
                }
                if (!(is_file($source))) {
                    $source = IA_ROOT . '/addons/' . $name . '/template/mobile/default/' . $filename . '/index.html';
                }
            }
            if (!(is_file($source))) {
                $names = explode('/', $filename);
                $pluginname = $names[0];
                $ptemplate = m('cache')->getString('template_' . $pluginname);
                if (empty($ptemplate) || ($pluginname == 'creditshop')) {
                    $ptemplate = 'default';
                }
                if (!(is_dir($moduleroot . '/plugin/' . $pluginname . '/template/mobile/' . $ptemplate))) {
                    $ptemplate = 'default';
                }
                unset($names[0]);
                $pfilename = implode('/', $names);
                $compile = IA_ROOT . '/data/tpl/app/' . $name . '/plugin/' . $pluginname . '/' . $ptemplate . '/mobile/' . $filename . '.tpl.php';
                $source = $moduleroot . '/plugin/' . $pluginname . '/template/mobile/' . $ptemplate . '/' . $pfilename . '.html';
                if (!(is_file($source))) {
                    $source = $moduleroot . '/plugin/' . $pluginname . '/template/mobile/' . $ptemplate . '/' . $pfilename . '/index.html';
                }
            }
        }
        if (!(is_file($source))) {
            exit('Error: template source \'' . $filename . '\' is not exist!');
        }
        if (DEVELOPMENT || !(is_file($compile)) || (filemtime($compile) < filemtime($source))) {
            shop_template_compile($source, $compile, true);
        }
        return $compile;
    }

    public function message($msg, $redirect = '', $type = '')
    {
        global $_W;
        $title = '';
        $buttontext = '';
        $message = $msg;
        $buttondisplay = true;
        if (is_array($msg)) {
            $message = ((isset($msg['message']) ? $msg['message'] : ''));
            $title = ((isset($msg['title']) ? $msg['title'] : ''));
            $buttontext = ((isset($msg['buttontext']) ? $msg['buttontext'] : ''));
            $buttondisplay = ((isset($msg['buttondisplay']) ? $msg['buttondisplay'] : true));
        }
        if (empty($redirect)) {
            $redirect = 'javascript:history.back(-1);';
        } else if ($redirect == 'close') {
            $redirect = 'javascript:WeixinJSBridge.call("closeWindow")';
        } else if ($redirect == 'exit') {
            $redirect = '';
        }
        include $this->template('_message');
        exit();
    }
}

?>