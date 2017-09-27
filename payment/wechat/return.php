<?php
/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

require '../../../../framework/bootstrap.inc.php';
require '../../../../addons/ching_leeing/defines.php';
require '../../../../addons/ching_leeing/core/inc/functions.php';
$ordersn = $_GET['outtradeno'];
$attachs = explode(':', $_GET['attach']);
if (empty($attachs) || !(is_array($attachs))) {
    exit();
}
$uniacid = $attachs[0];
$paytype = $attachs[1];
$url = $_W['siteroot'] . '../../app/index.php?i=' . $uniacid . '&c=entry&m=ching_leeing&do=mobile';
if (!(empty($ordersn))) {
    if ($paytype == 0) {
        $url = $_W['siteroot'] . '../../app/index.php?i=' . $uniacid . '&c=entry&m=ching_leeing&do=mobile&r=order.pay.complete&ordersn=' . $ordersn . '&type=wechat';
    } else if ($paytype == 1) {
        $url = $_W['siteroot'] . '../../app/index.php?i=' . $uniacid . '&c=entry&m=ching_leeing&do=mobile&r=member.recharge.wechat_complete&logno=' . $ordersn;
    } else if ($paytype == 2) {
    } else if ($paytype == 3) {
        $url = $_W['siteroot'] . '../../app/index.php?i=' . $uniacid . '&c=entry&m=ching_leeing&do=mobile&r=creditshop.log&logno=' . $ordersn;
    } else if ($paytype == 4) {
        $url = $_W['siteroot'] . '../../app/index.php?i=' . $uniacid . '&c=entry&m=ching_leeing&do=mobile&r=sale.coupon.my';
    } else if ($paytype == 5) {
        $url = $_W['siteroot'] . '../../app/index.php?i=' . $uniacid . '&c=entry&m=ching_leeing&do=mobile&r=groups.pay.complete&ordersn=' . $ordersn . '&type=wechat';
    }
}
header('location: ' . $url);
exit();
?>