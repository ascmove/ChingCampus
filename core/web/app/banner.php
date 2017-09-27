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

class Banner_ChingLeeingPage extends WebPage
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
            $condition .= ' and bannername  like :keyword';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }


        $list = pdo_fetchall('SELECT * FROM ' . tablename('ching_leeing_banner') . ' WHERE 1 ' . $condition . '  ORDER BY displayorder DESC limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('ching_leeing_banner') . ' WHERE 1 ' . $condition, $params);
        $pager = pagination($total, $pindex, $psize);
        $bannerswipe = $_W['shopset']['shop']['bannerswipe'];
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
            if(!empty($_GPC['publish'])){
                $pos = 'publish,';
            }if(!empty($_GPC['rec'])){
                $pos = $pos.'rec,';
            }
            if(!empty($_GPC['member'])){
                $pos = $pos.'member,';
            }
            if(!empty($_GPC['memcen'])){
                $pos = $pos.'memcen,';
            }
            if(!empty($_GPC['pubordermanage'])){
                $pos = $pos.'pubordermanage,';
            }
            if(!empty($_GPC['servordermanage'])){
                $pos = $pos.'servordermanage,';
            }
            if(!empty($_GPC['orderpudet'])){
                $pos = $pos.'orderpudet,';
            }
            if(!empty($_GPC['orderservdet'])){
                $pos = $pos.'orderservdet,';
            }
            if(!empty($_GPC['ordercomdet'])){
                $pos = $pos.'ordercomdet,';
            }
            if(!empty($_GPC['apply'])){
                $pos = $pos.'apply,';
            }
            
            $data = array('uniacid' => $_W['uniacid'], 'bannername' => trim($_GPC['bannername']), 'link' => trim($_GPC['link']),'pos'=>$pos, 'enabled' => intval($_GPC['enabled']), 'displayorder' => intval($_GPC['displayorder']), 'thumb' => save_media($_GPC['thumb']));

            if (!empty($id)) {
                pdo_update('ching_leeing_banner', $data, array('id' => $id));
            } else {
                pdo_insert('ching_leeing_banner', $data);
                $id = pdo_insertid();
            }

            show_json(1, array('url' => webUrl('app/banner')));
        }


        $item = pdo_fetch('select * from ' . tablename('ching_leeing_banner') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
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


        $items = pdo_fetchall('SELECT id,bannername FROM ' . tablename('ching_leeing_banner') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_delete('ching_leeing_banner', array('id' => $item['id']));
        }

        show_json(1, array('url' => referer()));
    }

    public function displayorder()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $displayorder = intval($_GPC['value']);
        $item = pdo_fetchall('SELECT id,bannername FROM ' . tablename('ching_leeing_banner') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

        if (!empty($item)) {
            pdo_update('ching_leeing_banner', array('displayorder' => $displayorder), array('id' => $id));
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


        $items = pdo_fetchall('SELECT id,bannername FROM ' . tablename('ching_leeing_banner') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_update('ching_leeing_banner', array('enabled' => intval($_GPC['enabled'])), array('id' => $item['id']));
        }

        show_json(1, array('url' => referer()));
    }

    public function setswipe()
    {
        global $_W;
        global $_GPC;
        $shop = $_W['shopset']['shop'];
        $shop['bannerswipe'] = intval($_GPC['bannerswipe']);
        m('common')->updateSysset(array('shop' => $shop));
        show_json(1);
    }
}


?>