<?php
/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

?>
<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

class Sms_ChingLeeingComModel extends ComModel
{
    public function sms_temp()
    {
        global $_W;
        $list = pdo_fetchall('SELECT id, `type`, `name` FROM ' . tablename('ching_leeing_sms') . ' WHERE status=1 and uniacid=:uniacid ', array(':uniacid' => $_W['uniacid']));
        foreach ($list as $i => &$item) {
            if ($item['type'] == 'juhe') {
                $item['name'] = '[聚合]' . $item['name'];
            } else if ($item['type'] == 'dayu') {
                $item['name'] = '[大于]' . $item['name'];
            } else if ($item['type'] == 'emay') {
                $item['name'] = '[亿美]' . $item['name'];
            }
        }
        unset($item);
        return $list;
    }

    public function callsms(array $params)
    {
        global $_W;
        $tag = ((isset($params['tag']) ? $params['tag'] : ''));
        $datas = ((isset($params['datas']) ? $params['datas'] : array()));
        $tm = $_W['shopset']['notice'];
        if (empty($tm)) {
            $tm = m('common')->getSysset('notice');
        }
        $smsid = $tm[$tag . '_sms'];
        $smsclose = $tm[$tag . '_close_sms'];
        if (!empty($smsid) && empty($smsclose) && !empty($params['mobile'])) {
            $sms_data = array();
            foreach ($datas as $i => $value) {
                $sms_data[$value['name']] = $value['value'];
            }
            $this->send($params['mobile'], $smsid, $sms_data);
        }
    }

    public function send($mobile, $tplid, $data, $replace = true)
    {
        global $_W;
        $smsset = $this->sms_set();
        $template = $this->sms_verify($tplid, $smsset);
        if (empty($template['status'])) {
            return $template;
        }
        $params = $this->sms_data($template['type'], $data, $replace, $template);
        if ($template['type'] == 'juhe') {
            $data = array('mobile' => $mobile, 'tpl_id' => $template['smstplid'], 'tpl_value' => $params, 'key' => $smsset['juhe_key']);
            $result = $this->http_post('http://v.juhe.cn/sms/send', $data);
            if (empty($result) || (0 < $result['error_code'])) {
                return array('status' => 0, 'message' => '短信发送失败(' . $result['error_code'] . ')：' . $result['reason']);


            }
        }


        if ($template['type'] == 'dayu') {
            include_once CHING_LEEING_VENDOR . 'dayu/TopSdk.php';
            $dayuClient = new TopClient();
            $dayuClient->appkey = $smsset['dayu_key'];
            $dayuClient->secretKey = $smsset['dayu_secret'];
            $dayuRequest = new AlibabaAliqinFcSmsNumSendRequest();
            $dayuRequest->setSmsType('normal');
            $dayuRequest->setSmsFreeSignName($template['smssign']);
            $dayuRequest->setSmsParam($params);
            $dayuRequest->setRecNum('' . $mobile);
            $dayuRequest->setSmsTemplateCode($template['smstplid']);
            $dayuResult = $dayuClient->execute($dayuRequest);
            $dayuResult = (array)$dayuResult;
            if (empty($dayuResult) || !empty($dayuResult['code'])) {
                return array('status' => 0, 'message' => '短信发送失败(' . $dayuResult['sub_msg'] . '/code: ' . $dayuResult['code'] . '/sub_code: ' . $dayuResult['sub_code'] . ')');

            }
        }


        if ($template['type'] == 'emay') {
            include_once CHING_LEEING_VENDOR . 'emay/SMSUtil.php';
            $balance = $this->sms_num('emay', $smsset);
            if ($balance <= 0) {
                return array('status' => 0, 'message' => '短信发送失败(亿美软通余额不足, 当前余额' . $balance . ')');
            }
            $emayClient = new SMSUtil($smsset['emay_url'], $smsset['emay_sn'], $smsset['emay_pw'], $smsset['emay_sk'], array('proxyhost' => $smsset['emay_phost'], 'proxyport' => $smsset['pport'], 'proxyusername' => $smsset['puser'], 'proxypassword' => $smsset['ppw']), $smsset['emay_out'], $smsset['emay_outresp']);
            $emayResult = $emayClient->send($mobile, '【' . $template['smssign'] . '】' . $params);
            if (!empty($emayResult)) {
                return array('status' => 0, 'message' => '短信发送失败(错误信息: ' . $emayResult . ')');
            }
        }


        return array('status' => 1);
    }

    public function sms_set()
    {
        global $_W;
        return pdo_fetch('SELECT * FROM ' . tablename('ching_leeing_sms_set') . ' WHERE uniacid=:uniacid ', array(':uniacid' => $_W['uniacid']));
    }

