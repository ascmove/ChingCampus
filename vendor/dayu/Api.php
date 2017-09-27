<?php
/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

include 'TopSdk.php';
date_default_timezone_set('Asia/Shanghai');
$appkey = '23401710';
$secret = 'ba1c3170481cbdd84bac8b5b0af879dd';
$c = new TopClient();
$c->appkey = $appkey;
$c->secretKey = $secret;
$req = new AlibabaAliqinFcSmsNumSendRequest();
$req->setSmsType('normal');
$req->setSmsFreeSignName('活动验证');
$req->setSmsParam('{"code":"1234","product":"rrshop"}');
$req->setRecNum('13553073705');
$req->setSmsTemplateCode('SMS_6756301');
$resp = $c->execute($req);
print_r($resp);

?>