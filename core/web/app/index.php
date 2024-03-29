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

class Index_ChingLeeingPage extends WebPage
{
    public function main()
    {
        global $_W;
        $shop_data = m('common')->getSysset('platform');
        $merch_plugin = p('merch');
        $merch_data = m('common')->getPluginset('merch');
        if ($merch_plugin && $merch_data['is_openmerch']) {
            $is_openmerch = 1;
        } else {
            $is_openmerch = 0;
        }

        $order_sql = 'select id,ordersn,createtime,address,price from ' . tablename('ching_leeing_order') . ' where uniacid = :uniacid and deleted=0 AND ( status = 1 or (status=0 and paytype=3) ) ORDER BY createtime ASC LIMIT 20';
        $order = pdo_fetchall($order_sql, array(':uniacid' => $_W['uniacid']));

        foreach ($order as &$value) {
            $value['address'] = iunserializer($value['address']);
        }

        unset($value);
        $order_ok = $order;
        $notice = pdo_fetchall('SELECT * FROM ' . tablename('ching_leeing_system_copyright_notice') . ' ORDER BY displayorder ASC,createtime DESC LIMIT 10');
        $hascommission = false;

        if (p('commission')) {
            $hascommission = 0 < intval($_W['shopset']['commission']['level']);
        }


        include $this->template();
    }

    public function view()
    {
        global $_GPC;
        $id = intval($_GPC['id']);
        $item = pdo_fetch('SELECT * FROM ' . tablename('ching_leeing_system_copyright_notice') . ' WHERE id = ' . $id . ' ORDER BY displayorder ASC,createtime DESC');
        $item['content'] = htmlspecialchars_decode($item['content']);
        include $this->template('shop/view');
    }

    public function ajax()
    {
        global $_W;
        $paras = array(':uniacid' => $_W['uniacid']);
        $goods_totals = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('ching_leeing_goods') . ' WHERE uniacid = :uniacid and status=1 and deleted=0 and total<=0 and total<>-1  ', $paras);
        $finance_total = pdo_fetchcolumn('select count(1) from ' . tablename('ching_leeing_member_log') . ' log ' . ' left join ' . tablename('ching_leeing_member') . ' m on m.openid=log.openid and m.uniacid= log.uniacid' . ' left join ' . tablename('ching_leeing_member_group') . ' g on m.groupid=g.id' . ' left join ' . tablename('ching_leeing_member_level') . ' l on m.level =l.id' . ' where log.uniacid=:uniacid and log.type=:type and log.money<>0 and log.status=:status', array(':uniacid' => $_W['uniacid'], ':type' => 1, ':status' => 0));
        $commission_agent_total = pdo_fetchcolumn('select count(1) from' . tablename('ching_leeing_member') . ' dm ' . ' left join ' . tablename('ching_leeing_member') . ' p on p.id = dm.agentid ' . ' left join ' . tablename('mc_mapping_fans') . 'f on f.openid=dm.openid' . ' where dm.uniacid =:uniacid and dm.isagent =1', array(':uniacid' => $_W['uniacid']));
        $commission_agent_status0_total = pdo_fetchcolumn('select count(1) from' . tablename('ching_leeing_member') . ' dm ' . ' left join ' . tablename('ching_leeing_member') . ' p on p.id = dm.agentid ' . ' left join ' . tablename('mc_mapping_fans') . 'f on f.openid=dm.openid' . ' where dm.uniacid =:uniacid and dm.isagent =1 and dm.status=:status', array(':uniacid' => $_W['uniacid'], ':status' => 0));
        $commission_apply_status1_total = pdo_fetchcolumn('select count(1) from' . tablename('ching_leeing_commission_apply') . ' a ' . ' left join ' . tablename('ching_leeing_member') . ' m on m.uid = a.mid' . ' left join ' . tablename('ching_leeing_commission_level') . ' l on l.id = m.agentlevel' . ' where a.uniacid=:uniacid and a.status=:status', array(':uniacid' => $_W['uniacid'], ':status' => 1));
        $commission_apply_status2_total = pdo_fetchcolumn('select count(1) from' . tablename('ching_leeing_commission_apply') . ' a ' . ' left join ' . tablename('ching_leeing_member') . ' m on m.uid = a.mid' . ' left join ' . tablename('ching_leeing_commission_level') . ' l on l.id = m.agentlevel' . ' where a.uniacid=:uniacid and a.status=:status', array(':uniacid' => $_W['uniacid'], ':status' => 2));
        show_json(1, array('goods_totals' => $goods_totals, 'finance_total' => $finance_total, 'commission_agent_total' => $commission_agent_total, 'commission_agent_status0_total' => $commission_agent_status0_total, 'commission_apply_status1_total' => $commission_apply_status1_total, 'commission_apply_status2_total' => $commission_apply_status2_total));
    }
}


?>