    protected function sms_verify($tplid, $smsset)
    {
        global $_W;
        $template = pdo_fetch('SELECT * FROM ' . tablename('ching_leeing_sms') . ' WHERE id=:id and uniacid=:uniacid ', array(':id' => $tplid, ':uniacid' => $_W['uniacid']));
        $template['data'] = iunserializer($template['data']);
        if (empty($template)) {
            return array('status' => 0, 'message' => '模板不存在!');
        }
        if (empty($template['status'])) {
            return array('status' => 0, 'message' => '模板未启用!');
        }
        if (empty($template['type'])) {
            return array('status' => 0, 'message' => '模板类型错误!');
        }
        if ($template['type'] == 'juhe') {
            if (empty($smsset['juhe'])) {
                return array('status' => 0, 'message' => '未开启聚合数据!');
            }
            if (empty($smsset['juhe_key'])) {
                return array('status' => 0, 'message' => '未填写聚合数据Key!');
            }
            if (empty($template['data']) || !is_array($template['data'])) {
                return array('status' => 0, 'message' => '模板类型错误!');
            }
        } else if ($template['type'] == 'dayu') {
            if (empty($smsset['dayu'])) {
                return array('status' => 0, 'message' => '未开启阿里大于!');
            }
            if (empty($smsset['dayu_key'])) {
                return array('status' => 0, 'message' => '未填写阿里大于Key!');
            }
            if (empty($smsset['dayu_secret'])) {
                return array('status' => 0, 'message' => '未填写阿里大于Secret!');
            }
            if (empty($template['data']) || !is_array($template['data'])) {
                return array('status' => 0, 'message' => '模板类型错误!');
            }
            if (empty($template['smssign'])) {
                return array('status' => 0, 'message' => '未填写阿里大于短信签名!');
            }
        } else if ($template['type'] == 'emay') {
            if (empty($smsset['emay'])) {
                return array('status' => 0, 'message' => '未开启亿美软通!');
            }
            if (empty($smsset['emay_url'])) {
                return array('status' => 0, 'message' => '未填写亿美软通网关!');
            }
            if (empty($smsset['emay_sn'])) {
                return array('status' => 0, 'message' => '未填写亿美软通序列号!');
            }
            if (empty($smsset['emay_pw'])) {
                return array('status' => 0, 'message' => '未填写亿美软通密码!');
            }
            if (empty($smsset['emay_sk'])) {
                return array('status' => 0, 'message' => '未填写亿美软通SessionKey!');
            }
            if (empty($template['smssign'])) {
                return array('status' => 0, 'message' => '未填写亿美软通短信签名!');
            }
        }
        return $template;
    }

    protected function sms_data($type, $data, $replace, $template)
    {
        if ($replace) {
            if ($type == 'emay') {
                $tempdata = $template['content'];
                foreach ($data as $key => $value) {
                    $tempdata = str_replace('[' . $key . ']', $value, $tempdata);
                }
                $data = $tempdata;
            } else {
                $tempdata = iunserializer($template['data']);
                foreach ($tempdata as &$td) {
                    foreach ($data as $key => $value) {
                        $td['data_shop'] = str_replace('[' . $key . ']', $value, $td['data_shop']);
                    }
                }
                unset($td);
                $newdata = array();
                foreach ($tempdata as $td) {
                    $newdata[$td['data_temp']] = $td['data_shop'];
                }
                $data = $newdata;
            }
        }
        if ($type == 'juhe') {
            $result = '';
            $count = count($data);
            $i = 0;
            foreach ($data as $key => $value) {
                if ((0 < $i) && ($i < $count)) {
                    $result .= '&';
                }
                $result .= '#' . $key . '#=' . $value;
                ++$i;
            }
        } else if ($type == 'dayu') {
            $result = json_encode($data);
        } else if ($type == 'emay') {
            $result = $data;
        }
        return $result;
    }

    protected function http_post($url, $postData)
    {
        $postData = http_build_query($postData);
        $options = array('http' => array('method' => 'POST', 'header' => 'Content-type:application/x-www-form-urlencoded', 'content' => $postData, 'timeout' => 15 * 60));
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if (!is_array($result)) {
            $result = json_decode($result, true);
        }
        return $result;
    }

    public function sms_num($type, $smsset = NULL)
    {
        if (empty($type)) {
            return NULL;
        }
        if (empty($smsset) || !is_array($smsset)) {
            $smsset = $this->sms_set();
        }
        if ($type == 'emay') {
            include_once CHING_LEEING_VENDOR . 'emay/SMSUtil.php';
            $emayClient = new SMSUtil($smsset['emay_url'], $smsset['emay_sn'], $smsset['emay_pw'], $smsset['emay_sk'], array('proxyhost' => $smsset['emay_phost'], 'proxyport' => $smsset['pport'], 'proxyusername' => $smsset['puser'], 'proxypassword' => $smsset['ppw']), $smsset['emay_out'], $smsset['emay_outresp']);
            $num = $emayClient->getBalance();
            if (!empty($smsset['emay_warn']) && !empty($smsset['emay_mobile']) && ($num < $smsset['emay_warn']) && (($smsset['emay_warn_time'] + (60 * 60 * 24)) < time())) {
                $emayClient = new SMSUtil($smsset['emay_url'], $smsset['emay_sn'], $smsset['emay_pw'], $smsset['emay_sk'], array('proxyhost' => $smsset['emay_phost'], 'proxyport' => $smsset['pport'], 'proxyusername' => $smsset['puser'], 'proxypassword' => $smsset['ppw']), $smsset['emay_out'], $smsset['emay_outresp']);
                $emayResult = $emayClient->send($smsset['emay_mobile'], '【系统预警】' . '您的亿美软通SMS余额为:' . $num . '，低于预警值:' . $smsset['emay_warn'] . ' (24小时内仅通知一次)');
                if (empty($emayResult)) {
                    pdo_update('ching_leeing_sms_set', array('emay_warn_time' => time()), array('id' => $smsset['id']));
                }
            }
            return $num;
        }
    }
}

?>