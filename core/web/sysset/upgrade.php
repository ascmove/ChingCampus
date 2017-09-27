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


global $_W;
global $_GPC;

if (!$_W['isfounder']) {
    $this->message('无权访问!');
}


$entry = IA_ROOT . '/addons/ching_leeing/plugin/poster/model.php';
$op = ((empty($_GPC['op']) ? 'display' : $_GPC['op']));
load()->func('communication');
load()->func('file');

if ($op == 'display') {
    $auth = $this->getAuthSet();
    $versionfile = IA_ROOT . '/addons/ching_leeing/version.php';
    $updatedate = date('Y-m-d H:i', filemtime($versionfile));
    $version = CHING_LEEING_VERSION;
} else if ($op == 'check') {
    set_time_limit(0);
    $auth = $this->getAuthSet();
    $version = ((defined('CHING_LEEING_VERSION') ? CHING_LEEING_VERSION : '1.0'));
    $resp = ihttp_post(CHING_LEEING_AUTH_URL, array('type' => 'check', 'ip' => $auth['ip'], 'id' => $auth['id'], 'code' => $auth['code'], 'domain' => $auth['domain'], 'version' => $version, 'manual' => 1));
    $templatefiles = '';
    $ret = @json_decode($resp['content'], true);

    if (is_array($ret)) {
        $templatefiles = '';

        if ($ret['result'] == 1) {
            $files = array();

            if (!empty($ret['files'])) {
                foreach ($ret['files'] as $file) {
                    $entry = IA_ROOT . '/addons/ching_leeing/' . $file['path'];
                    if (!is_file($entry) || (md5_file($entry) != $file['hash'])) {
                        $files[] = array('path' => $file['path'], 'download' => 0);
                        if (strexists($entry, 'template/mobile') && strexists($entry, '.html')) {
                            $templatefiles .= '/' . $file['path'] . "\r\n";
                        }

                    }

                }
            }


            cache_write('cloud:modules:upgrade', array('files' => $files, 'version' => $ret['version'], 'upgrade' => $ret['upgrade']));
            $log = base64_decode($ret['log']);

            if (!empty($templatefiles)) {
                $log = '<br/><b>模板变化:</b><br/>' . $templatefiles . "\r\n" . $log;
            }


            exit(json_encode(array('result' => 1, 'version' => $ret['version'], 'filecount' => count($files), 'upgrade' => !empty($ret['upgrade']), 'log' => str_replace("\r\n", '<br/>', $log))));
        }

    }


    exit(json_encode(array('result' => 0, 'message' => $resp['content'] . '. ')));
} else if ($op == 'download') {
    $upgrade = cache_load('cloud:modules:upgrade');
    $files = $upgrade['files'];
    $version = $upgrade['version'];
    $auth = $this->getAuthSet();
    $path = '';

    foreach ($files as $f) {
        if (!(empty($f['download']))) {
            continue;
        }
        $path = $f['path'];
        break;
    }

    if (!empty($path)) {
        $resp = ihttp_post(CHING_LEEING_AUTH_URL, array('type' => 'download', 'ip' => $auth['ip'], 'id' => $auth['id'], 'code' => $auth['code'], 'domain' => $auth['domain'], 'path' => $path));
        $ret = @json_decode($resp['content'], true);

        if (is_array($ret)) {
            $path = $ret['path'];
            $dirpath = dirname($path);

            if (!is_dir(IA_ROOT . '/addons/ching_leeing/' . $dirpath)) {
                mkdirs(IA_ROOT . '/addons/ching_leeing/' . $dirpath, '0777');
            }


            $content = base64_decode($ret['content']);
            file_put_contents(IA_ROOT . '/addons/ching_leeing/' . $path, $content);

            if (isset($ret['path1'])) {
                $path1 = $ret['path1'];
                $dirpath1 = dirname($path1);

                if (!is_dir(IA_ROOT . '/addons/ching_leeing/' . $dirpath1)) {
                    mkdirs(IA_ROOT . '/addons/ching_leeing/' . $dirpath1, '0777');
                }


                $content1 = base64_decode($ret['content1']);
                file_put_contents(IA_ROOT . '/addons/ching_leeing/' . $path1, $content1);
            }


            $success = 0;

            foreach ($files as &$f) {
                if ($f['path'] == $path) {
                    $f['download'] = 1;
                    break;
                }


                if ($f['download']) {
                    ++$success;
                }

            }

            unset($f);
            cache_write('cloud:modules:upgrade', array('files' => $files, 'version' => $version, 'upgrade' => $upgrade['upgrade']));
            exit(json_encode(array('result' => 1, 'total' => count($files), 'success' => $success)));
        }

    } else {
        if (!empty($upgrade['upgrade'])) {
            $updatefile = IA_ROOT . '/addons/ching_leeing/upgrade.php';
            file_put_contents($updatefile, base64_decode($upgrade['upgrade']));
            require $updatefile;
            @unlink($updatefile);
        }


        load()->func('file');
        @rmdirs(IA_ROOT . '/addons/ching_leeing/tmp');
        file_put_contents(IA_ROOT . '/addons/ching_leeing/version.php', '<?php if(!defined(\'IN_IA\')) {exit(\'Access Denied\');}if(!defined(\'CHING_LEEING_VERSION\')) {define(\'CHING_LEEING_VERSION\', \'' . $upgrade['version'] . '\');}');
        cache_delete('cloud:modules:upgrade');
        $time = time();
        global $my_scenfiles;
        my_scandir(IA_ROOT . '/addons/ching_leeing');

        foreach ($my_scenfiles as $file) {
            if (!strexists($file, '/ching_leeing/data/') && !strexists($file, 'version.php')) {
                @touch($file, $time);
            }

        }

        exit(json_encode(array('result' => 2)));
    }
} else if ($op == 'checkversion') {
    file_put_contents(IA_ROOT . '/addons/ching_leeing/version.php', '<?php if(!defined(\'IN_IA\')) {exit(\'Access Denied\');}if(!defined(\'CHING_LEEING_VERSION\')) {define(\'CHING_LEEING_VERSION\', \'1.0\');}');
    header('location: ' . webUrl('upgrade'));
    exit();
}


include $this->template('web/sysset/upgrade');

?>