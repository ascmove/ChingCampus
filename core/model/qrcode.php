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

class Qrcode_ChingLeeingModel
{
    public function createShopQrcode($mid = 0, $posterid = 0)
    {
        global $_W;
        global $_GPC;
        $path = IA_ROOT . '/addons/ching_leeing/data/qrcode/' . $_W['uniacid'] . '/';
        if (!is_dir($path)) {
            load()->func('file');
            mkdirs($path);
        }
        $url = mobileUrl('', array('mid' => $mid), true);
        if (!empty($posterid)) {
            $url .= '&posterid=' . $posterid;
        }
        $file = 'shop_qrcode_' . $posterid . '_' . $mid . '.png';
        $qrcode_file = $path . $file;
        if (!is_file($qrcode_file)) {
            require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
            QRcode::png($url, $qrcode_file, QR_ECLEVEL_L, 4);
        }
        return $_W['siteroot'] . 'addons/ching_leeing/data/qrcode/' . $_W['uniacid'] . '/' . $file;
    }

    public function createGoodsQrcode($mid = 0, $goodsid = 0, $posterid = 0)
    {
        global $_W;
        global $_GPC;
        $path = IA_ROOT . '/addons/ching_leeing/data/qrcode/' . $_W['uniacid'];
        if (!is_dir($path)) {
            load()->func('file');
            mkdirs($path);
        }
        $url = mobileUrl('goods/detail', array('id' => $goodsid, 'mid' => $mid), true);
        if (!empty($posterid)) {
            $url .= '&posterid=' . $posterid;
        }
        $file = 'goods_qrcode_' . $posterid . '_' . $mid . '_' . $goodsid . '.png';
        $qrcode_file = $path . '/' . $file;
        if (!is_file($qrcode_file)) {
            require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
            QRcode::png($url, $qrcode_file, QR_ECLEVEL_L, 4);
        }
        return $_W['siteroot'] . 'addons/ching_leeing/data/qrcode/' . $_W['uniacid'] . '/' . $file;
    }

    public function createQrcode($url)
    {
        global $_W;
        global $_GPC;
        $path = IA_ROOT . '/addons/ching_leeing/data/qrcode/' . $_W['uniacid'] . '/';
        if (!is_dir($path)) {
            load()->func('file');
            mkdirs($path);
        }
        $file = md5(base64_encode($url)) . '.jpg';
        $qrcode_file = $path . $file;
        if (!is_file($qrcode_file)) {
            require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
            QRcode::png($url, $qrcode_file, QR_ECLEVEL_L, 4);
        }
        return $_W['siteroot'] . 'addons/ching_leeing/data/qrcode/' . $_W['uniacid'] . '/' . $file;
    }
}

?>