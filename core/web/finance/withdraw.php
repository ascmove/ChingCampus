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

class Withdraw_ChingLeeingPage extends WebPage
{
    function main($type = 1)
    {
        global $_W;
        global $_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' and uniacid=:uniacid and money<>0';
        $params = array(':uniacid' => $_W['uniacid']);
        if (!(empty($_GPC['keyword']))) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            if ($_GPC['searchfield'] == 'logno') {
                $condition .= ' and logno like :keyword';
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
        $sql = 'select * from ' . tablename('ching_leeing_member_log') .' where 1 and datatype = 0' . $condition . ' ORDER BY createtime DESC ';
        $list = pdo_fetchall($sql, $params);
        if (!(empty($list))) {
            foreach ($list as $key => $value) {
                $list[$key]['typestr'] = $apply_type[$value['applytype']];
                if ($value['deductionmoney'] == 0) {
                    $list[$key]['realmoney'] = $value['money'];
                }
            }
        }
        $total = pdo_fetchcolumn('select count(*) from ' . tablename('ching_leeing_member_log') . ' where 1 and datatype = 0'  . $condition . ' ', $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template();
    }
    public function manual()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $log = pdo_fetch('select * from ' . tablename('ching_leeing_member_log') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if (empty($log)) {
            show_json(0, '未找到记录!');
        }
        m('balance')->WithdrawDone($log['money'],$log['openid']);
        pdo_update('ching_leeing_member_log', array('status' => 1), array('id' => $id, 'uniacid' => $_W['uniacid']));
        m('notice')->sendwithdrawokMessage($log['openid'],'我们已将酬金通过其他方式转账到您的账户',$log['money'],$_W['uniacid']);
        show_json(1);
    }
    public function wechat()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $log = pdo_fetch('select * from ' . tablename('ching_leeing_member_log') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if (empty($log)) {
            show_json(0, '未找到记录!');
        }
        $realmoeny = $log['money'];
        $set = $_W['shopset']['shop'];
        $result = m('finance')->pay($log['openid'], 1, $realmoeny * 100, $log['logno'], $set['name'] . '余额提现');
        if (is_error($result)) {
            show_json(0, array('message' => $result['message']));
        }
        pdo_update('ching_leeing_member_log', array('status' => 1), array('id' => $id, 'uniacid' => $_W['uniacid']));
        m('notice')->sendwithdrawokMessage($log['openid'],'我们已将酬金转账到您的微信账户',$log['money'],$_W['uniacid']);
        show_json(1);
    }
    public function refuse()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $log = pdo_fetch('select * from ' . tablename('ching_leeing_member_log') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        if (empty($log)) {
            show_json(0, '未找到记录!');
        }
        pdo_update('ching_leeing_member_log', array('status' => -1), array('id' => $id, 'uniacid' => $_W['uniacid']));
        show_json(1);
    }
}

?>