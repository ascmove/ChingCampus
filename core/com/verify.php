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

class Verify_ChingLeeingComModel extends ComModel
{
    public function createQrcode($orderid = 0)
    {
        global $_W;
        global $_GPC;
        $path = IA_ROOT . '/addons/ching_leeing/data/qrcode/' . $_W['uniacid'];
        if (!(is_dir($path))) {
            load()->func('file');
            mkdirs($path);
        }
        $url = mobileUrl('verify/detai', array('id' => $orderid));
        $file = 'order_verify_qrcode_' . $orderid . '.png';
        $qrcode_file = $path . '/' . $file;
        if (!(is_file($qrcode_file))) {
            require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
            QRcode::png($url, $qrcode_file, QR_ECLEVEL_H, 4);
        }
        return $_W['siteroot'] . '/addons/ching_leeing/data/qrcode/' . $_W['uniacid'] . '/' . $file;
    }

    public function verify($orderid = 0, $times = 0, $verifycode = '', $openid = '')
    {
        global $_W;
        global $_GPC;
        $current_time = time();
        if (empty($openid)) {
            $openid = $_W['openid'];
        }
        $data = $this->allow($orderid, $times, $openid);
        if (is_error($data)) {
            return;
        }
        extract($data);
        if ($order['isverify']) {
            if ($order['verifytype'] == 0) {
                pdo_update('ching_leeing_order', array('status' => 3, 'sendtime' => $current_time, 'finishtime' => $current_time, 'verifytime' => $current_time, 'verified' => 1, 'verifyopenid' => $openid, 'verifystoreid' => $saler['storeid']), array('id' => $order['id']));
                $this->finish($openid, $order);
                m('order')->setGiveBalance($orderid, 1);
                m('notice')->sendOrderMessage($orderid);
                com_run('printer::sendOrderMessage', $orderid, array('type' => 0));
            } else if ($order['verifytype'] == 1) {
                $verifyinfo = iunserializer($order['verifyinfo']);
                $i = 1;
                while ($i <= $times) {
                    $verifyinfo[] = array('verifyopenid' => $openid, 'verifystoreid' => $store['id'], 'verifytime' => $current_time);
                    ++$i;
                }
                pdo_update('ching_leeing_order', array('verifyinfo' => iserializer($verifyinfo)), array('id' => $orderid));
                com_run('printer::sendOrderMessage', $orderid, array('type' => 1, 'times' => $times, 'lastverifys' => $data['lastverifys'] - $times));
                if ($order['status'] != 3) {
                    pdo_update('ching_leeing_order', array('status' => 3, 'sendtime' => $current_time, 'finishtime' => $current_time), array('id' => $order['id']));
                    $this->finish($openid, $order);
                    m('order')->setGiveBalance($orderid, 1);
                    m('notice')->sendOrderMessage($orderid);
                }
            } else if ($order['verifytype'] == 2) {
                $verifyinfo = iunserializer($order['verifyinfo']);
                if (!(empty($verifycode))) {
                    foreach ($verifyinfo as &$v) {
                        if (!($v['verified']) && (trim($v['verifycode']) === trim($verifycode))) {
                            $v['verifyopenid'] = $openid;
                            $v['verifystoreid'] = $store['id'];
                            $v['verifytime'] = $current_time;
                            $v['verified'] = 1;
                        }
                    }
                    unset($v);
                    com_run('printer::sendOrderMessage', $orderid, array('type' => 2, 'verifycode' => $verifycode, 'lastverifys' => $data['lastverifys'] - 1));
                } else {
                    $selecteds = array();
                    $printer_code = array();
                    $printer_code_all = array();
                    foreach ($verifyinfo as $v) {
                        if ($v['select']) {
                            $selecteds[] = $v;
                            $printer_code[] = $v['verifycode'];
                        }
                        $printer_code_all[] = $v['verifycode'];
                    }
                    if (count($selecteds) <= 0) {
                        foreach ($verifyinfo as &$v) {
                            $v['verifyopenid'] = $openid;
                            $v['verifystoreid'] = $store['id'];
                            $v['verifytime'] = $current_time;
                            $v['verified'] = 1;
                            unset($v['select']);
                        }
                        unset($v);
                        com_run('printer::sendOrderMessage', $orderid, array('type' => 2, 'verifycode' => implode(',', $printer_code_all), 'lastverifys' => 0));
                    } else {
                        foreach ($verifyinfo as &$v) {
                            if ($v['select']) {
                                $v['verifyopenid'] = $openid;
                                $v['verifystoreid'] = $store['id'];
                                $v['verifytime'] = $current_time;
                                $v['verified'] = 1;
                                unset($v['select']);
                            }
                        }
                        unset($v);
                        com_run('printer::sendOrderMessage', $orderid, array('type' => 2, 'verifycode' => implode(',', $printer_code), 'lastverifys' => $data['lastverifys'] - count($selecteds)));
                    }
                }
                pdo_update('ching_leeing_order', array('verifyinfo' => iserializer($verifyinfo)), array('id' => $order['id']));
                if ($order['status'] != 3) {
                    pdo_update('ching_leeing_order', array('status' => 3, 'sendtime' => $current_time, 'finishtime' => $current_time, 'verifytime' => $current_time, 'verified' => 1, 'verifyopenid' => $openid, 'verifystoreid' => $saler['storeid']), array('id' => $order['id']));
                    $this->finish($openid, $order);
                    m('order')->setGiveBalance($orderid, 1);
                    m('notice')->sendOrderMessage($orderid);
                    $this->finish(array('status' => 3, 'sendtime' => $current_time, 'finishtime' => $current_time, 'verifytime' => $current_time, 'verified' => 1, 'verifyopenid' => $openid, 'verifystoreid' => $saler['storeid']), $order);
                }
            }
        } else if ($order['dispatchtype'] == 1) {
            pdo_update('ching_leeing_order', array('status' => 3, 'fetchtime' => $current_time, 'sendtime' => $current_time, 'finishtime' => $current_time, 'verifytime' => $current_time, 'verified' => 1, 'verifyopenid' => $openid, 'verifystoreid' => $saler['storeid']), array('id' => $order['id']));
            $this->finish($openid, $order);
            m('order')->setGiveBalance($orderid, 1);
            com_run('printer::sendOrderMessage', $orderid, array('type' => 0));
            m('notice')->sendOrderMessage($orderid);
        }
        return true;
    }

