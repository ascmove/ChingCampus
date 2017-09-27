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

class Adv_ChingLeeingPage extends WebPage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' and uniacid=:uniacid';
        $params = array(':uniacid' => $_W['uniacid']);

        if ($_GPC['enabled'] != '') {
            $condition .= ' and enabled=' . intval($_GPC['enabled']);
        }


        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and advname  like :keyword';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }


        $list = pdo_fetchall('SELECT * FROM ' . tablename('ching_leeing_adv') . ' WHERE 1 ' . $condition . '  ORDER BY displayorder DESC limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('ching_leeing_adv') . ' WHERE 1 ' . $condition, $params);
        $pager = pagination($total, $pindex, $psize);
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
        $id = intval($_GPC['id']);

        if ($_W['ispost']) {
            $data = array('uniacid' => $_W['uniacid'], 'advname' => trim($_GPC['advname']), 'link' => trim($_GPC['link']), 'enabled' => intval($_GPC['enabled']), 'displayorder' => intval($_GPC['displayorder']), 'thumb' => save_media($_GPC['thumb']));

            if (!empty($id)) {
                pdo_update('ching_leeing_adv', $data, array('id' => $id));
                plog('shop.adv.edit', '修改广告 ID: ' . $id);
            } else {
                pdo_insert('ching_leeing_adv', $data);
                $id = pdo_insertid();
                plog('shop.adv.add', '添加广告 ID: ' . $id);
            }
            show_json(1, array('url' => webUrl('app/adv')));
        }


        $item = pdo_fetch('select * from ' . tablename('ching_leeing_adv') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
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


        $items = pdo_fetchall('SELECT id,advname FROM ' . tablename('ching_leeing_adv') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_delete('ching_leeing_adv', array('id' => $item['id']));
            plog('shop.adv.delete', '删除幻灯片 ID: ' . $item['id'] . ' 标题: ' . $item['advname'] . ' ');
        }

        show_json(1, array('url' => referer()));
    }

    public function displayorder()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $displayorder = intval($_GPC['value']);
        $item = pdo_fetchall('SELECT id,advname FROM ' . tablename('ching_leeing_adv') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

        if (!empty($item)) {
            pdo_update('ching_leeing_adv', array('displayorder' => $displayorder), array('id' => $id));
            plog('shop.adv.edit', '修改幻灯片排序 ID: ' . $item['id'] . ' 标题: ' . $item['advname'] . ' 排序: ' . $displayorder . ' ');
        }


        show_json(1);
    }

    public function enabled()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }


        $items = pdo_fetchall('SELECT id,advname FROM ' . tablename('ching_leeing_adv') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_update('ching_leeing_adv', array('enabled' => intval($_GPC['enabled'])), array('id' => $item['id']));
            plog('shop.adv.edit', (('修改幻灯片状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['advname'] . '<br/>状态: ' . $_GPC['enabled']) == 1 ? '显示' : '隐藏'));
        }

        show_json(1, array('url' => referer()));
    }
}


?>