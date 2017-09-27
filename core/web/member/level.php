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

class Level_ChingLeeingPage extends WebPage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $set = m('common')->getSysset();
        $shopset = $set['shop'];
        if (isset($shopset['leveldiscount'])) {
            $leveldiscount = $shopset['leveldiscount'];
        } else {
            $leveldiscount = 0;
        }
        $default = array('id' => 'default', 'levelname' => (empty($set['shop']['levelname']) ? '普通等级' : $set['shop']['levelname']), 'discount' => $set['shop']['leveldiscount'], 'ordermoney' => 0, 'ordercount' => 0);
        $condition = ' and uniacid=:uniacid';
        $params = array(':uniacid' => $_W['uniacid']);
        if ($_GPC['enabled'] != '') {
            $condition .= ' and enabled=' . intval($_GPC['enabled']);
        }
        if (!(empty($_GPC['keyword']))) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and ( levelname like :levelname)';
            $params[':levelname'] = '%' . $_GPC['keyword'] . '%';
        }
        if (p('cmember')) {
            $condition .= ' and flag=1';
        }
        $others = pdo_fetchall('SELECT * FROM ' . tablename('ching_leeing_member_level') . ' WHERE 1 ' . $condition . ' ORDER BY level asc', $params);
        $list = array_merge(array($default), $others);
        include $this->template();
    }

    public function add()
    {
        $this->post();
    }

    protected function post()
    {
        global $_W;
        global $_GPC;
        global $_S;
        $id = trim($_GPC['id']);
        $set = $_S;
        $setdata = pdo_fetch('select * from ' . tablename('ching_leeing_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
        if ($id == 'default') {
            $level = array('id' => 'default', 'levelname' => (empty($set['shop']['levelname']) ? '普通等级' : $set['shop']['levelname']), 'discount' => $set['shop']['leveldiscount'], 'ordermoney' => 0, 'ordercount' => 0);
        } else {
            $level = pdo_fetch('SELECT * FROM ' . tablename('ching_leeing_member_level') . ' WHERE id=:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => intval($id)));
        }
        if ($_W['ispost']) {
            $enabled = intval($_GPC['enabled']);
            $data = array('uniacid' => $_W['uniacid'], 'level' => intval($_GPC['level']), 'levelname' => trim($_GPC['levelname']), 'ordercount' => intval($_GPC['ordercount']), 'ordermoney' => $_GPC['ordermoney'], 'discount' => trim($_GPC['discount']), 'enabled' => $enabled);
            if (!(empty($id))) {
                if ($id == 'default') {
                    $set['shop']['levelname'] = $data['levelname'];
                    $set['shop']['leveldiscount'] = $data['discount'];
                    $data = array('uniacid' => $_W['uniacid'], 'sets' => iserializer($set));
                    if (empty($setdata)) {
                        pdo_insert('ching_leeing_sysset', $data);
                    } else {
                        pdo_update('ching_leeing_sysset', $data, array('uniacid' => $_W['uniacid']));
                    }
                    $setdata = pdo_fetch('select * from ' . tablename('ching_leeing_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
                    m('cache')->set('sysset', $setdata);
                } else {
                    pdo_update('ching_leeing_member_level', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
                }
            } else {
                pdo_insert('ching_leeing_member_level', $data);
                $id = pdo_insertid();
            }
            show_json(1, array('url' => webUrl('member/level')));
        }
        $level_array = array();
        $i = 0;
        while ($i < 101) {
            $level_array[$i] = $i;
            ++$i;
        }
        include $this->template();
    }

    public function edit()
    {
        $this->post();
    }

    public function delete()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('SELECT id,levelname FROM ' . tablename('ching_leeing_member_level') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);
        foreach ($items as $item) {
            pdo_delete('ching_leeing_member_level', array('id' => $item['id']));
        }
        show_json(1, array('url' => referer()));
    }

    public function enabled()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }
        $items = pdo_fetchall('SELECT id,levelname FROM ' . tablename('ching_leeing_member_level') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);
        foreach ($items as $item) {
            pdo_update('ching_leeing_member_level', array('enabled' => intval($_GPC['enabled'])), array('id' => $item['id']));
        }
        show_json(1, array('url' => referer()));
    }
}

?>