    public function allow($orderid, $times = 0, $verifycode = '', $openid = '')
    {
        global $_W;
        global $_GPC;
        if (empty($openid)) {
            $openid = $_W['openid'];
        }
        $uniacid = $_W['uniacid'];
        $store = false;
        $merchid = 0;
        $lastverifys = 0;
        $verifyinfo = false;
        if ($times <= 0) {
            $times = 1;
        }
        $merch_plugin = p('merch');
        $order = pdo_fetch('select * from ' . tablename('ching_leeing_order') . ' where id=:id and uniacid=:uniacid  limit 1', array(':id' => $orderid, ':uniacid' => $uniacid));
        if (empty($order)) {
            return error(-1, '订单不存在!');
        }
        if (empty($order['isverify']) && empty($order['dispatchtype'])) {
            return error(-1, '订单无需核销!');
        }
        $merchid = $order['merchid'];
        if (empty($merchid)) {
            $saler = pdo_fetch('select * from ' . tablename('ching_leeing_saler') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
        } else if ($merch_plugin) {
            $saler = pdo_fetch('select * from ' . tablename('ching_leeing_merch_saler') . ' where openid=:openid and uniacid=:uniacid and merchid=:merchid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid, ':merchid' => $merchid));
        }
        if (empty($saler)) {
            return error(-1, '无核销权限!');
        }
        $allgoods = pdo_fetchall('select og.goodsid,og.price,g.title,g.thumb,og.total,g.credit,og.optionid,o.title as optiontitle,g.isverify,g.storeids from ' . tablename('ching_leeing_order_goods') . ' og ' . ' left join ' . tablename('ching_leeing_goods') . ' g on g.id=og.goodsid ' . ' left join ' . tablename('ching_leeing_goods_option') . ' o on o.id=og.optionid ' . ' where og.orderid=:orderid and og.uniacid=:uniacid ', array(':uniacid' => $uniacid, ':orderid' => $orderid));
        if (empty($allgoods)) {
            return error(-1, '订单异常!');
        }
        $goods = $allgoods[0];
        if ($order['isverify']) {
            if (count($allgoods) != 1) {
                return error(-1, '核销单异常!');
            }
            if ((0 < $order['refundid']) && (0 < $order['refundstate'])) {
                return error(-1, '订单维权中,无法核销!');
            }
            if (($order['status'] == -1) && (0 < $order['refundtime'])) {
                return error(-1, '订单状态变更,无法核销!');
            }
            $storeids = array();
            if (!(empty($goods['storeids']))) {
                $storeids = explode(',', $goods['storeids']);
            }
            if (!(empty($storeids))) {
                if (!(empty($saler['storeid']))) {
                    if (!(in_array($saler['storeid'], $storeids))) {
                        return error(-1, '您无此门店的核销权限!');
                    }
                }
            }
            if ($order['verifytype'] == 0) {
                if (!(empty($order['verified']))) {
                    return error(-1, '此订单已核销!');
                    if ($order['verifytype'] == 1) {
                        $verifyinfo = iunserializer($order['verifyinfo']);
                        if (!(is_array($verifyinfo))) {
                            $verifyinfo = array();
                        }
                        $lastverifys = $goods['total'] - count($verifyinfo);
                        if ($lastverifys <= 0) {
                            return error(-1, '此订单已全部使用!');
                        }
                        if ($lastverifys < $times) {
                            return error(-1, '最多核销 ' . $lastverifys . ' 次!');
                            if ($order['verifytype'] == 2) {
                                $verifyinfo = iunserializer($order['verifyinfo']);
                                $verifys = 0;
                                foreach ($verifyinfo as $v) {
                                    if (!(empty($verifycode)) && (trim($v['verifycode']) === trim($verifycode))) {
                                        if ($v['verified']) {
                                            return error(-1, '消费码 ' . $verifycode . ' 已经使用!');
                                        }
                                    }
                                    if ($v['verified']) {
                                        ++$verifys;
                                    }
                                }
                                $lastverifys = count($verifyinfo) - $verifys;
                                if (count($verifyinfo) <= $verifys) {
                                    return error(-1, '消费码都已经使用过了!');
                                }
                            }
                        }
                    } else {
                        $verifyinfo = iunserializer($order['verifyinfo']);
                        $verifys = 0;
                        return error(-1, '消费码 ' . $verifycode . ' 已经使用!');
                        ++$verifys;
                        $lastverifys = count($verifyinfo) - $verifys;
                        return error(-1, '消费码都已经使用过了!');
                    }
                }
            } else {
                $verifyinfo = iunserializer($order['verifyinfo']);
                $verifyinfo = array();
                $lastverifys = $goods['total'] - count($verifyinfo);
                return error(-1, '此订单已全部使用!');
                return error(-1, '最多核销 ' . $lastverifys . ' 次!');
                $verifyinfo = iunserializer($order['verifyinfo']);
                $verifys = 0;
                return error(-1, '消费码 ' . $verifycode . ' 已经使用!');
                ++$verifys;
                $lastverifys = count($verifyinfo) - $verifys;
                return error(-1, '消费码都已经使用过了!');
            }
            if (!(empty($saler['storeid']))) {
                if (0 < $merchid) {
                    $store = pdo_fetch('select * from ' . tablename('ching_leeing_merch_store') . ' where id=:id and uniacid=:uniacid and merchid = :merchid limit 1', array(':id' => $saler['storeid'], ':uniacid' => $_W['uniacid'], ':merchid' => $merchid));
                } else {
                    $store = pdo_fetch('select * from ' . tablename('ching_leeing_store') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $saler['storeid'], ':uniacid' => $_W['uniacid']));
                }
            }
        } else if ($order['dispatchtype'] == 1) {
            if (3 <= $order['status']) {
                return error(-1, '订单已经完成，无法进行自提!');
            }
            if ((0 < $order['refundid']) && (0 < $order['refundstate'])) {
                return error(-1, '订单维权中,无法进行自提!');
            }
            if (($order['status'] == -1) && (0 < $order['refundtime'])) {
                return error(-1, '订单状态变更,无法进行自提!');
            }
            if (!(empty($order['storeid']))) {
                if (0 < $merchid) {
                    $store = pdo_fetch('select * from ' . tablename('ching_leeing_merch_store') . ' where id=:id and uniacid=:uniacid and merchid = :merchid limit 1', array(':id' => $order['storeid'], ':uniacid' => $_W['uniacid'], ':merchid' => $merchid));
                } else {
                    $store = pdo_fetch('select * from ' . tablename('ching_leeing_store') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $order['storeid'], ':uniacid' => $_W['uniacid']));
                }
            }
            if (empty($store)) {
                return error(-1, '订单未选择自提门店!');
            }
            if (!(empty($saler['storeid']))) {
                if ($saler['storeid'] != $order['storeid']) {
                    return error(-1, '您无此门店的自提权限!');
                }
            }
        }
        $carrier = unserialize($order['carrier']);
        return array('order' => $order, 'store' => $store, 'saler' => $saler, 'lastverifys' => $lastverifys, 'allgoods' => $allgoods, 'goods' => $goods, 'verifyinfo' => $verifyinfo, 'carrier' => $carrier);
    }

