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

class Receiver extends WeModuleReceiver
{
    public function receive()
    {
        global $_W;
        $type = $this->message['type'];
        $event = $this->message['event'];
        if (($event == 'subscribe') && ($type == 'subscribe')) {
            $this->saleVirtual();
        }
    }

    public function saleVirtual($obj = NULL)
    {
        global $_W;
        if (empty($obj)) {
            $obj = $this;
        }
        $sale = m('common')->getSysset('sale');
        $data = $sale['virtual'];
        if (empty($data['status'])) {
            return false;
        }
        $totalagent = pdo_fetchcolumn('select count(*) from' . tablename('ching_leeing_member') . ' where uniacid =' . $_W['uniacid'] . ' and isagent =1');
        $totalmember = pdo_fetchcolumn('select count(*) from' . tablename('ching_leeing_member') . ' where uniacid =' . $_W['uniacid']);
        $member = abs((int)$data['virtual_people']) + (int)$totalmember;
        $commission = abs((int)$data['virtual_commission']) + (int)$totalagent;
        $user = m('member')->checkMemberFromPlatform($obj->message['from']);
        if ($user['isnew']) {
            $message = str_replace('[会员数]', $member, $data['virtual_text']);
            $message = str_replace('[排名]', $member + 1, $message);
        } else {
            $message = str_replace('[会员数]', $member, $data['virtual_text2']);
        }
        $message = str_replace('[分销商数]', $commission, $message);
        $message = str_replace('[昵称]', $user['nickname'], $message);
        $message = htmlspecialchars_decode($message, ENT_QUOTES);
        $message = str_replace('"', '\\"', $message);
        return $this->sendText(WeAccount::create($_W['acid']), $obj->message['from'], $message);
    }

    public function sendText($acc, $openid, $content)
    {
        $send['touser'] = trim($openid);
        $send['msgtype'] = 'text';
        $send['text'] = array('content' => urlencode($content));
        $data = $acc->sendCustomNotice($send);
        return $data;
    }
}

?>