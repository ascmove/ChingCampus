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

class Role_ChingLeeingPage extends WebPage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $status = $_GPC['status'];
        $condition = ' and uniacid = :uniacid and deleted=0';
        $params = array(':uniacid' => $_W['uniacid']);

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and rolename like :keyword';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }


        if ($_GPC['status'] != '') {
            $condition .= ' and status=' . intval($_GPC['status']);
        }


        $list = pdo_fetchall('SELECT *  FROM ' . tablename('ching_leeing_perm_role') . ' WHERE 1 ' . $condition . ' ORDER BY id desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize, $params);

        foreach ($list as &$row) {
            $row['usercount'] = pdo_fetchcolumn('select count(*) from ' . tablename('ching_leeing_perm_user') . ' where roleid=:roleid limit 1', array(':roleid' => $row['id']));
        }

        unset($row);
        $total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('ching_leeing_perm_role') . '  WHERE 1 ' . $condition . ' ', $params);
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
        $item = pdo_fetch('SELECT * FROM ' . tablename('ching_leeing_perm_role') . ' WHERE id =:id and deleted=0 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
        $perms = com('perm')->formatPerms();
        $role_perms = array();
        $user_perms = array();

        if (!empty($item)) {
            $role_perms = explode(',', $item['perms2']);
        }


        $user_perms = explode(',', $item['perms2']);

        if ($_W['ispost']) {
            $data = array('uniacid' => $_W['uniacid'], 'rolename' => trim($_GPC['rolename']), 'status' => intval($_GPC['status']), 'perms2' => (is_array($_GPC['perms']) ? implode(',', $_GPC['perms']) : ''));

            if (!empty($id)) {
                pdo_update('ching_leeing_perm_role', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
                plog('perm.role.edit', '修改角色 ID: ' . $id);
            } else {
                pdo_insert('ching_leeing_perm_role', $data);
                $id = pdo_insertid();
                plog('perm.role.add', '添加角色 ID: ' . $id . ' ');
            }

            show_json(1);
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


        $items = pdo_fetchall('SELECT id,rolename FROM ' . tablename('ching_leeing_perm_role') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_delete('ching_leeing_perm_role', array('id' => $item['id']));
            plog('perm.role.delete', '删除角色 ID: ' . $item['id'] . ' 角色名称: ' . $item['rolename'] . ' ');
        }

        show_json(1, array('url' => referer()));
    }

    public function status()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }


        $status = intval($_GPC['status']);
        $items = pdo_fetchall('SELECT id,rolename FROM ' . tablename('ching_leeing_perm_role') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_update('ching_leeing_perm_role', array('status' => $status), array('id' => $item['id']));
            plog('perm.role.edit', '修改角色状态 ID: ' . $item['id'] . ' 角色名称: ' . $item['rolename'] . ' 状态: ' . (($status == 0 ? '禁用' : '启用')));
        }

        show_json(1, array('url' => referer()));
    }

    public function query()
    {
        global $_GPC;
        global $_W;
        $kwd = trim($_GPC['keyword']);
        $params = array();
        $params[':uniacid'] = $_W['uniacid'];
        $condition = ' and uniacid=:uniacid and deleted=0';

        if (!empty($kwd)) {
            $condition .= ' AND `rolename` LIKE :keyword';
            $params[':keyword'] = '%' . $kwd . '%';
        }


        $ds = pdo_fetchall('SELECT id,rolename,perms2 FROM ' . tablename('ching_leeing_perm_role') . ' WHERE status=1 ' . $condition . ' order by id asc', $params);
        include $this->template();
        exit();
    }
}


?>