    protected function finish($openid, $order)
    {
        m('member')->upgradeLevel($openid);
        if (com('coupon')) {
            $refurnid = com('coupon')->sendcouponsbytask($order['id']);
        }
        if (com('coupon') && !(empty($order['couponid']))) {
            com('coupon')->backConsumeCoupon($order['id']);
        }
        if (p('commission')) {
            p('commission')->checkOrderFinish($order['id']);
        }
    }

    public function perms()
    {
        return array('verify' => array('text' => $this->getName(), 'isplugin' => true, 'child' => array('keyword' => array('text' => '关键词设置-log'), 'store' => array('text' => '门店', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'saler' => array('text' => '核销员', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'))));
    }

    public function getSalerInfo($openid, $merchid = 0)
    {
        global $_W;
        $condition = ' s.uniacid = :uniacid and s.openid = :openid';
        $params = array(':uniacid' => $_W['uniacid'], ':openid' => $openid);
        if (empty($merchid)) {
            $table_name = tablename('ching_leeing_saler');
        } else {
            $table_name = tablename('ching_leeing_merch_saler');
            $condition .= ' and s.merchid = :merchid';
            $params['merchid'] = $merchid;
        }
        $sql = 'SELECT m.id as salerid,m.nickname as salernickname,s.salername FROM ' . $table_name . '  s ' . ' left join ' . tablename('ching_leeing_member') . ' m on s.openid=m.openid and m.uniacid = s.uniacid ' . ' WHERE ' . $condition . ' Limit 1';
        $data = pdo_fetch($sql, $params);
        return $data;
    }
}

?>