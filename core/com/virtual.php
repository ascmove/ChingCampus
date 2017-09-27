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

class Virtual_ChingLeeingComModel extends ComModel
{
    public function pay($order)
    {
        global $_W;
        global $_GPC;
        $goods = pdo_fetch('select id,goodsid,total,realprice from ' . tablename('ching_leeing_order_goods') . ' where  orderid=:orderid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':orderid' => $order['id']));
        $g = pdo_fetch('select id,credit,sales,salesreal from ' . tablename('ching_leeing_goods') . ' where  id=:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $goods['goodsid']));
        $virtual_data = pdo_fetchall('SELECT id,typeid,fields FROM ' . tablename('ching_leeing_virtual_data') . ' WHERE typeid=:typeid and openid=:openid and uniacid=:uniacid and merchid = :merchid order by rand() limit ' . $goods['total'], array(':openid' => '', ':typeid' => $order['virtual'], ':uniacid' => $_W['uniacid'], ':merchid' => $order['merchid']));
        $type = pdo_fetch('select fields from ' . tablename('ching_leeing_virtual_type') . ' where id=:id and uniacid=:uniacid and merchid = :merchid limit 1 ', array(':id' => $order['virtual'], ':uniacid' => $_W['uniacid'], ':merchid' => $order['merchid']));
        $fields = iunserializer($type['fields'], true);
        $virtual_info = array();
        $virtual_str = array();
        foreach ($virtual_data as $vd) {
            $virtual_info[] = $vd['fields'];
            $strs = array();
            $vddatas = iunserializer($vd['fields']);
            foreach ($vddatas as $vk => $vv) {
                $strs[] = $fields[$vk] . ': ' . $vv;
            }
            $virtual_str[] = implode(' ', $strs);
            pdo_update('ching_leeing_virtual_data', array('openid' => $order['openid'], 'orderid' => $order['id'], 'ordersn' => $order['ordersn'], 'price' => round($goods['realprice'] / $goods['total'], 2), 'usetime' => time()), array('id' => $vd['id']));
            pdo_update('ching_leeing_virtual_type', 'usedata=usedata+1', array('id' => $vd['typeid']));
            $this->updateStock($vd['typeid']);
        }
        $virtual_str = implode("\n", $virtual_str);
        $virtual_info = '[' . implode(',', $virtual_info) . ']';
        $time = time();
        pdo_update('ching_leeing_order', array('virtual_info' => $virtual_info, 'virtual_str' => $virtual_str, 'status' => '3', 'paytime' => $time, 'sendtime' => $time, 'finishtime' => $time), array('id' => $order['id']));
        $credits = $goods['total'] * $g['credit'];
        if (0 < $credits) {
            $shopset = m('common')->getSysset('shop');
            m('member')->setCredit($order['openid'], 'credit1', $credits, array(0, $shopset['name'] . '购物积分 订单号: ' . $order['ordersn']));
        }
        $salesreal = pdo_fetchcolumn('select ifnull(sum(total),0) from ' . tablename('ching_leeing_order_goods') . ' og ' . ' left join ' . tablename('ching_leeing_order') . ' o on o.id = og.orderid ' . ' where og.goodsid=:goodsid and o.status>=1 and o.uniacid=:uniacid limit 1', array(':goodsid' => $g['id'], ':uniacid' => $_W['uniacid']));
        pdo_update('ching_leeing_goods', array('salesreal' => $salesreal), array('id' => $g['id']));
        m('member')->upgradeLevel($order['openid']);
        m('notice')->sendOrderMessage($order['id']);
        m('order')->setGiveBalance($order['id'], 1);
        if (com('coupon')) {
            com('coupon')->sendcouponsbytask($order['id']);
        }
        if (com('coupon') && !(empty($order['couponid']))) {
            com('coupon')->backConsumeCoupon($order['id']);
        }
        if (p('commission')) {
            p('commission')->checkOrderPay($order['id']);
            p('commission')->checkOrderFinish($order['id']);
        }
        return true;
    }

    public function updateStock($typeid = 0)
    {
        global $_W;
        $goodsids = array();
        $goods = pdo_fetchall('select id from ' . tablename('ching_leeing_goods') . ' where type=3 and `virtual`=:virtual and uniacid=:uniacid limit 1', array(':virtual' => $typeid, ':uniacid' => $_W['uniacid']));
        foreach ($goods as $g) {
            $goodsids[] = $g['id'];
        }
        $alloptions = pdo_fetchall('select id, goodsid from ' . tablename('ching_leeing_goods_option') . ' where `virtual`=:virtual and uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':virtual' => $typeid));
        foreach ($alloptions as $opt) {
            if (!(in_array($opt['goodsid'], $goodsids))) {
                $goodsids[] = $opt['goodsid'];
            }
        }
        foreach ($goodsids as $gid) {
            $this->updateGoodsStock($gid);
        }
    }

    public function updateGoodsStock($id = 0)
    {
        global $_W;
        global $_GPC;
        $goods = pdo_fetch('select `virtual`,merchid from ' . tablename('ching_leeing_goods') . ' where id=:id and type=3 and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if (empty($goods)) {
            return;
        }
        $merchid = $goods['merchid'];
        $stock = 0;
        if (!(empty($goods['virtual']))) {
            $stock = pdo_fetchcolumn('select count(*) from ' . tablename('ching_leeing_virtual_data') . ' where typeid=:typeid and uniacid=:uniacid and merchid = :merchid and openid=\'\' limit 1', array(':typeid' => $goods['virtual'], ':uniacid' => $_W['uniacid'], ':merchid' => $merchid));
        } else {
            $virtuals = array();
            $alloptions = pdo_fetchall('select id, `virtual` from ' . tablename('ching_leeing_goods_option') . ' where goodsid=' . $id);
            foreach ($alloptions as $opt) {
                if (empty($opt['virtual'])) {
                    continue;
                }
                $c = pdo_fetchcolumn('select count(*) from ' . tablename('ching_leeing_virtual_data') . ' where typeid=:typeid and uniacid=:uniacid and merchid = :merchid and openid=\'\' limit 1', array(':typeid' => $opt['virtual'], ':uniacid' => $_W['uniacid'], ':merchid' => $merchid));
                pdo_update('ching_leeing_goods_option', array('stock' => $c), array('id' => $opt['id']));
                if (!(in_array($opt['virtual'], $virtuals))) {
                    $virtuals[] = $opt['virtual'];
                    $stock += $c;
                }
            }
        }
        pdo_update('ching_leeing_goods', array('total' => $stock), array('id' => $id));
    }
}

?>