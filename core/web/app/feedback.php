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

class Feedback_ChingLeeingPage extends WebPage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' and uniacid=:uniacid';
        $params = array(':uniacid' => $_W['uniacid']);

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and content like :keyword';
            $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
        }


        if (empty($starttime) || empty($endtime)) {
            $starttime = strtotime('-1 month');
            $endtime = time();
        }


        if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
            $starttime = strtotime($_GPC['time']['start']);
            $endtime = strtotime($_GPC['time']['end']);
            $condition .= ' AND createtime >= :starttime AND createtime <= :endtime ';
            $params[':starttime'] = $starttime;
            $params[':endtime'] = $endtime;
        }



        $list = pdo_fetchall('SELECT  * FROM ' . tablename('ching_leeing_feedback') . ' WHERE 1 ' . $condition . ' ORDER BY createtime desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('ching_leeing_feedback') . ' WHERE 1 ' . $condition . ' ', $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template();
    }

    public function delete()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }


        $items = pdo_fetchall('SELECT id FROM ' . tablename('ching_leeing_feedback') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_delete('ching_leeing_feedback', array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
        }

        show_json(1, array('url' => referer()));
    }

    public function add()
    {
        $this->virtual();
    }

    protected function virtual()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $item = pdo_fetch('SELECT * FROM ' . tablename('ching_leeing_feedback') . ' WHERE id =:id and uniacid=:uniacid limit 1 ', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        $goodsid = intval($_GPC['goodsid']);

        if ($_W['ispost']) {
            if (empty($goodsid)) {
                show_json(0, array('message' => '请选择要评价的商品'));
            }


            $goods = set_medias(pdo_fetch('select id,thumb,title from ' . tablename('ching_leeing_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $goodsid, ':uniacid' => $_W['uniacid'])), 'thumb');

            if (empty($goods)) {
                show_json(0, array('message' => '请选择要评价的商品'));
            }


            $createtime = strtotime($_GPC['createtime']);
            if (empty($createtime) || (time() < $createtime)) {
                $createtime = time();
            }


            $data = array('uniacid' => $_W['uniacid'], 'level' => intval($_GPC['level']), 'goodsid' => intval($_GPC['goodsid']), 'nickname' => trim($_GPC['nickname']), 'headimgurl' => trim($_GPC['headimgurl']), 'content' => $_GPC['content'], 'images' => (is_array($_GPC['images']) ? iserializer($_GPC['images']) : iserializer(array())), 'reply_content' => $_GPC['reply_content'], 'reply_images' => (is_array($_GPC['reply_images']) ? iserializer($_GPC['reply_images']) : iserializer(array())), 'append_content' => $_GPC['append_content'], 'append_images' => (is_array($_GPC['append_images']) ? iserializer($_GPC['append_images']) : iserializer(array())), 'append_reply_content' => $_GPC['append_reply_content'], 'append_reply_images' => (is_array($_GPC['append_reply_images']) ? iserializer($_GPC['append_reply_images']) : iserializer(array())), 'createtime' => $createtime);

            if (empty($data['nickname'])) {
                $data['nickname'] = pdo_fetchcolumn('select nickname from ' . tablename('mc_members') . ' where nickname<>\'\' order by rand() limit 1');
            }


            if (empty($data['headimgurl'])) {
                $data['headimgurl'] = pdo_fetchcolumn('select avatar from ' . tablename('mc_members') . ' where avatar<>\'\' order by rand() limit 1');
            }


            if (!empty($id)) {
                pdo_update('ching_leeing_feedback', $data, array('id' => $id));
            } else {
                pdo_insert('ching_leeing_feedback', $data);
                $id = pdo_insertid();
            }

            show_json(1, array('url' => webUrl('shop/comment')));
        }


        if (empty($goodsid)) {
            $goodsid = intval($item['goodsid']);
        }


        $goods = pdo_fetch('select id,thumb,title from ' . tablename('ching_leeing_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $goodsid, ':uniacid' => $_W['uniacid']));
        include $this->template('shop/comment/virtual');
    }

    public function edit()
    {
        $this->virtual();
    }

    public function post()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $type = intval($_GPC['type']);
        $item = pdo_fetch('SELECT * FROM ' . tablename('ching_leeing_feedback') . ' WHERE id =:id and uniacid=:uniacid limit 1 ', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        $goods = pdo_fetch('select id,thumb,title from ' . tablename('ching_leeing_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $item['goodsid'], ':uniacid' => $_W['uniacid']));
        $order = pdo_fetch('select id,ordersn from ' . tablename('ching_leeing_order') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $item['orderid'], ':uniacid' => $_W['uniacid']));

        if ($_W['ispost']) {
            if ($type == 0) {
                $data = array('uniacid' => $_W['uniacid'], 'reply_content' => $_GPC['reply_content'], 'reply_images' => (is_array($_GPC['reply_images']) ? iserializer(m('common')->array_images($_GPC['reply_images'])) : iserializer(array())), 'append_reply_content' => $_GPC['append_reply_content'], 'append_reply_images' => (is_array($_GPC['append_reply_images']) ? iserializer($_GPC['append_reply_images']) : iserializer(array())));
                pdo_update('ching_leeing_feedback', $data, array('id' => $id));
            } else {
                $checked = intval($_GPC['checked']);
                $change_data = array();
                $change_data['checked'] = $checked;

                if (!empty($item['append_content'])) {
                    $replychecked = intval($_GPC['replychecked']);
                    $change_data['replychecked'] = $replychecked;
                }


                $checked_array = array('审核通过', '审核中', '审核不通过');
                pdo_update('ching_leeing_feedback', $change_data, array('id' => $id));
                $log_msg = '商品首次评价' . $checked_array[$checked];

                if (!empty($item['append_content'])) {
                    $log_msg .= ' 追加评价' . $checked_array[$checked];
                }


                $log_msg .= ' ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title'];
            }

            show_json(1, array('url' => webUrl('shop/comment')));
        }


        include $this->template();
    }
}


?>