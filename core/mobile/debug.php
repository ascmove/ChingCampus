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

class Debug_ChingLeeingPage extends MobilePage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $memberinfo = m('member')->getInfo($_W['openid']);
        $cardcfg = m('common')->getSysset('member');
        $levelinfo = m('member')->getLevel($_W['openid']);
        $memberinfo['level'] = $levelinfo['levelname'];
//        include $this->template('member/card');
        include $this->template('im');
    }
//    public function main()
//    {
//        global $_W;
//        global $_GPC;
//        include $this->template('member/card');
//        m('member')->upgradeLevel($_W['openid']);
////        header('Location:weixin://wxpay/bizpayurl?pr=UzyTJRW');exit;
////        echo "<html><body><a href='weixin://wxpay/bizpayurl?pr=UzyTJRW'>err</a></body></html>";exit;
////        print_r($_W['openid']);
//    }
}
?>