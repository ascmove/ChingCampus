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

class Ad_ChingLeeingPage extends MobilePage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $link = pdo_fetch('select link,clicks from '.tablename('ching_leeing_banner').' where id=:id',array(':id'=>intval($_GPC['aid'])));
        if(!empty($link)){
            pdo_query('update '.tablename('ching_leeing_banner').' set clicks=clicks+1 where id=:id',array(':id'=>intval($_GPC['aid'])));
            header('location:'.$link['link']);exit;
        }else{
            header('location:'.mobileUrl('index'));exit;
        }
    }
}

?>