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

class Uploader_ChingLeeingPage extends MobilePage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        load()->func('file');
        $field = $_GPC['file'];
        if (!empty($_FILES[$field]['name'])) {
            if (is_array($_FILES[$field]['name'])) {
                $files = array();
                foreach ($_FILES[$field]['name'] as $key => $name) {
                    $file = array('name' => $name, 'type' => $_FILES[$field]['type'][$key], 'tmp_name' => $_FILES[$field]['tmp_name'][$key], 'error' => $_FILES[$field]['error'][$key], 'size' => $_FILES[$field]['size'][$key]);
                    $files[] = $this->upload($file);
                }
                $ret = array('status' => 'success', 'files' => $files);
                exit(json_encode($ret));
                return NULL;
            }
            $result = $this->upload($_FILES[$field]);
            exit(json_encode($result));
            return NULL;
        }
        $result['message'] = '请选择要上传的图片！';
        exit(json_encode($result));
    }

    protected function upload($uploadfile)
    {
        global $_W;
        global $_GPC;
        $result['status'] = 'error';
        if ($uploadfile['error'] != 0) {
            $result['message'] = '上传失败，请重试！';
            return $result;
        }
        load()->func('file');
        $path = '/images/chinoldg_leeingold/' . $_W['uniacid'];
        if (!is_dir(ATTACHMENT_ROOT . $path)) {
            mkdirs(ATTACHMENT_ROOT . $path);
        }
        $_W['uploadsetting'] = array();
        $_W['uploadsetting']['image']['folder'] = $path;
        $_W['uploadsetting']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
        $_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
        $file = file_upload($uploadfile, 'image');
        if (is_error($file)) {
            $result['message'] = $file['message'];
            return $result;
        }
        if (function_exists('file_remote_upload')) {
            $remote = file_remote_upload($file['path']);
            if (is_error($remote)) {
                $result['message'] = $remote['message'];
                return $result;
            }
        }
        $result['status'] = 'success';
        $result['url'] = $file['url'];
        $result['error'] = 0;
        $result['filename'] = $file['path'];
        $result['url'] = trim($_W['attachurl'] . $result['filename']);
        pdo_insert('core_attachment', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'filename' => $uploadfile['name'], 'attachment' => $result['filename'], 'type' => 1, 'createtime' => TIMESTAMP));
        return $result;
    }

    public function remove()
    {
        global $_W;
        global $_GPC;
        load()->func('file');
        $file = $_GPC['file'];
        file_delete($file);
        exit(json_encode(array('status' => 'success')));
    }
}

?>