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

class Cover_ChingLeeingPage extends WebPage
{
    public function release()
    {
        global $_W;
        global $_GPC;
        $cover = $this->_cover('shop', '下单', mobileUrl('', array(), false));
        include $this->template('sysset/cover');
    }

    protected function _cover($key, $name, $url)
    {
        global $_W;
        global $_GPC;
        $rule = pdo_fetch('select * from ' . tablename('rule') . ' where uniacid=:uniacid and module=:module and name=:name limit 1', array(':uniacid' => $_W['uniacid'], ':module' => 'cover', ':name' => 'ching_leeing' . $name . '入口设置'));
        $keyword = false;
        $cover = false;

        if (!empty($rule)) {
            $keyword = pdo_fetch('select * from ' . tablename('rule_keyword') . ' where uniacid=:uniacid and rid=:rid limit 1', array(':uniacid' => $_W['uniacid'], ':rid' => $rule['id']));
            $cover = pdo_fetch('select * from ' . tablename('cover_reply') . ' where uniacid=:uniacid and rid=:rid limit 1', array(':uniacid' => $_W['uniacid'], ':rid' => $rule['id']));
        }


        if ($_W['ispost']) {
            ca('sysset.cover.' . $key . '.edit');
            $data = ((is_array($_GPC['cover']) ? $_GPC['cover'] : array()));

            if (empty($data['keyword'])) {
                show_json(0, '请输入关键词!');
            }


            $keyword1 = m('common')->keyExist($data['keyword']);

            if (!empty($keyword1)) {
                if ($keyword1['name'] != 'ching_leeing' . $name . '入口设置') {
                    show_json(0, '关键字已存在!');
                }

            }


            if (!empty($rule)) {
                pdo_delete('rule', array('id' => $rule['id'], 'uniacid' => $_W['uniacid']));
                pdo_delete('rule_keyword', array('rid' => $rule['id'], 'uniacid' => $_W['uniacid']));
                pdo_delete('cover_reply', array('rid' => $rule['id'], 'uniacid' => $_W['uniacid']));
            }


            $rule_data = array('uniacid' => $_W['uniacid'], 'name' => 'ching_leeing' . $name . '入口设置', 'module' => 'cover', 'displayorder' => 0, 'status' => intval($data['status']));
            pdo_insert('rule', $rule_data);
            $rid = pdo_insertid();
            $keyword_data = array('uniacid' => $_W['uniacid'], 'rid' => $rid, 'module' => 'cover', 'content' => trim($data['keyword']), 'type' => 1, 'displayorder' => 0, 'status' => intval($data['status']));
            pdo_insert('rule_keyword', $keyword_data);
            $cover_data = array('uniacid' => $_W['uniacid'], 'rid' => $rid, 'module' => 'ching_leeing', 'title' => trim($data['title']), 'description' => trim($data['desc']), 'thumb' => save_media($data['thumb']), 'url' => $url);
            pdo_insert('cover_reply', $cover_data);
            show_json(1);
        }


        return array('rule' => $rule, 'cover' => $cover, 'keyword' => $keyword, 'url' => $_W['siteroot'] . 'app/' . substr($url, 2), 'name' => $name, 'key' => $key);
    }

    public function orderhall()
    {
        global $_W;
        global $_GPC;
        $cover = $this->_cover('market', '抢单大厅', mobileUrl('order/hall', array(), false));
        include $this->template('sysset/cover');
    }

    public function notice()
    {
        global $_W;
        global $_GPC;
        $cover = $this->_cover('tip', '通知设置', mobileUrl('member/notice', array(), false));
        include $this->template('sysset/cover');
    }


    public function home()
    {
        global $_W;
        global $_GPC;
        $cover = $this->_cover('member', '我的主页', mobileUrl('member', array(), false));
        include $this->template('sysset/cover');
    }

    public function myrelease()
    {
        global $_W;
        global $_GPC;
        $cover = $this->_cover('myrelease', '我下的单', mobileUrl('order', array(), false));
        include $this->template('sysset/cover');
    }

    public function myservice()
    {
        global $_W;
        global $_GPC;
        $cover = $this->_cover('myservice', '我接的单', mobileUrl('order/servant', array(), false));
        include $this->template('sysset/cover');
    }
}


?>