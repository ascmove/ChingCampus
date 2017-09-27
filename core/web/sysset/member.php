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

class Member_ChingLeeingPage extends WebPage
{

    public function main()
    {
        $this->cards();
    }
    public function cards()
    {
        global $_W;
        global $_GPC;
        $data = m('common')->getSysset('member');
        if ($_W['ispost']) {
            $data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
            $data['bg'] = save_media($data['bg']);
            m('common')->updateSysset(array('member' => $data));
            show_json(1);
        }
        include $this->template('sysset/member/cards');
    }
}

?>