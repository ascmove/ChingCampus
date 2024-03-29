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
require IA_ROOT . '/addons/ching_leeing/defines.php';
require CHING_LEEING_INC . 'com_processor.php';

class VerifyProcessor extends ComProcessor
{
    protected $sessionkey;
    protected $errkey;

    public function __construct()
    {
        parent::__construct('verify');
        $this->sessionkey = CHING_LEEING_PREFIX . 'order_wechat_verify_info';
        $this->codekey = CHING_LEEING_PREFIX . 'order_wechat_verify_code';
    }

    public function respond($obj = NULL)
    {
        global $_W;
        $message = $obj->message;
        $openid = $obj->message['from'];
        $content = $obj->message['content'];
        $msgtype = strtolower($message['msgtype']);
        $event = strtolower($message['event']);
        @session_start();
        if (($msgtype == 'text') || ($event == 'click')) {
            $saler = pdo_fetch('select * from ' . tablename('ching_leeing_saler') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
            if (empty($saler)) {
                return $this->responseEmpty();
            }
            $verifyset = m('common')->getSysset('verify');
            if (!empty($verifyset['type'])) {
                return $obj->respText('<a href=\'' . mobileUrl('verify/page', NULL, true) . '\'>点击进入核销台</a>');
            }
            if (!$obj->inContext) {
                unset($_SESSION[$this->sessionkey]);
                unset($_SESSION[$this->codekey]);
                $obj->beginContext();
                return $obj->respText('请输入8位及以上数字订单消费码或自提码:');
            }
            if ($obj->inContext) {
                if (is_numeric($content)) {
                    if (8 <= strlen($content)) {
                        $_SESSION[$this->codekey] = $verifycode = trim($content);
                        $orderid = pdo_fetchcolumn('select id from ' . tablename('ching_leeing_order') . ' where uniacid=:uniacid and ( verifycode=:verifycode or verifycodes like :verifycodes ) limit 1 ', array(':uniacid' => $_W['uniacid'], ':verifycode' => $verifycode, ':verifycodes' => '%|' . $verifycode . '|%'));
                        if (empty($orderid)) {
                            unset($_SESSION[$this->sessionkey]);
                            return $obj->respText('未找到订单，请继续输入，退出请回复 n。');
                        }
                        $allow = com('verify')->allow($orderid, 0, $openid);
                        if (is_error($allow)) {
                            unset($_SESSION[$this->sessionkey]);
                            return $obj->respText($allow['message'] . ' 请输入其他消费码或自提码，退出请回复 n。');
                        }
                        extract($allow);
                        $_SESSION[$this->sessionkey] = json_encode(array('orderid' => $allow['order']['id'], 'verifytype' => $allow['order']['verifytype'], 'lastverifys' => $allow['lastverifys']));
                        $str = '';
                        $str .= '订单：' . $order['ordersn'] . "\r\n" . '金额：' . $order['price'] . ' 元' . "\r\n";
                        $str .= '商品：' . "\r\n";
                        foreach ($allgoods as $index => $g) {
                            $str .= ($index + 1) . '、' . $g['title'] . "\r\n";
                        }
                        if ($order['dispatchtype'] == 1) {
                            $str .= "\r\n" . '信息正确请回复 y 进行自提确认，回复 n 退出。';
                        } else if ($order['verifytype'] == 0) {
                            $str .= "\r\n" . '正确请回复 y 进行订单核销，回复 n 退出。';
                        } else if ($order['verifytype'] == 1) {
                            $str .= "\r\n" . '信息正确请输入核销次数进行核销（可核销剩余 ' . $lastverifys . ' 次），回复 n 退出。';
                        } else if ($order['verifytype'] == 2) {
                            $str .= "\r\n" . '消费码：' . $verifycode;
                            $str .= "\r\n" . '确认信息正确请回复 y 进行确认，回复 n 退出。';
                        }
                        return $obj->respText($str);
                    }
                    if (isset($_SESSION[$this->sessionkey])) {
                        $session = json_decode($_SESSION[$this->sessionkey], true);
                        if ($session['verifytype'] == 1) {
                            if (intval($content) <= 0) {
                                return $obj->respText('订单最少核销 1 次!');
                            }
                            if ($session['lastverifys'] < intval($content)) {
                                return $obj->respText('此订单最多核销 ' . $session['lastverifys'] . ' 次!');
                            }
                            $result = com('verify')->verify($session['orderid'], intval($content), '', $openid);
                            if (is_error($result)) {
                                unset($_SESSION[$this->sessionkey]);
                                return $obj->respText($allow['message'] . ' 请输入其他消费码或自提码，退出请回复 n。');
                            }
                            $obj->endContext();
                            return $obj->respText('核销成功!');
                        }
                    }
                    return $obj->respText('请输入8位及以上数字订单消费码或自提码:');
                }
                if (strtolower($content) == 'y') {
                    if (isset($_SESSION[$this->sessionkey])) {
                        $session = json_decode($_SESSION[$this->sessionkey], true);
                        if ($session['verifytype'] == 1) {
                            return $obj->respText(' 请输入核销次数:');
                        }
                        $result = com('verify')->verify($session['orderid'], 0, $session[$this->codekey], $openid);
                        if (is_error($result)) {
                            unset($_SESSION[$this->sessionkey]);
                            return $obj->respText($result['message'] . ' 请输入其他消费码或自提码，退出请回复 n。');
                        }
                        $obj->endContext();
                        return $obj->respText('核销成功!');
                    }
                    return $obj->respText('请输入8位及以上数字订单消费码或自提码:');
                }
                @session_start();
                unset($_SESSION[$this->sessionkey]);
                unset($_SESSION[$this->codekey]);
                $obj->endContext();
                return $obj->respText('退出成功.');
            }
        }
    }
}

?>