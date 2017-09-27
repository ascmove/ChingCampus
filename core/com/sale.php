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

class Sale_ChingLeeingComModel extends ComModel
{
    public function getEnoughsGoods()
    {
        global $_W;
        global $_S;
        $set = $_S['sale'];
        $goodsids = $set['goodsids'];
        return $goodsids;
    }

    public function getEnoughs()
    {
        global $_W;
        global $_S;
        $set = $_S['sale'];
        $allenoughs = array();
        $enoughs = $set['enoughs'];
        if ((0 < floatval($set['enoughmoney'])) && (0 < floatval($set['enoughdeduct']))) {
            $allenoughs[] = array('enough' => floatval($set['enoughmoney']), 'money' => floatval($set['enoughdeduct']));
        }
        if (is_array($enoughs)) {
            foreach ($enoughs as $e) {
                if ((0 < floatval($e['enough'])) && (0 < floatval($e['give']))) {
                    $allenoughs[] = array('enough' => floatval($e['enough']), 'money' => floatval($e['give']));
                }
            }
        }
        usort($allenoughs, 'sort_enoughs');
        return $allenoughs;
    }

    public function getEnoughFree()
    {
        global $_W;
        global $_S;
        $set = $_S['sale'];
        if (!empty($set['enoughfree'])) {
            return (0 < $set['enoughorder'] ? $set['enoughorder'] : -1);
        }
        return false;
    }

    public function getRechargeActivity()
    {
        global $_S;
        $set = $_S['sale'];
        $recharges = iunserializer($set['recharges']);
        if (is_array($recharges)) {
            usort($recharges, 'sort_enoughs');
            return $recharges;
        }
        return false;
    }

    public function setRechargeActivity($log)
    {
        global $_W;
        $set = m('common')->getPluginset('sale');
        $recharges = iunserializer($set['recharges']);
        $credit2 = 0;
        $enough = 0;
        $give = '';
        if (is_array($recharges)) {
            usort($recharges, 'sort_enoughs');
            foreach ($recharges as $r) {
                if (empty($r['enough']) || empty($r['give'])) {
                    continue;
                }
                if (strexists($r['give'], '%')) {
                    $credit2 = round((floatval(str_replace('%', '', $r['give'])) / 100) * $log['money'], 2);
                } else {
                    $credit2 = round(floatval($r['give']), 2);
                }
                $enough = floatval($r['enough']);
                $give = $r['give'];
                if ($log['money'] >= $enough) {
                    break;
                }
            }
        }
        if ($log['money'] >= $enough) {
            if (0 < $credit2) {
                m('member')->setCredit($log['openid'], 'credit2', $credit2, array('0', $_S['shop']['name'] . '充值满' . $enough . '赠送' . $give, '现金活动'));
                pdo_update('ching_leeing_member_log', array('gives' => $credit2), array('id' => $log['id']));
            }
        }
    }
}

function sort_enoughs($a, $b)
{
    $enough1 = floatval($a['enough']);
    $enough2 = floatval($b['enough']);
    if ($enough1 == $enough2) {
        return 0;
    }
    return ($enough1 < $enough2 ? 1 : -1);
}

?>