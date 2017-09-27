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

class Common_ChingLeeingModel
{
    public function getBanner($position){
        global $_W;
        $banner = pdo_fetchall('select id,thumb from ' . tablename('ching_leeing_banner') . ' where uniacid=:uniacid and enabled=1 and pos like :pos order by displayorder ASC', array(':uniacid' => $_W['uniacid'],':pos'=>'%'.$position.'%'));
        foreach($banner as &$ban){
            $ban['link'] = mobileLink('ad',array('aid'=>$ban['id']));
        }
        return $banner;
    }
    public function updateSysset($values, $uniacid = 0)
    {
        global $_W;
        global $_GPC;
        if (empty($uniacid)) {
            $uniacid = $_W['uniacid'];
        }
        $setdata = pdo_fetch('select id, sets from ' . tablename('ching_leeing_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $uniacid));
        if (empty($setdata)) {
            pdo_insert('ching_leeing_sysset', array('sets' => iserializer($values), 'uniacid' => $uniacid));
        } else {
            $sets = iunserializer($setdata['sets']);
            $sets = ((is_array($sets) ? $sets : array()));
            foreach ($values as $key => $value) {
                foreach ($value as $k => $v) {
                    $sets[$key][$k] = $v;
                }
            }
            pdo_update('ching_leeing_sysset', array('sets' => iserializer($sets)), array('id' => $setdata['id']));
        }
        $setdata = pdo_fetch('select * from ' . tablename('ching_leeing_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $uniacid));
        m('cache')->set('sysset', $setdata, $uniacid);
        $this->setGlobalSet();
    }

    public function setGlobalSet()
    {
        $sysset = $this->getSysset();
        $sysset = ((is_array($sysset) ? $sysset : array()));
        $pluginset = $this->getPluginset();
        if (is_array($pluginset)) {
            foreach ($pluginset as $k => $v) {
                $sysset[$k] = $v;
            }
        }
        m('cache')->set('globalset', $sysset);
        return $sysset;
    }

    public function getSysset($key = '', $uniacid = 0)
    {
        global $_W;
        global $_GPC;
        $set = $this->getSetData($uniacid);
        $allset = iunserializer($set['sets']);
        $retsets = array();
        if (!(empty($key))) {
            if (is_array($key)) {
                foreach ($key as $k) {
                    $retsets[$k] = ((isset($allset[$k]) ? $allset[$k] : array()));
                }
            } else {
                $retsets = ((isset($allset[$key]) ? $allset[$key] : array()));
            }
            return $retsets;
        }
        return $allset;
    }

    public function getSetData($uniacid = 0)
    {
        global $_W;
        if (empty($uniacid)) {
            $uniacid = $_W['uniacid'];
        }
        $set = m('cache')->getArray('sysset', $uniacid);
        if (empty($set)) {
            $set = pdo_fetch('select * from ' . tablename('ching_leeing_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $uniacid));
            if (empty($set)) {
                $set = array();
            }
            m('cache')->set('sysset', $set, $uniacid);
        }
        return $set;
    }

    public function getPluginset($key = '', $uniacid = 0)
    {
        global $_W;
        global $_GPC;
        $set = $this->getSetData($uniacid);
        $allset = iunserializer($set['plugins']);
        $retsets = array();
        if (!(empty($key))) {
            if (is_array($key)) {
                foreach ($key as $k) {
                    $retsets[$k] = ((isset($allset[$k]) ? $allset[$k] : array()));
                }
            } else {
                $retsets = ((isset($allset[$key]) ? $allset[$key] : array()));
            }
            return $retsets;
        }
        return $allset;
    }

    public function updatePluginset($values, $uniacid = 0)
    {
        global $_W;
        global $_GPC;
        if (empty($uniacid)) {
            $uniacid = $_W['uniacid'];
        }
        $setdata = pdo_fetch('select id, plugins from ' . tablename('ching_leeing_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $uniacid));
        if (empty($setdata)) {
            pdo_insert('ching_leeing_sysset', array('plugins' => iserializer($values), 'uniacid' => $uniacid));
        } else {
            $plugins = iunserializer($setdata['plugins']);
            foreach ($values as $key => $value) {
                foreach ($value as $k => $v) {
                    $plugins[$key][$k] = $v;
                }
            }
            pdo_update('ching_leeing_sysset', array('plugins' => iserializer($plugins)), array('id' => $setdata['id']));
        }
        $setdata = pdo_fetch('select * from ' . tablename('ching_leeing_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $uniacid));
        m('cache')->set('sysset', $setdata, $uniacid);
        $this->setGlobalSet();
    }

    public function wechat_native_build($params, $wechat, $type = 0, $diy = NULL)
    {
        global $_W;
        if ($diy === NULL) {
            $set = m('common')->getSysset('pay');
            $sec = m('common')->getSec();
            $sec = iunserializer($sec['sec']);
            if (!(empty($set['weixin_jie_sub']))) {
                $wechat = array('appid' => $sec['appid_jie_sub'], 'mch_id' => $sec['mchid_jie_sub'], 'sub_appid' => (!(empty($sec['sub_appid_jie_sub'])) ? $sec['sub_appid_jie_sub'] : ''), 'sub_mch_id' => $sec['sub_mchid_jie_sub'], 'apikey' => $sec['apikey_jie_sub']);
                return $this->wechat_native_child_build($params, $wechat, $type);
            }
        }
        if (!(empty($params['openid']))) {
            $wechat['version'] = 2;
            $wechat['signkey'] = $wechat['apikey'];
            $wechat['mch_id'] = $wechat['mchid'];
            return $this->wechat_build($params, $wechat, $type);
        }
        $package = array();
        $package['appid'] = $wechat['appid'];
        $package['mch_id'] = $wechat['mchid'];
        $package['nonce_str'] = random(32);
        $package['body'] = $params['title'];
        $package['device_info'] = ((isset($params['device_info']) ? 'ching_leeing:' . $params['device_info'] : 'ching_leeing'));
        $package['attach'] = ((isset($params['uniacid']) ? $params['uniacid'] : $_W['uniacid'])) . ':' . $type;
        $package['out_trade_no'] = $params['tid'];
        $package['total_fee'] = $params['fee'] * 100;
        $package['spbill_create_ip'] = CLIENT_IP;
        $package['product_id'] = $params['tid'];
        if (!(empty($params['goods_tag']))) {
            $package['goods_tag'] = $params['goods_tag'];
        }
        $package['time_start'] = date('YmdHis', TIMESTAMP);
        $package['time_expire'] = date('YmdHis', TIMESTAMP + 3600);
        $package['notify_url'] = ((empty($params['notify_url']) ? $_W['siteroot'] . 'addons/ching_leeing/payment/wechat/notify.php' : $params['notify_url']));
        $package['trade_type'] = 'NATIVE';
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach ($package as $key => $v) {
            if (empty($v)) {
                continue;
            }
            $string1 .= $key . '=' . $v . '&';
        }
        $string1 .= 'key=' . $wechat['apikey'];
        $package['sign'] = strtoupper(md5($string1));
        $dat = array2xml($package);
        load()->func('communication');
        $response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);
        if (is_error($response)) {
            return $response;
        }
        libxml_disable_entity_loader(true);
        $xml = simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
        if (strval($xml->return_code) == 'FAIL') {
            return error(-1, strval($xml->return_msg));
        }
        if (strval($xml->result_code) == 'FAIL') {
            return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
        }
        $result = json_decode(json_encode($xml), true);
        return $result;
    }

    public function wechat_native_child_build($params, $wechat, $type = 0)
    {
        global $_W;
        if (!(empty($params['openid']))) {
            return $this->wechat_child_build($params, $wechat, $type);
        }
        $package = array();
        $package['appid'] = $wechat['appid'];
        if (!(empty($wechat['sub_appid']))) {
            $package['sub_appid'] = $wechat['sub_appid'];
        }
        $package['mch_id'] = $wechat['mch_id'];
        $package['sub_mch_id'] = $wechat['sub_mch_id'];
        $package['nonce_str'] = random(32);
        $package['body'] = $params['title'];
        $package['device_info'] = ((isset($params['device_info']) ? 'ching_leeing:' . $params['device_info'] : 'ching_leeing'));
        $package['attach'] = ((isset($params['uniacid']) ? $params['uniacid'] : $_W['uniacid'])) . ':' . $type;
        $package['out_trade_no'] = $params['tid'];
        $package['total_fee'] = $params['fee'] * 100;
        $package['spbill_create_ip'] = CLIENT_IP;
        $package['product_id'] = $params['tid'];
        if (!(empty($params['goods_tag']))) {
            $package['goods_tag'] = $params['goods_tag'];
        }
        $package['time_start'] = date('YmdHis', TIMESTAMP);
        $package['time_expire'] = date('YmdHis', TIMESTAMP + 3600);
        $package['notify_url'] = ((empty($params['notify_url']) ? $_W['siteroot'] . 'addons/ching_leeing/payment/wechat/notify.php' : $params['notify_url']));
        $package['trade_type'] = 'NATIVE';
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach ($package as $key => $v) {
            if (empty($v)) {
                continue;
            }
            $string1 .= $key . '=' . $v . '&';
        }
        $string1 .= 'key=' . $wechat['apikey'];
        $package['sign'] = strtoupper(md5($string1));
        $dat = array2xml($package);
        load()->func('communication');
        $response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);
        if (is_error($response)) {
            return $response;
        }
        libxml_disable_entity_loader(true);
        $xml = simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
        if (strval($xml->return_code) == 'FAIL') {
            return error(-1, strval($xml->return_msg));
        }
        if (strval($xml->result_code) == 'FAIL') {
            return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
        }
        $result = json_decode(json_encode($xml), true);
        return $result;
    }

    public function wechat_child_build($params, $wechat, $type = 0)
    {
        global $_W;
        load()->func('communication');
        $package = array();
        $package['appid'] = $wechat['appid'];
        $package['mch_id'] = $wechat['mch_id'];
        $package['sub_mch_id'] = $wechat['sub_mch_id'];
        $package['nonce_str'] = random(32);
        $package['body'] = $params['title'];
        $package['device_info'] = ((isset($params['device_info']) ? 'ching_leeing:' . $params['device_info'] : 'ching_leeing'));
        $package['attach'] = ((isset($params['uniacid']) ? $params['uniacid'] : $_W['uniacid'])) . ':' . $type;
        $package['out_trade_no'] = $params['tid'];
        $package['total_fee'] = $params['fee'] * 100;
        $package['spbill_create_ip'] = CLIENT_IP;
        $package['product_id'] = $params['goods_id'];
        if (!(empty($params['goods_tag']))) {
            $package['goods_tag'] = $params['goods_tag'];
        }
        $package['time_start'] = date('YmdHis', TIMESTAMP);
        $package['time_expire'] = date('YmdHis', TIMESTAMP + 3600);
        $package['notify_url'] = ((empty($params['notify_url']) ? $_W['siteroot'] . 'addons/ching_leeing/payment/wechat/notify.php' : $params['notify_url']));
        $package['trade_type'] = 'JSAPI';
        if (!(empty($wechat['sub_appid']))) {
            $package['sub_appid'] = $wechat['sub_appid'];
            $package['sub_openid'] = $params['openid'];
        } else {
            $package['openid'] = $params['openid'];
        }
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach ($package as $key => $v) {
            if (empty($v)) {
                continue;
            }
            $string1 .= $key . '=' . $v . '&';
        }
        $string1 .= 'key=' . $wechat['apikey'];
        $package['sign'] = strtoupper(md5($string1));
        $dat = array2xml($package);
        $response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);
        if (is_error($response)) {
            return $response;
        }
        $xml = simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
        if (strval($xml->return_code) == 'FAIL') {
            return error(-1, strval($xml->return_msg));
        }
        if (strval($xml->result_code) == 'FAIL') {
            return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
        }
        libxml_disable_entity_loader(true);
        $prepayid = $xml->prepay_id;
        $wOpt = array('appId' => $wechat['appid'], 'timeStamp' => TIMESTAMP . '', 'nonceStr' => random(32), 'package' => 'prepay_id=' . $prepayid, 'signType' => 'MD5');
        ksort($wOpt, SORT_STRING);
        $string = '';
        foreach ($wOpt as $key => $v) {
            $string .= $key . '=' . $v . '&';
        }
        $string .= 'key=' . $wechat['apikey'];
        $wOpt['paySign'] = strtoupper(md5($string));
        return $wOpt;
    }

    public function wechat_build($params, $wechat, $type = 0)
    {
        global $_W;
        $set = m('common')->getSysset('pay');
        $sec = m('common')->getSec();
        $sec = iunserializer($sec['sec']);
        if (!(empty($set['weixin_sub']))) {
            $wechat = array('appid' => $sec['appid_sub'], 'mch_id' => $sec['mchid_sub'], 'sub_appid' => (!(empty($sec['sub_appid_sub'])) ? $sec['sub_appid_sub'] : ''), 'sub_mch_id' => $sec['sub_mchid_sub'], 'apikey' => $sec['apikey_sub']);
            $params['openid'] = ((isset($params['user']) ? $params['user'] : $_W['openid']));
            return $this->wechat_child_build($params, $wechat, $type);
        }
        load()->func('communication');
        if (empty($wechat['version']) && !(empty($wechat['signkey']))) {
            $wechat['version'] = 1;
        }
        $wOpt = array();
        if ($wechat['version'] == 1) {
            $wOpt['appId'] = $wechat['appid'];
            $wOpt['timeStamp'] = TIMESTAMP . '';
            $wOpt['nonceStr'] = random(32);
            $package = array();
            $package['bank_type'] = 'WX';
            $package['body'] = urlencode($params['title']);
            $package['attach'] = $_W['uniacid'] . ':' . $type;
            $package['partner'] = $wechat['partner'];
            $package['device_info'] = 'ching_leeing';
            $package['out_trade_no'] = $params['tid'];
            $package['total_fee'] = $params['fee'] * 100;
            $package['fee_type'] = '1';
            $package['notify_url'] = $_W['siteroot'] . 'addons/ching_leeing/payment/wechat/notify.php';
            $package['spbill_create_ip'] = CLIENT_IP;
            $package['input_charset'] = 'UTF-8';
            ksort($package);
            $string1 = '';
            foreach ($package as $key => $v) {
                if (empty($v)) {
                    continue;
                }
                $string1 .= $key . '=' . $v . '&';
            }
            $string1 .= 'key=' . $wechat['key'];
            $sign = strtoupper(md5($string1));
            $string2 = '';
            foreach ($package as $key => $v) {
                $v = urlencode($v);
                $string2 .= $key . '=' . $v . '&';
            }
            $string2 .= 'sign=' . $sign;
            $wOpt['package'] = $string2;
            $string = '';
            $keys = array('appId', 'timeStamp', 'nonceStr', 'package', 'appKey');
            sort($keys);
            foreach ($keys as $key) {
                $v = $wOpt[$key];
                if ($key == 'appKey') {
                    $v = $wechat['signkey'];
                }
                $key = strtolower($key);
                $string .= $key . '=' . $v . '&';
            }
            $string = rtrim($string, '&');
            $wOpt['signType'] = 'SHA1';
            $wOpt['paySign'] = sha1($string);
            return $wOpt;
        }
        $package = array();
        $package['appid'] = $wechat['appid'];
        $package['mch_id'] = $wechat['mchid'];
        $package['nonce_str'] = random(32);
        $package['body'] = $params['title'];
        $package['device_info'] = 'ching_leeing';
        $package['attach'] = $_W['uniacid'] . ':' . $type;
        $package['out_trade_no'] = $params['tid'];
        $package['total_fee'] = $params['fee'] * 100;
        $package['spbill_create_ip'] = CLIENT_IP;
        if (!(empty($params['goods_tag']))) {
            $package['goods_tag'] = $params['goods_tag'];
        }
        $package['notify_url'] = $_W['siteroot'] . 'addons/ching_leeing/payment/wechat/notify.php';
        $package['trade_type'] = 'JSAPI';
        $package['openid'] = ((empty($params['openid']) ? $_W['openid'] : $params['openid']));
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach ($package as $key => $v) {
            if (empty($v)) {
                continue;
            }
            $string1 .= $key . '=' . $v . '&';
        }
        $string1 .= 'key=' . $wechat['signkey'];
        $package['sign'] = strtoupper(md5($string1));
        $dat = array2xml($package);
        $response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);
        if (is_error($response)) {
            return $response;
        }
        $xml = @simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
        if (strval($xml->return_code) == 'FAIL') {
            return error(-1, strval($xml->return_msg));
        }
        if (strval($xml->result_code) == 'FAIL') {
            return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
        }
        $prepayid = $xml->prepay_id;
        $wOpt['appId'] = $wechat['appid'];
        $wOpt['timeStamp'] = TIMESTAMP . '';
        $wOpt['nonceStr'] = random(32);
        $wOpt['package'] = 'prepay_id=' . $prepayid;
        $wOpt['signType'] = 'MD5';
        ksort($wOpt, SORT_STRING);
        $string = '';
        foreach ($wOpt as $key => $v) {
            $string .= $key . '=' . $v . '&';
        }
        $string .= 'key=' . $wechat['signkey'];
        $wOpt['paySign'] = strtoupper(md5($string));
        return $wOpt;
    }

    public function authCodeToOpenid($auth_code, $wechat)
    {
        $package = array();
        $package['appid'] = $wechat['appid'];
        $package['mch_id'] = $wechat['mch_id'];
        $package['auth_code'] = $auth_code;
        $package['nonce_str'] = random(32);
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach ($package as $key => $v) {
            $string1 .= $key . '=' . $v . '&';
        }
        $string1 .= 'key=' . $wechat['apikey'];
        $package['sign'] = strtoupper(md5($string1));
        $dat = array2xml($package);
        load()->func('communication');
        $response = ihttp_post('https://api.mch.weixin.qq.com/tools/authcodetoopenid', $dat);
        if (is_error($response)) {
            return $response;
        }
        libxml_disable_entity_loader(true);
        $xml = simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
        if (strval($xml->return_code) == 'FAIL') {
            return error(-1, strval($xml->return_msg));
        }
        if (strval($xml->result_code) == 'FAIL') {
            return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
        }
        $result = json_decode(json_encode($xml), true);
        return $result;
    }

    public function sendredpack($params, $wechat)
    {
        global $_W;
        $package = array();
        $package['wxappid'] = $wechat['appid'];
        $package['mch_id'] = $wechat['mchid'];
        $package['mch_billno'] = $params['tid'];
        $package['send_name'] = $params['send_name'];
        $package['nonce_str'] = random(32);
        $package['re_openid'] = $params['openid'];
        $package['total_amount'] = $params['money'] * 100;
        $package['total_num'] = 1;
        $package['wishing'] = ((isset($params['wishing']) ? $params['wishing'] : '恭喜发财,大吉大利'));
        $package['client_ip'] = CLIENT_IP;
        $package['act_name'] = $params['act_name'];
        $package['remark'] = ((isset($params['remark']) ? $params['remark'] : '暂无备注'));
        $package['scene_id'] = ((isset($params['scene_id']) ? $params['scene_id'] : 'PRODUCT_1'));
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach ($package as $k => $v) {
            $string1 .= $k . '=' . $v . '&';
        }
        $string1 .= 'key=' . $wechat['apikey'];
        $package['sign'] = strtoupper(md5($string1));
        $xml = array2xml($package);
        $extras = array();
        $errmsg = '未上传完整的微信支付证书，请到【系统设置】->【支付方式】中上传!';
        if (is_array($wechat['certs'])) {
            if (empty($wechat['certs']['cert']) || empty($wechat['certs']['key']) || empty($wechat['certs']['root'])) {
                if ($_W['ispost']) {
                    show_json(0, array('message' => $errmsg));
                }
                show_message($errmsg, '', 'error');
            }
            $certfile = IA_ROOT . '/addons/ching_leeing/cert/' . random(128);
            file_put_contents($certfile, $wechat['certs']['cert']);
            $keyfile = IA_ROOT . '/addons/ching_leeing/cert/' . random(128);
            file_put_contents($keyfile, $wechat['certs']['key']);
            $rootfile = IA_ROOT . '/addons/ching_leeing/cert/' . random(128);
            file_put_contents($rootfile, $wechat['certs']['root']);
            $extras['CURLOPT_SSLCERT'] = $certfile;
            $extras['CURLOPT_SSLKEY'] = $keyfile;
            $extras['CURLOPT_CAINFO'] = $rootfile;
        } else {
            if ($_W['ispost']) {
                show_json(0, array('message' => $errmsg));
            }
            show_message($errmsg, '', 'error');
        }
        load()->func('communication');
        $resp = ihttp_request($url, $xml, $extras);
        @unlink($certfile);
        @unlink($keyfile);
        @unlink($rootfile);
        if (is_error($resp)) {
            return error(-2, $resp['message']);
        }
        if (empty($resp['content'])) {
            return error(-2, '网络错误');
        }
        $arr = json_decode(json_encode(simplexml_load_string($resp['content'], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if (($arr['return_code'] == 'SUCCESS') && ($arr['result_code'] == 'SUCCESS')) {
            return true;
        }
        if ($arr['return_msg'] == $arr['err_code_des']) {
            $error = $arr['return_msg'];
        } else {
            $error = $arr['return_msg'] . ' | ' . $arr['err_code_des'];
        }
        return error(-2, $error);
    }

    public function wechat_micropay_build($params, $wechat, $type = 0)
    {
        global $_W;
        load()->func('communication');
        $package = array();
        $package['appid'] = $wechat['appid'];
        $package['mch_id'] = $wechat['mch_id'];
        $package['nonce_str'] = random(32);
        $package['body'] = $params['title'];
        $package['device_info'] = ((isset($params['device_info']) ? 'ching_leeing:' . $params['device_info'] : 'ching_leeing'));
        $package['attach'] = ((isset($params['uniacid']) ? $params['uniacid'] : $_W['uniacid'])) . ':' . $type;
        $package['out_trade_no'] = $params['tid'];
        $package['total_fee'] = $params['fee'] * 100;
        $package['spbill_create_ip'] = CLIENT_IP;
        $package['auth_code'] = $params['auth_code'];
        if (!(empty($wechat['sub_mch_id']))) {
            $package['sub_mch_id'] = $wechat['sub_mch_id'];
        }
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach ($package as $key => $v) {
            if (empty($v)) {
                continue;
            }
            $string1 .= $key . '=' . $v . '&';
        }
        $string1 .= 'key=' . $wechat['apikey'];
        $package['sign'] = strtoupper(md5($string1));
        $dat = array2xml($package);
        $response = ihttp_request('https://api.mch.weixin.qq.com/pay/micropay', $dat);
        if (is_error($response)) {
            return $response;
        }
        $xml = simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode($xml), true);
        if ($result['return_code'] == 'FAIL') {
            return error(-1, $result['return_msg']);
        }
        if ($result['result_code'] == 'FAIL') {
            return error(-2, $result['err_code'] . ': ' . $result['err_code_des']);
        }
        return $result;
    }

    public function wechat_order_query($out_trade_no, $money = 0, $wechat)
    {
        $package = array();
        $package['appid'] = $wechat['appid'];
        $package['mch_id'] = $wechat['mch_id'];
        $package['nonce_str'] = random(32);
        $package['out_trade_no'] = $out_trade_no;
        if (!(empty($wechat['sub_mch_id']))) {
            $package['sub_mch_id'] = $wechat['sub_mch_id'];
        }
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach ($package as $key => $v) {
            if (empty($v)) {
                continue;
            }
            $string1 .= $key . '=' . $v . '&';
        }
        $string1 .= 'key=' . $wechat['apikey'];
        $package['sign'] = strtoupper(md5($string1));
        $dat = array2xml($package);
        load()->func('communication');
        $response = ihttp_request('https://api.mch.weixin.qq.com/pay/orderquery', $dat);
        if (is_error($response)) {
            return $response;
        }
        $xml = simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
        if (strval($xml->return_code) == 'FAIL') {
            return error(-1, strval($xml->return_msg));
        }
        if (strval($xml->result_code) == 'FAIL') {
            return error(-2, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
        }
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode($xml), true);
        if (($result['total_fee'] != $money * 100) && ($money != 0)) {
            return error(-1, '金额出错');
        }
        return $result;
    }

    public function getAccount()
    {
        global $_W;
        load()->model('account');
        if (!(empty($_W['acid']))) {
            return WeAccount::create($_W['acid']);
        }
        $acid = pdo_fetchcolumn('SELECT acid FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid LIMIT 1', array(':uniacid' => $_W['uniacid']));
        return WeAccount::create($acid);
    }

    public function createNO($table, $field, $prefix)
    {
        $billno = date('YmdHis') . random(6, true);
        while (1) {
            $count = pdo_fetchcolumn('select count(*) from ' . tablename('ching_leeing_' . $table) . ' where ' . $field . '=:billno limit 1', array(':billno' => $billno));
            if ($count <= 0) {
                break;
            }
            $billno = date('YmdHis') . random(6, true);
        }
        return $prefix . $billno;
    }

    public function html_images($detail = '', $enforceQiniu = false)
    {
        $detail = htmlspecialchars_decode($detail);
        preg_match_all('/<img.*?src=[\\\\\'| \\"](.*?(?:[\\.gif|\\.jpg|\\.png|\\.jpeg]?))[\\\\\'|\\"].*?[\\/]?>/', $detail, $imgs);
        $images = array();
        if (isset($imgs[1])) {
            foreach ($imgs[1] as $img) {
                $im = array('old' => $img, 'new' => save_media($img, $enforceQiniu));
                $images[] = $im;
            }
        }
        foreach ($images as $img) {
            $detail = str_replace($img['old'], $img['new'], $detail);
        }
        return $detail;
    }

    public function html_to_images($detail = '')
    {
        $detail = htmlspecialchars_decode($detail);
        preg_match_all('/<img.*?src=[\\\\\'| \\"](.*?(?:[\\.gif|\\.jpg|\\.png|\\.jpeg]?))[\\\\\'|\\"].*?[\\/]?>/', $detail, $imgs);
        $images = array();
        if (isset($imgs[1])) {
            foreach ($imgs[1] as $img) {
                $im = array('old' => $img, 'new' => tomedia($img));
                $images[] = $im;
            }
        }
        foreach ($images as $img) {
            $detail = str_replace($img['old'], $img['new'], $detail);
        }
        return $detail;
    }

    public function array_images($arr)
    {
        foreach ($arr as &$a) {
            $a = save_media($a);
        }
        unset($a);
        return $arr;
    }

    public function getSec($uniacid = 0)
    {
        global $_W;
        if (empty($uniacid)) {
            $uniacid = $_W['uniacid'];
        }
        $set = pdo_fetch('select sec from ' . tablename('ching_leeing_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $uniacid));
        if (empty($set)) {
            $set = array();
        }
        return $set;
    }

    public function paylog($log = '')
    {
        global $_W;
        $openpaylog = m('cache')->getString('paylog', 'global');
        if (!(empty($openpaylog))) {
            $path = IA_ROOT . '/addons/ching_leeing/data/paylog/' . $_W['uniacid'] . '/' . date('Ymd');
            if (!(is_dir($path))) {
                load()->func('file');
                @mkdirs($path, '0777');
            }
            $file = $path . '/' . date('H') . '.log';
            file_put_contents($file, $log, FILE_APPEND);
        }
    }

    public function getAreas()
    {
        $file = IA_ROOT . '/addons/ching_leeing/static/js/dist/area/Area.xml';
        $file_str = file_get_contents($file);
        $areas = json_decode(json_encode(simplexml_load_string($file_str)), true);
        return $areas;
    }

    public function getWechats()
    {
        return pdo_fetchall('SELECT  a.uniacid,a.name FROM ' . tablename('ching_leeing_sysset') . ' s  ' . ' left join ' . tablename('account_wechats') . ' a on a.uniacid = s.uniacid' . ' left join ' . tablename('account') . ' acc on acc.uniacid = a.uniacid where acc.isdeleted=0 GROUP BY uniacid');
    }

    public function getCopyright($ismanage = false)
    {
        global $_W;
        $copyrights = m('cache')->getArray('systemcopyright', 'global');
        if (!(is_array($copyrights))) {
            $copyrights = pdo_fetchall('select *  from ' . tablename('ching_leeing_system_copyright'));
            m('cache')->set('systemcopyright', $copyrights, 'global');
        }
        $copyright = false;
        foreach ($copyrights as $cr) {
            if ($cr['uniacid'] == $_W['uniacid']) {
                if ($ismanage && ($cr['ismanage'] == 1)) {
                    $copyright = $cr;
                    break;
                }
                if (!($ismanage) && ($cr['ismanage'] == 0)) {
                    $copyright = $cr;
                    break;
                }
            }
        }
        if (!($copyright)) {
            foreach ($copyrights as $cr) {
                if ($cr['uniacid'] == -1) {
                    if ($ismanage && ($cr['ismanage'] == 1)) {
                        $copyright = $cr;
                        break;
                    }
                    if (!($ismanage) && ($cr['ismanage'] == 0)) {
                        $copyright = $cr;
                        break;
                    }
                }
            }
        }
        return $copyright;
    }

    public function keyExist($key = '')
    {
        global $_W;
        if (empty($key)) {
            return;
        }
        $keyword = pdo_fetch('SELECT * FROM ' . tablename('rule_keyword') . ' WHERE content=:content and uniacid=:uniacid limit 1 ', array(':content' => trim($key), ':uniacid' => $_W['uniacid']));
        if (!(empty($keyword))) {
            $rule = pdo_fetch('SELECT * FROM ' . tablename('rule') . ' WHERE id=:id and uniacid=:uniacid limit 1 ', array(':id' => $keyword['rid'], ':uniacid' => $_W['uniacid']));
            if (!(empty($rule))) {
                return $rule;
            }
        }
    }

    public function createStaticFile($url, $regen = false)
    {
        global $_W;
        if (empty($url)) {
            return;
        }
        $url = preg_replace('/(&|\\?)mid=[^&]+/', '', $url);
        $cache = md5($url) . '_html';
        $content = m('cache')->getString($cache);
        if (empty($content) || $regen) {
            load()->func('communication');
            $resp = ihttp_request($url, array('site' => 'createStaticFile'));
            $content = $resp['content'];
            m('cache')->set($cache, $content);
        }
        return $content;
    }

    public function delrule($rids)
    {
        if (!(is_array($rids))) {
            $rids = array($rids);
        }
        foreach ($rids as $rid) {
            $rid = intval($rid);
            load()->model('reply');
            $reply = reply_single($rid);
            if (pdo_delete('rule', array('id' => $rid))) {
                pdo_delete('rule_keyword', array('rid' => $rid));
                pdo_delete('stat_rule', array('rid' => $rid));
                pdo_delete('stat_keyword', array('rid' => $rid));
                $module = WeUtility::createModule($reply['module']);
                if (method_exists($module, 'ruleDeleted')) {
                    $module->ruleDeleted($rid);
                }
            }
        }
    }

    public function deleteFile($attachment, $fileDelete = false)
    {
        global $_W;
        $attachment = trim($attachment);
        if (empty($attachment)) {
            return false;
        }
        $media = pdo_get('core_attachment', array('uniacid' => $_W['uniacid'], 'attachment' => $attachment));
        if (empty($media)) {
            return false;
        }
        if (empty($_W['isfounder']) && ($_W['role'] != 'manager')) {
            return false;
        }
        if ($fileDelete) {
            load()->func('file');
            if (!(empty($_W['setting']['remote']['type']))) {
                $status = file_remote_delete($media['attachment']);
            } else {
                $status = file_delete($media['attachment']);
            }
            if (is_error($status)) {
                exit($status['message']);
            }
        }
        pdo_delete('core_attachment', array('uniacid' => $_W['uniacid'], 'id' => $media['id']));
        return true;
    }
}

?>