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

class Payment_ChingLeeingPage extends WebPage
{
    public function wechat()
    {
        global $_W;
        global $_GPC;
        $data = m('common')->getSysset('pay');
        $sec = m('common')->getSec();
        $sec = iunserializer($sec['sec']);
        if ($_W['ispost']) {
            if ($_FILES['weixin_cert_file']['name']) {
                $sec['cert'] = $this->upload_cert('weixin_cert_file');
            }
            if ($_FILES['weixin_key_file']['name']) {
                $sec['key'] = $this->upload_cert('weixin_key_file');
            }
            if ($_FILES['weixin_root_file']['name']) {
                $sec['root'] = $this->upload_cert('weixin_root_file');
            }
            $sec['appid'] = trim($_GPC['data']['appid']);
            $sec['secret'] = trim($_GPC['data']['secret']);
            $sec['mchid'] = trim($_GPC['data']['mchid']);
            $sec['apikey'] = trim($_GPC['data']['apikey']);
            pdo_update('ching_leeing_sysset', array('sec' => iserializer($sec)), array('uniacid' => $_W['uniacid']));
            $inputdata = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
            $data['weixin'] = intval($inputdata['weixin']);
            m('common')->updateSysset(array('pay' => $data));
            show_json(1);
        }
        $url = $_W['siteroot'] . 'addons/ching_leeing/payment/wechat/notify.php';
        load()->func('communication');
        $resp = ihttp_get($url);
        include $this->template();
    }

    protected function upload_cert($fileinput)
    {
        global $_W;
        $path = IA_ROOT . '/addons/ching_leeing/cert';
        load()->func('file');
        mkdirs($path);
        $f = $fileinput . '_' . $_W['uniacid'] . '.pem';
        $outfilename = $path . '/' . $f;
        $filename = $_FILES[$fileinput]['name'];
        $tmp_name = $_FILES[$fileinput]['tmp_name'];
        if (!(empty($filename)) && !(empty($tmp_name)))
        {
            $ext = strtolower(substr($filename, strrpos($filename, '.')));
            if ($ext != '.pem')
            {
                $errinput = '';
                if ($fileinput == 'weixin_cert_file')
                {
                    $errinput = 'CERT文件格式错误';
                }
                else if ($fileinput == 'weixin_key_file')
                {
                    $errinput = 'KEY文件格式错误';
                }
                else if ($fileinput == 'weixin_root_file')
                {
                    $errinput = 'ROOT文件格式错误';
                }
                show_json(0, $errinput . ',请重新上传!');
            }
            return file_get_contents($tmp_name);
        }
        return '';
    }
}

?>