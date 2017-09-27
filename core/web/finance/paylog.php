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

class Paylog_ChingLeeingPage extends WebPage
{
    function main($type = 1)
    {
        global $_W;
        global $_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' and uniacid=:uniacid and money<>0 and tag<> "refund"';
//        $condition = ' and uniacid=:uniacid and money<>0';
        $params = array(':uniacid' => $_W['uniacid']);
        if (!(empty($_GPC['keyword']))) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            if ($_GPC['searchfield'] == 'logno') {
                $condition .= ' and tid like :keyword';
            }
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }
        if (empty($starttime) || empty($endtime)) {
            $starttime = strtotime('-1 month');
            $endtime = time();
        }
        if (!(empty($_GPC['time']['start'])) && !(empty($_GPC['time']['end']))) {
            $starttime = strtotime($_GPC['time']['start']);
            $endtime = strtotime($_GPC['time']['end']);
            $condition .= ' AND createtime >= :starttime AND createtime <= :endtime ';
            $params[':starttime'] = $starttime;
            $params[':endtime'] = $endtime;
        }
        if ($_GPC['status'] != '') {
            $condition .= ' and status=' . intval($_GPC['status']);
        }
        $sql = 'select * from ' . tablename('ching_leeing_paylog') .' where 1' . $condition . ' ORDER BY createtime DESC ';
        $sql .= 'LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $total = pdo_fetchcolumn('select count(*) from ' . tablename('ching_leeing_paylog') . ' where 1'  . $condition . ' ', $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template();
    }
}

?>