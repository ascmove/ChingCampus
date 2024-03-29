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

class Coupon_ChingLeeingComModel extends ComModel
{
    public function creditshop($logid = 0)
    {
        global $_W;
        global $_GPC;
        $pcreditshop = p('creditshop');
        if (!($pcreditshop)) {
            return;
        }
        $log = pdo_fetch('SELECT * FROM ' . tablename('ching_leeing_creditshop_log') . ' WHERE `id`=:id and `uniacid`=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $logid));
        if (!(empty($log))) {
            $member = m('member')->getMember($log['openid']);
            $goods = $pcreditshop->getGoods($log['couponid'], $member);
            $couponlog = array('uniacid' => $_W['uniacid'], 'openid' => $log['openid'], 'logno' => m('common')->createNO('coupon_log', 'logno', 'CC'), 'couponid' => $log['couponid'], 'status' => 1, 'paystatus' => (0 < $goods['money'] ? 0 : -1), 'creditstatus' => (0 < $goods['credit'] ? 0 : -1), 'createtime' => time(), 'getfrom' => 2);
            pdo_insert('ching_leeing_coupon_log', $couponlog);
            $data = array('uniacid' => $_W['uniacid'], 'openid' => $log['openid'], 'couponid' => $log['couponid'], 'gettype' => 2, 'gettime' => time());
            pdo_insert('ching_leeing_coupon_data', $data);
            $coupon = pdo_fetch('select * from ' . tablename('ching_leeing_coupon') . ' where id=:id limit 1', array(':id' => $log['couponid']));
            $coupon = $this->setCoupon($coupon, time());
            $this->sendMessage($coupon, 1, $member);
            pdo_update('ching_leeing_creditshop_log', array('status' => 3), array('id' => $logid));
        }
    }

    public function setCoupon($row, $time, $withOpenid = true)
    {
        global $_W;
        if ($withOpenid) {
            $openid = $_W['openid'];
        }
        $row['free'] = false;
        $row['past'] = false;
        $row['thumb'] = tomedia($row['thumb']);
        $row['merchname'] = '';
        $row['total'] = $this->get_last_count($row['id']);
        if (0 < $row['merchid']) {
            $merch_plugin = p('merch');
            if ($merch_plugin) {
                $merch_user = $merch_plugin->getListUserOne($row['merchid']);
                if (!(empty($merch_user))) {
                    $row['merchname'] = $merch_user['merchname'];
                }
            }
        }
        if ((0 < $row['money']) && (0 < $row['credit'])) {
            $row['getstatus'] = 0;
            $row['gettypestr'] = '购买';
        } else if (0 < $row['money']) {
            $row['getstatus'] = 1;
            $row['gettypestr'] = '购买';
        } else if (0 < $row['credit']) {
            $row['getstatus'] = 2;
            $row['gettypestr'] = '兑换';
        } else {
            $row['getstatus'] = 3;
            $row['gettypestr'] = '领取';
        }
        $row['timestr'] = '0';
        if (empty($row['timelimit'])) {
            if (!(empty($row['timedays']))) {
                $row['timestr'] = 1;
            }
        } else if ($time <= $row['timestart']) {
            $row['timestr'] = date('Y-m-d', $row['timestart']) . '-' . date('Y-m-d', $row['timeend']);
        } else {
            $row['timestr'] = date('Y-m-d', $row['timeend']);
        }
        $row['css'] = 'deduct';
        if ($row['backtype'] == 0) {
            $row['backstr'] = '立减';
            $row['css'] = 'deduct';
            $row['backpre'] = true;
            $row['_backmoney'] = $row['deduct'];
        } else if ($row['backtype'] == 1) {
            $row['backstr'] = '折';
            $row['css'] = 'discount';
            $row['_backmoney'] = $row['discount'];
        } else if ($row['backtype'] == 2) {
            if (!(empty($row['backredpack']))) {
                $row['backstr'] = '返现';
                $row['css'] = 'redpack';
                $row['backpre'] = true;
                $row['_backmoney'] = $row['backredpack'];
            } else if (!(empty($row['backmoney']))) {
                $row['backstr'] = '返利';
                $row['css'] = 'money';
                $row['backpre'] = true;
                $row['_backmoney'] = $row['backmoney'];
            } else if (!(empty($row['backcredit']))) {
                $row['backstr'] = '返积分';
                $row['css'] = 'credit';
                $row['_backmoney'] = $row['backcredit'];
            }
        }
        if ($withOpenid) {
            $row['cangetmax'] = -1;
            $row['canget'] = true;
            if (($row['total'] != -1) && ($row['total'] <= 0)) {
                $row['canget'] = false;
                $row['cangetmax'] = -2;
                return $row;
            }
            if (0 < $row['getmax']) {
                $gets = pdo_fetchcolumn('select count(*) from ' . tablename('ching_leeing_coupon_data') . ' where couponid=:couponid and openid=:openid and uniacid=:uniacid and gettype=1 limit 1', array(':couponid' => $row['id'], ':openid' => $openid, ':uniacid' => $_W['uniacid']));
                $row['cangetmax'] = $row['getmax'] - $gets;
                if ($row['cangetmax'] <= 0) {
                    $row['cangetmax'] = 0;
                    $row['canget'] = false;
                }
            }
        }
        return $row;
    }

    public function get_last_count($couponid = 0)
    {
        global $_W;
        $coupon = pdo_fetch('SELECT id,total FROM ' . tablename('ching_leeing_coupon') . ' WHERE id=:id and uniacid=:uniacid ', array(':id' => $couponid, ':uniacid' => $_W['uniacid']));
        if (empty($coupon)) {
            return 0;
        }
        if ($coupon['total'] == -1) {
            return -1;
        }
        $gettotal = pdo_fetchcolumn('select count(*) from ' . tablename('ching_leeing_coupon_data') . ' where couponid=:couponid and uniacid=:uniacid ', array(':couponid' => $couponid, ':uniacid' => $_W['uniacid']));
        return $coupon['total'] - $gettotal;
    }

    public function sendMessage($coupon, $send_total, $member, $account = NULL)
    {
        global $_W;
        $articles = array();
        $title = str_replace('[nickname]', $member['nickname'], $coupon['resptitle']);
        $desc = str_replace('[nickname]', $member['nickname'], $coupon['respdesc']);
        $title = str_replace('[total]', $send_total, $title);
        $desc = str_replace('[total]', $send_total, $desc);
        $url = ((empty($coupon['respurl']) ? mobileUrl('sale/coupon/my', NULL, true) : $coupon['respurl']));
        if (!(empty($coupon['resptitle']))) {
            $articles[] = array('title' => urlencode($title), 'description' => urlencode($desc), 'url' => $url, 'picurl' => tomedia($coupon['respthumb']));
        }
        if (!(empty($articles))) {
            $resp = m('message')->sendNews($member['openid'], $articles, $account);
            if (is_error($resp)) {
                $msg = array('keyword1' => array('value' => $title, 'color' => '#73a68d'), 'keyword2' => array('value' => $desc, 'color' => '#73a68d'));
                $ret = m('message')->sendCustomNotice($member['openid'], $msg, $url, $account);
                if (is_error($ret)) {
                    m('message')->sendCustomNotice($member['openid'], $msg, $url, $account);
                }
            }
        }
    }

    public function taskposter($member, $couponid, $couponnum)
    {
        global $_W;
        global $_GPC;
        $pposter = p('poster');
        if (!($pposter)) {
            return;
        }
        $coupon = $this->getCoupon($couponid);
        if (empty($coupon)) {
            return;
        }
        $i = 1;
        while ($i <= $couponnum) {
            $couponlog = array('uniacid' => $_W['uniacid'], 'openid' => $member['openid'], 'logno' => m('common')->createNO('coupon_log', 'logno', 'CC'), 'couponid' => $couponid, 'status' => 1, 'paystatus' => -1, 'creditstatus' => -1, 'createtime' => time(), 'getfrom' => 3);
            pdo_insert('ching_leeing_coupon_log', $couponlog);
            $data = array('uniacid' => $_W['uniacid'], 'openid' => $member['openid'], 'couponid' => $couponid, 'gettype' => 3, 'gettime' => time(), 'nocount' => 1);
            pdo_insert('ching_leeing_coupon_data', $data);
            ++$i;
        }
    }

    public function getCoupon($couponid = 0)
    {
        global $_W;
        return pdo_fetch('select * from ' . tablename('ching_leeing_coupon') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $couponid, ':uniacid' => $_W['uniacid']));
    }

    public function getAvailableCoupons($type, $money = 0, $merch_array, $goods_array = array())
    {
        global $_W;
        global $_GPC;
        $time = time();
        $param = array();
        $param[':openid'] = $_W['openid'];
        $param[':uniacid'] = $_W['uniacid'];
        $sql = 'select d.id,d.couponid,d.gettime,c.timelimit,c.timedays,c.timestart,c.timeend,c.thumb,c.couponname,c.enough,c.backtype,c.deduct,c.discount,c.backmoney,c.backcredit,c.backredpack,c.bgcolor,c.thumb,c.merchid,c.limitgoodcatetype,c.limitgoodtype,c.limitgoodcateids,c.limitgoodids  from ' . tablename('ching_leeing_coupon_data') . ' d';
        $sql .= ' left join ' . tablename('ching_leeing_coupon') . ' c on d.couponid = c.id';
        $sql .= ' where d.openid=:openid and d.uniacid=:uniacid and c.merchid=0 and d.merchid=0 and c.coupontype=' . $type . ' and d.used=0 ';
        if ($type == 1) {
            $sql .= 'and ' . $money . '>=c.enough ';
        }
        $sql .= ' and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<=' . $time . ' && c.timeend>=' . $time . ')) order by d.gettime desc';
        $list = pdo_fetchall($sql, $param);
        if (!(empty($merch_array))) {
            foreach ($merch_array as $key => $value) {
                $merchid = $key;
                if (0 < $merchid) {
                    $param[':merchid'] = $merchid;
                    $sql = 'select d.id,d.couponid,d.gettime,c.timelimit,c.timedays,c.timestart,c.timeend,c.thumb,c.couponname,c.enough,c.backtype,c.deduct,c.discount,c.backmoney,c.backcredit,c.backredpack,c.bgcolor,c.thumb,c.merchid,c.limitgoodcatetype,c.limitgoodtype,c.limitgoodcateids,c.limitgoodids  from ' . tablename('ching_leeing_coupon_data') . ' d';
                    $sql .= ' left join ' . tablename('ching_leeing_coupon') . ' c on d.couponid = c.id';
                    $sql .= ' where d.openid=:openid and d.uniacid=:uniacid and c.merchid=:merchid and d.merchid=:merchid and c.coupontype=' . $type . '  and d.used=0 ';
                    $sql .= ' and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<=' . $time . ' && c.timeend>=' . $time . ')) order by d.gettime desc';
                    $merch_list = pdo_fetchall($sql, $param);
                    if (!(empty($merch_list))) {
                        $list = array_merge($list, $merch_list);
                    }
                }
            }
        }
        $goodlist = array();
        if (!(empty($goods_array))) {
            foreach ($goods_array as $key => $value) {
                $goodparam[':uniacid'] = $_W['uniacid'];
                $goodparam[':id'] = $value['goodsid'];
                $sql = 'select id,cates,marketprice,merchid   from ' . tablename('ching_leeing_goods');
                $sql .= ' where uniacid=:uniacid and id =:id order by id desc LIMIT 1 ';
                $good = pdo_fetch($sql, $goodparam);
                $good['saletotal'] = $value['total'];
                $good['optionid'] = $value['optionid'];
                if (!(empty($good))) {
                    $goodlist[] = $good;
                }
            }
        }
        if ($type == 0) {
            $list = $this->checkcouponlimit($list, $goodlist);
        }
        $list = set_medias($list, 'thumb');
        if (!(empty($list))) {
            foreach ($list as &$row) {
                $row['thumb'] = tomedia($row['thumb']);
                $row['timestr'] = '永久有效';
                if (empty($row['timelimit'])) {
                    if (!(empty($row['timedays']))) {
                        $row['timestr'] = date('Y-m-d H:i', $row['gettime'] + ($row['timedays'] * 86400));
                    }
                } else if ($time <= $row['timestart']) {
                    $row['timestr'] = date('Y-m-d H:i', $row['timestart']) . '-' . date('Y-m-d H:i', $row['timeend']);
                } else {
                    $row['timestr'] = date('Y-m-d H:i', $row['timeend']);
                }
                if ($row['backtype'] == 0) {
                    $row['backstr'] = '立减';
                    $row['css'] = 'deduct';
                    $row['backmoney'] = $row['deduct'];
                    $row['backpre'] = true;
                    if ($row['enough'] == '0') {
                        $row['color'] = 'org ';
                    } else {
                        $row['color'] = 'blue';
                    }
                } else if ($row['backtype'] == 1) {
                    $row['backstr'] = '折';
                    $row['css'] = 'discount';
                    $row['backmoney'] = $row['discount'];
                    $row['color'] = 'red ';
                } else if ($row['backtype'] == 2) {
                    if ($row['coupontype'] == '0') {
                        $row['color'] = 'red ';
                    } else {
                        $row['color'] = 'pink ';
                    }
                    if (0 < $row['backredpack']) {
                        $row['backstr'] = '返现';
                        $row['css'] = 'redpack';
                        $row['backmoney'] = $row['backredpack'];
                        $row['backpre'] = true;
                    } else if (0 < $row['backmoney']) {
                        $row['backstr'] = '返利';
                        $row['css'] = 'money';
                        $row['backmoney'] = $row['backmoney'];
                        $row['backpre'] = true;
                    } else if (!(empty($row['backcredit']))) {
                        $row['backstr'] = '返积分';
                        $row['css'] = 'credit';
                        $row['backmoney'] = $row['backcredit'];
                    }
                }
            }
            unset($row);
        }
        return $list;
    }

    public function checkcouponlimit($list, $goodlist)
    {
        global $_W;
        foreach ($list as $key => $row) {
            $pass = 0;
            $enough = 0;
            if (($row['limitgoodcatetype'] == 0) && ($row['limitgoodtype'] == 0) && ($row['enough'] == 0)) {
                $pass = 1;
            } else {
                foreach ($goodlist as $good) {
                    if ((0 < $row['merchid']) && (0 < $good['merchid']) && ($row['merchid'] != $good['merchid'])) {
                        continue;
                    }
                    $p = 0;
                    $cates = explode(',', $good['cates']);
                    $limitcateids = explode(',', $row['limitgoodcateids']);
                    $limitgoodids = explode(',', $row['limitgoodids']);
                    if (($row['limitgoodcatetype'] == 0) && ($row['limitgoodtype'] == 0)) {
                        $p = 1;
                    }
                    if ($row['limitgoodcatetype'] == 1) {
                        $result = array_intersect($cates, $limitcateids);
                        if (0 < count($result)) {
                            $p = 1;
                        }
                    }
                    if ($row['limitgoodtype'] == 1) {
                        $isin = in_array($good['id'], $limitgoodids);
                        if ($isin) {
                            $p = 1;
                        }
                    }
                    if ($p == 1) {
                        $pass = 1;
                    }
                    if ((0 < $row['enough']) && ($p == 1)) {
                        if (0 < $good['optionid']) {
                            $optionparam[':uniacid'] = $_W['uniacid'];
                            $optionparam[':id'] = $good['optionid'];
                            $sql = 'select  marketprice  from ' . tablename('ching_leeing_goods_option');
                            $sql .= ' where uniacid=:uniacid and id =:id order by id desc LIMIT 1 ';
                            $option = pdo_fetch($sql, $optionparam);
                            if (!(empty($option))) {
                                $enough += (double)$option['marketprice'] * $good['saletotal'];
                            }
                        } else {
                            $enough += (double)$good['marketprice'] * $good['saletotal'];
                        }
                    }
                }
                if ((0 < $row['enough']) && ($enough < $row['enough'])) {
                    $pass = 0;
                }
            }
            if ($pass == 0) {
                unset($list[$key]);
            }
        }
        return array_values($list);
    }

    public function payResult($logno)
    {
        global $_W;
        if (empty($logno)) {
            return error(-1);
        }
        $log = pdo_fetch('SELECT * FROM ' . tablename('ching_leeing_coupon_log') . ' WHERE `logno`=:logno and `uniacid`=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':logno' => $logno));
        if (empty($log)) {
            return error(-1, '服务器错误!');
        }
        if (1 <= $log['status']) {
            return true;
        }
        $coupon = pdo_fetch('select * from ' . tablename('ching_leeing_coupon') . ' where id=:id limit 1', array(':id' => $log['couponid']));
        $coupon = $this->setCoupon($coupon, time());
        if (empty($coupon['gettype'])) {
            return error(-1, '无法领取');
        }
        if ($coupon['total'] != -1) {
            if ($coupon['total'] <= 0) {
                return error(-1, '优惠券数量不足');
            }
        }
        if (!($coupon['canget'])) {
            return error(-1, '您已超出领取次数限制');
        }
        if (empty($log['status'])) {
            $update = array();
            if ((0 < $coupon['credit']) && empty($log['creditstatus'])) {
                m('member')->setCredit($log['openid'], 'credit1', -$coupon['credit'], '购买优惠券扣除积分 ' . $coupon['credit']);
                $update['creditstatus'] = 1;
            }
            if ((0 < $coupon['money']) && empty($log['paystatus'])) {
                if ($log['paytype'] == 0) {
                    m('member')->setCredit($log['openid'], 'credit2', -$coupon['money'], '购买优惠券扣除余额 ' . $coupon['money']);
                }
                $update['paystatus'] = 1;
            }
            $update['status'] = 1;
            pdo_update('ching_leeing_coupon_log', $update, array('id' => $log['id']));
            $data = array('uniacid' => $_W['uniacid'], 'merchid' => $log['merchid'], 'openid' => $log['openid'], 'couponid' => $log['couponid'], 'gettype' => $log['getfrom'], 'gettime' => time());
            pdo_insert('ching_leeing_coupon_data', $data);
            $dataid = pdo_insertid();
            $coupon['dataid'] = $dataid;
            $member = m('member')->getMember($log['openid']);
            $set = m('common')->getPluginset('coupon');
            $this->sendMessage($coupon, 1, $member);
        }
        $url = mobileUrl('member', NULL, true);
        if ($coupon['coupontype'] == 0) {
            $coupon['url'] = mobileUrl('goods', NULL, true);
        } else {
            $coupon['url'] = mobileUrl('member/recharge', NULL, true);
        }
        return $coupon;
    }

    public function useRechargeCoupon($log)
    {
        global $_W;
        if (empty($log['couponid'])) {
            return;
        }
        $data = pdo_fetch('select id,openid,couponid,used from ' . tablename('ching_leeing_coupon_data') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $log['couponid'], ':uniacid' => $_W['uniacid']));
        if (empty($data)) {
            return;
        }
        if (!(empty($data['used']))) {
            return;
        }
        $coupon = pdo_fetch('select enough,backcredit,backmoney,backredpack,couponname from ' . tablename('ching_leeing_coupon') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $data['couponid'], ':uniacid' => $_W['uniacid']));
        if (empty($coupon)) {
            return;
        }
        if ((0 < $coupon['enough']) && ($log['money'] < $coupon['enough'])) {
            return;
        }
        $gives = array();
        $backcredit = $coupon['backcredit'];
        if (!(empty($backcredit))) {
            if (strexists($backcredit, '%')) {
                $backcredit = intval((floatval(str_replace('%', '', $backcredit)) / 100) * $log['money']);
            } else {
                $backcredit = intval($backcredit);
            }
            if (0 < $backcredit) {
                $gives['credit'] = $backcredit;
                m('member')->setCredit($data['openid'], 'credit1', $backcredit, array(0, '充值优惠券返积分'));
            }
        }
        $backmoney = $coupon['backmoney'];
        if (!(empty($backmoney))) {
            if (strexists($backmoney, '%')) {
                $backmoney = round(floatval((floatval(str_replace('%', '', $backmoney)) / 100) * $log['money']), 2);
            } else {
                $backmoney = round(floatval($backmoney), 2);
            }
            if (0 < $backmoney) {
                $gives['money'] = $backmoney;
                m('member')->setCredit($data['openid'], 'credit2', $backmoney, array(0, '充值优惠券返利'));
            }
        }
        $backredpack = $coupon['backredpack'];
        if (!(empty($backredpack))) {
            if (strexists($backredpack, '%')) {
                $backredpack = round(floatval((floatval(str_replace('%', '', $backredpack)) / 100) * $log['money']), 2);
            } else {
                $backredpack = round(floatval($backredpack), 2);
            }
            if (0 < $backredpack) {
                $gives['redpack'] = $backredpack;
                $backredpack = intval($backredpack * 100);
                m('finance')->pay($data['openid'], 1, $backredpack, '', '充值优惠券-返现金', false);
            }
        }
        pdo_update('ching_leeing_coupon_data', array('used' => 1, 'usetime' => time(), 'ordersn' => $log['logno']), array('id' => $data['id']));
        $this->sendBackMessage($log['openid'], $coupon, $gives);
    }

    public function sendBackMessage($openid, $coupon, $gives)
    {
        global $_W;
        if (empty($gives)) {
            return;
        }
        $set = m('common')->getPluginset('coupon');
        $templateid = $set['templateid'];
        $content = '您的优惠券【' . $coupon['couponname'] . '】已返利 ';
        $givestr = '';
        if (isset($gives['credit'])) {
            $givestr .= ' ' . $gives['credit'] . '个积分';
        }
        if (isset($gives['money'])) {
            if (!(empty($givestr))) {
                $givestr .= '，';
            }
            $givestr .= $gives['money'] . '元余额';
        }
        if (isset($gives['redpack'])) {
            if (!(empty($givestr))) {
                $givestr .= '，';
            }
            $givestr .= $gives['redpack'] . '元现金';
        }
        $content .= $givestr;
        $content .= '，请查看您的账户，谢谢!';
        $msg = array('keyword1' => array('value' => '优惠券返利', 'color' => '#73a68d'), 'keyword2' => array('value' => $content, 'color' => '#73a68d'));
        $url = mobileUrl('member', NULL, true);
        if (!(empty($templateid))) {
            m('message')->sendTplNotice($openid, $templateid, $msg, $url);
            return;
        }
        m('message')->sendCustomNotice($openid, $msg, $url);
    }

    public function consumeCouponCount($openid, $money = 0, $merch_array, $goods_array)
    {
        global $_W;
        global $_GPC;
        $time = time();
        $param = array();
        $param[':openid'] = $openid;
        $param[':uniacid'] = $_W['uniacid'];
        $sql = 'select d.id,d.couponid,c.enough,c.merchid,c.limitgoodtype,c.limitgoodcatetype,c.limitgoodcateids,c.limitgoodids  from ' . tablename('ching_leeing_coupon_data') . ' d';
        $sql .= ' left join ' . tablename('ching_leeing_coupon') . ' c on d.couponid = c.id';
        $sql .= ' where d.openid=:openid and d.uniacid=:uniacid and c.merchid=0 and d.merchid=0 and c.coupontype=0 and d.used=0 ';
        $sql .= ' and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<=' . $time . ' && c.timeend>=' . $time . ')) order by d.gettime desc';
        $list = pdo_fetchall($sql, $param);
        if (!(empty($merch_array))) {
            foreach ($merch_array as $key => $value) {
                $merchid = $key;
                if (0 < $merchid) {
                    $ggprice = $value['ggprice'];
                    $param[':merchid'] = $merchid;
                    $sql = 'select d.id,d.couponid,c.enough,c.merchid,c.limitgoodtype,c.limitgoodcatetype,c.limitgoodcateids,c.limitgoodids  from ' . tablename('ching_leeing_coupon_data') . ' d';
                    $sql .= ' left join ' . tablename('ching_leeing_coupon') . ' c on d.couponid = c.id';
                    $sql .= ' where d.openid=:openid and d.uniacid=:uniacid and c.merchid=:merchid and d.merchid=:merchid and c.coupontype=0  and d.used=0 ';
                    $sql .= ' and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<=' . $time . ' && c.timeend>=' . $time . '))';
                    $merch_list = pdo_fetchall($sql, $param);
                    if (!(empty($merch_list))) {
                        $list = array_merge($list, $merch_list);
                    }
                }
            }
        }
        $goodlist = array();
        if (!(empty($goods_array))) {
            foreach ($goods_array as $key => $value) {
                $goodparam[':uniacid'] = $_W['uniacid'];
                $goodparam[':id'] = $value['goodsid'];
                $sql = 'select id,cates,marketprice,merchid  from ' . tablename('ching_leeing_goods');
                $sql .= ' where uniacid=:uniacid and id =:id order by id desc LIMIT 1 ';
                $good = pdo_fetch($sql, $goodparam);
                $good['saletotal'] = $value['total'];
                $good['optionid'] = $value['optionid'];
                if (!(empty($good))) {
                    $goodlist[] = $good;
                }
            }
        }
        $list = $this->checkcouponlimit($list, $goodlist);
        return count($list);
    }

    public function rechargeCouponCount($openid, $money = 0)
    {
        global $_W;
        global $_GPC;
        $time = time();
        $sql = 'select count(*) from ' . tablename('ching_leeing_coupon_data') . ' d ' . '  left join ' . tablename('ching_leeing_coupon') . ' c on d.couponid = c.id ' . '  where d.openid=:openid and d.uniacid=:uniacid and  c.coupontype=1 and ' . $money . '>=c.enough and d.used=0 ' . ' and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<=' . $time . ' && c.timeend>=' . $time . '))';
        return pdo_fetchcolumn($sql, array(':openid' => $openid, ':uniacid' => $_W['uniacid']));
    }

    public function useConsumeCoupon($orderid = 0)
    {
        global $_W;
        global $_GPC;
        if (empty($orderid)) {
            return;
        }
        $order = pdo_fetch('select ordersn,createtime,couponid from ' . tablename('ching_leeing_order') . ' where id=:id and status>=0 and uniacid=:uniacid limit 1', array(':id' => $orderid, ':uniacid' => $_W['uniacid']));
        if (empty($order)) {
            return;
        }
        $coupon = false;
        if (!(empty($order['couponid']))) {
            $coupon = $this->getCouponByDataID($order['couponid']);
        }
        if (empty($coupon)) {
            return;
        }
        pdo_update('ching_leeing_coupon_data', array('used' => 1, 'usetime' => $order['createtime'], 'ordersn' => $order['ordersn']), array('id' => $order['couponid']));
    }

    public function getCouponByDataID($dataid = 0)
    {
        global $_W;
        $data = pdo_fetch('select id,openid,couponid,used,back,backtime from ' . tablename('ching_leeing_coupon_data') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $dataid, ':uniacid' => $_W['uniacid']));
        if (empty($data)) {
            return false;
        }
        $coupon = pdo_fetch('select * from ' . tablename('ching_leeing_coupon') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $data['couponid'], ':uniacid' => $_W['uniacid']));
        if (empty($coupon)) {
            return false;
        }
        $coupon['back'] = $data['back'];
        $coupon['backtime'] = $data['backtime'];
        $coupon['used'] = $data['used'];
        $coupon['usetime'] = $data['usetime'];
        return $coupon;
    }

    public function returnConsumeCoupon($order)
    {
        global $_W;
        if (!(is_array($order))) {
            $order = pdo_fetch('select id,openid,ordersn,createtime,couponid,status,finishtime from ' . tablename('ching_leeing_order') . ' where id=:id and status>=0 and uniacid=:uniacid limit 1', array(':id' => intval($order), ':uniacid' => $_W['uniacid']));
        }
        if (empty($order)) {
            return;
        }
        $coupon = $this->getCouponByDataID($order['couponid']);
        if (empty($coupon)) {
            return;
        }
        if (!(empty($coupon['returntype']))) {
            if (!(empty($coupon['used']))) {
                pdo_update('ching_leeing_coupon_data', array('used' => 0, 'usetime' => 0, 'ordersn' => ''), array('id' => $order['couponid']));
                $this->sendReturnMessage($order['openid'], $coupon);
            }
        }
    }

    public function sendReturnMessage($openid, $coupon)
    {
        global $_W;
        $set = m('common')->getPluginset('coupon');
        $templateid = $set['templateid'];
        $msg = array('keyword1' => array('value' => '优惠券退回', 'color' => '#73a68d'), 'keyword2' => array('value' => '您的优惠券【' . $coupon['couponname'] . '】已退回您的账户，您可以再次使用, 谢谢!', 'color' => '#73a68d'));
        $url = mobileUrl('sale/coupon/my', NULL, true);
        if (!(empty($templateid))) {
            m('message')->sendTplNotice($openid, $templateid, $msg, $url);
            return;
        }
        m('message')->sendCustomNotice($openid, $msg, $url);
    }

    public function backConsumeCoupon($orderid)
    {
        global $_W;
        if (!(is_array($orderid))) {
            $order = pdo_fetch('select id,openid,ordersn,createtime,couponid,couponmerchid,status,finishtime,`virtual`,isparent,parentid,coupongoodprice  from ' . tablename('ching_leeing_order') . ' where id=:id and status>=0 and couponid >0 and uniacid=:uniacid limit 1', array(':id' => intval($orderid), ':uniacid' => $_W['uniacid']));
        }
        if (empty($order)) {
            return;
        }
        $couponid = $order['couponid'];
        $couponmerchid = $order['couponmerchid'];
        $isparent = $order['isparent'];
        $parentid = $order['parentid'];
        $finishtime = $order['finishtime'];
        if (empty($couponid)) {
            return;
        }
        $coupon = $this->getCouponByDataID($order['couponid']);
        if (empty($coupon)) {
            return;
        }
        if (!(empty($coupon['back']))) {
            return;
        }
        $coupongoodprice = 0;
        if ($parentid == 0) {
            $coupongoodprice = $order['coupongoodprice'];
        }
        if (($isparent == 1) || ($parentid != 0)) {
            $all_done = 1;
            if ($isparent == 1) {
                if (0 < $couponmerchid) {
                    $sql = 'select id,openid,ordersn,createtime,couponid,couponmerchid,status,finishtime,`virtual`,isparent,parentid from ' . tablename('ching_leeing_order') . ' where parentid=:parentid and couponmerchid=:couponmerchid and status>=0 and uniacid=:uniacid limit 1';
                    $order = pdo_fetch($sql, array(':parentid' => $orderid, ':couponmerchid' => $couponmerchid, ':uniacid' => $_W['uniacid']));
                    if (empty($order)) {
                        return;
                    }
                    if ($order['status'] != 3) {
                        $all_done = 0;
                    } else {
                        $finishtime = $order['finishtime'];
                    }
                } else {
                    $list = m('order')->getChildOrder($orderid);
                }
            } else if (0 < $couponmerchid) {
                if ($order['status'] != 3) {
                    $all_done = 0;
                } else {
                    $finishtime = $order['finishtime'];
                }
            } else {
                $list = m('order')->getChildOrder($parentid);
            }
            if (!(empty($list))) {
                foreach ($list as $k => $v) {
                    if (($v['status'] != 3) && (0 < $v['couponid'])) {
                        $all_done = 0;
                    } else if ($finishtime < $v['finishtime']) {
                        $finishtime = $v['finishtime'];
                    }
                }
            }
        }
        if (($parentid != 0) && ($couponmerchid == 0)) {
            if ($all_done == 1) {
                $sql = 'select id,openid,ordersn,createtime,couponid,couponmerchid,status,finishtime,`virtual`,isparent,parentid from ' . tablename('ching_leeing_order') . ' where id=:id and status>=0 and uniacid=:uniacid limit 1';
                $order = pdo_fetch($sql, array(':id' => $parentid, ':uniacid' => $_W['uniacid']));
                if (empty($order)) {
                    return;
                }
            }
        }
        $backcredit = $coupon['backcredit'];
        $backmoney = $coupon['backmoney'];
        $backredpack = $coupon['backredpack'];
        $gives = array();
        $canback = false;
        if (($order['status'] == 1) && ($coupon['backwhen'] == 2)) {
            $canback = true;
        } else {
            $is_done = 0;
            if (($isparent == 1) || ($parentid != 0)) {
                if ($all_done == 1) {
                    $is_done = 1;
                }
            } else if ($order['status'] == 3) {
                $is_done = 1;
            }
            if ($is_done == 1) {
                if (!(empty($order['virtual']))) {
                    $canback = true;
                } else if ($coupon['backwhen'] == 1) {
                    $canback = true;
                } else if ($coupon['backwhen'] == 0) {
                    $canback = true;
                    $tradeset = m('common')->getSysset('trade');
                    $refunddays = intval($tradeset['refunddays']);
                    if (0 < $refunddays) {
                        $days = intval((time() - $finishtime) / 3600 / 24);
                        if ($days <= $refunddays) {
                            $canback = false;
                        }
                    }
                }
            }
        }
        if ($canback) {
            if (0 < $parentid) {
                $ordermoney = pdo_fetchcolumn('select coupongoodprice from ' . tablename('ching_leeing_order') . ' where id=:id and status>=0 and couponid >0 and uniacid=:uniacid limit 1', array(':id' => intval($parentid), ':uniacid' => $_W['uniacid']));
            } else {
                $ordermoney = $coupongoodprice;
            }
            if ($ordermoney == 0) {
                $sql = 'select ifnull( sum(og.realprice),0) from ' . tablename('ching_leeing_order_goods') . ' og ';
                $sql .= ' left join ' . tablename('ching_leeing_order') . ' o on';
                if (($couponmerchid == 0) && ($isparent == 1)) {
                    $sql .= ' o.id=og.parentorderid ';
                } else {
                    $sql .= ' o.id=og.orderid ';
                }
                $sql .= ' where o.id=:orderid and o.openid=:openid and o.uniacid=:uniacid ';
                $ordermoney = pdo_fetchcolumn($sql, array(':uniacid' => $_W['uniacid'], ':openid' => $order['openid'], ':orderid' => $order['id']));
            }
            if (!(empty($backcredit))) {
                if (strexists($backcredit, '%')) {
                    $backcredit = intval((floatval(str_replace('%', '', $backcredit)) / 100) * $ordermoney);
                } else {
                    $backcredit = intval($backcredit);
                }
                if (0 < $backcredit) {
                    $gives['credit'] = $backcredit;
                    m('member')->setCredit($order['openid'], 'credit1', $backcredit, array(0, '充值优惠券返积分'));
                }
            }
            if (!(empty($backmoney))) {
                if (strexists($backmoney, '%')) {
                    $backmoney = round(floatval((floatval(str_replace('%', '', $backmoney)) / 100) * $ordermoney), 2);
                } else {
                    $backmoney = round(floatval($backmoney), 2);
                }
                if (0 < $backmoney) {
                    $gives['money'] = $backmoney;
                    m('member')->setCredit($order['openid'], 'credit2', $backmoney, array(0, '购物优惠券返利'));
                }
            }
            if (!(empty($backredpack))) {
                if (strexists($backredpack, '%')) {
                    $backredpack = round(floatval((floatval(str_replace('%', '', $backredpack)) / 100) * $ordermoney), 2);
                } else {
                    $backredpack = round(floatval($backredpack), 2);
                }
                if (0 < $backredpack) {
                    $gives['redpack'] = $backredpack;
                    $backredpack = intval($backredpack * 100);
                    m('finance')->pay($order['openid'], 1, $backredpack, '', '购物优惠券-返现金', false);
                }
            }
            pdo_update('ching_leeing_coupon_data', array('back' => 1, 'backtime' => time()), array('id' => $order['couponid']));
            $this->sendBackMessage($order['openid'], $coupon, $gives);
        }
    }

    public function setMyCoupon($row, $time)
    {
        global $_W;
        $row['past'] = false;
        $row['thumb'] = tomedia($row['thumb']);
        $row['merchname'] = '';
        if (0 < $row['merchid']) {
            $merch_plugin = p('merch');
            if ($merch_plugin) {
                $merch_user = $merch_plugin->getListUserOne($row['merchid']);
                if (!(empty($merch_user))) {
                    $row['merchname'] = $merch_user['merchname'];
                }
            }
        }
        $row['timestr'] = '';
        if (empty($row['timelimit'])) {
            if (!(empty($row['timedays']))) {
                $row['timestr'] = date('Y-m-d', $row['gettime'] + ($row['timedays'] * 86400));
                if (($row['gettime'] + ($row['timedays'] * 86400)) < $time) {
                    $row['past'] = true;
                }
            }
        } else {
            if ($time <= $row['timestart']) {
                $row['timestr'] = date('Y-m-d H:i', $row['timestart']) . '-' . date('Y-m-d', $row['timeend']);
            } else {
                $row['timestr'] = date('Y-m-d H:i', $row['timeend']);
            }
            if ($row['timeend'] < $time) {
                $row['past'] = true;
            }
        }
        $row['css'] = 'deduct';
        if ($row['backtype'] == 0) {
            $row['backstr'] = '立减';
            $row['css'] = 'deduct';
            $row['backpre'] = true;
            $row['_backmoney'] = $row['deduct'];
        } else if ($row['backtype'] == 1) {
            $row['backstr'] = '折';
            $row['css'] = 'discount';
            $row['_backmoney'] = $row['discount'];
        } else if ($row['backtype'] == 2) {
            if (!(empty($row['backredpack']))) {
                $row['backstr'] = '返现';
                $row['css'] = 'redpack';
                $row['backpre'] = true;
                $row['_backmoney'] = $row['backredpack'];
            } else if (!(empty($row['backmoney']))) {
                $row['backstr'] = '返利';
                $row['css'] = 'money';
                $row['backpre'] = true;
                $row['_backmoney'] = $row['backmoney'];
            } else if (!(empty($row['backcredit']))) {
                $row['backstr'] = '返积分';
                $row['css'] = 'credit';
                $row['_backmoney'] = $row['backcredit'];
            }
        }
        if ($row['past']) {
            $row['css'] = 'past';
        }
        return $row;
    }

    public function setShare()
    {
        global $_W;
        global $_GPC;
        $set = m('common')->getPluginset('coupon');
        $openid = $_W['openid'];
        $url = mobileUrl('sale/coupon', NULL, true);
        $_W['shopshare'] = array('title' => $set['title'], 'imgUrl' => tomedia($set['icon']), 'desc' => $set['desc'], 'link' => $url);
        if (p('commission')) {
            $pset = p('commission')->getSet();
            if (!(empty($pset['level']))) {
                $member = m('member')->getMember($openid);
                if (!(empty($member)) && ($member['status'] == 1) && ($member['isagent'] == 1)) {
                    $_W['shopshare']['link'] = $url . '&mid=' . $member['id'];
                    if (empty($member['realname']) || empty($member['mobile'])) {
                        $trigger = true;
                    }
                } else if (!(empty($_GPC['mid']))) {
                    $_W['shopshare']['link'] = $url . '&mid=' . $_GPC['id'];
                }
            }
        }
    }

    public function perms()
    {
        return array('coupon' => array('text' => $this->getName(), 'isplugin' => true, 'child' => array('coupon' => array('text' => '优惠券', 'view' => '查看', 'add' => '添加优惠券-log', 'edit' => '编辑优惠券-log', 'delete' => '删除优惠券-log', 'send' => '发放优惠券-log'), 'category' => array('text' => '分类', 'view' => '查看', 'add' => '添加分类-log', 'edit' => '编辑分类-log', 'delete' => '删除分类-log'), 'log' => array('text' => '优惠券记录', 'view' => '查看', 'export' => '导出-log'), 'center' => array('text' => '领券中心设置', 'view' => '查看设置', 'save' => '保存设置-log'), 'set' => array('text' => '基础设置', 'view' => '查看设置', 'save' => '保存设置-log'))));
    }

    public function addtaskdata($orderid)
    {
        global $_W;
        $pdata = m('common')->getPluginset('coupon');
        $order = pdo_fetch('select id,openid,price  from ' . tablename('ching_leeing_order') . ' where id=:id   and uniacid=:uniacid limit 1', array(':id' => intval($orderid), ':uniacid' => $_W['uniacid']));
        if (empty($order)) {
            return;
        }
        if ($pdata['isopensendtask'] == 1) {
            $price = $order['price'];
            $sendtasks = pdo_fetch('select id,couponid,sendnum,num,sendpoint  from ' . tablename('ching_leeing_coupon_sendtasks') . "\n" . '             where  status =1  and uniacid=:uniacid and starttime< :now and endtime>:now and enough<:enough   and num>=sendnum' . "\n" . '             order by  enough desc,id  limit 1', array(':now' => time(), ':enough' => $price, ':uniacid' => $_W['uniacid']));
            if (!(empty($sendtasks))) {
                $data = array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'taskid' => intval($sendtasks['id']), 'couponid' => intval($sendtasks['couponid']), 'parentorderid' => 0, 'sendnum' => intval($sendtasks['sendnum']), 'tasktype' => 1, 'orderid' => $orderid, 'createtime' => time(), 'status' => 0, 'sendpoint' => intval($sendtasks['sendpoint']));
                pdo_insert('ching_leeing_coupon_taskdata', $data);
                $num = intval($sendtasks['num']) - intval($sendtasks['sendnum']);
                pdo_update('ching_leeing_coupon_sendtasks', array('num' => $num), array('id' => $sendtasks['id']));
            }
        }
        if ($pdata['isopengoodssendtask'] == 1) {
            $goodssendtasks = pdo_fetchall('select  og.id,og.goodsid,og.orderid,og.parentorderid,og.total,gst.id as taskid,gst.couponid,gst.sendnum,gst.sendpoint,gst.num' . "\n" . '            from ' . tablename('ching_leeing_coupon_goodsendtask') . ' gst' . "\n" . '            inner join ' . tablename('ching_leeing_order_goods') . ' og on og.goodsid =gst.goodsid  and (orderid=:orderid or parentorderid=:orderid)' . "\n" . '            where  og.uniacid=:uniacid and og.openid=:openid and gst.num>=gst.sendnum', array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid'], ':orderid' => $orderid));
            foreach ($goodssendtasks as $task) {
                $data = array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'taskid' => intval($task['taskid']), 'couponid' => intval($task['couponid']), 'sendnum' => intval($task['total']) * intval($task['sendnum']), 'tasktype' => 2, 'orderid' => intval($task['orderid']), 'parentorderid' => intval($task['parentorderid']), 'createtime' => time(), 'status' => 0, 'sendpoint' => intval($task['sendpoint']));
                pdo_insert('ching_leeing_coupon_taskdata', $data);
                $num = intval($task['num']) - (intval($task['total']) * intval($task['sendnum']));
                pdo_update('ching_leeing_coupon_goodsendtask', array('num' => $num), array('id' => $task['taskid']));
            }
        }
    }

    public function sendcouponsbytask($orderid)
    {
        global $_W;
        if (!(is_array($orderid))) {
            $order = pdo_fetch('select id,openid,ordersn,createtime,status,finishtime,`virtual`,isparent,parentid  from ' . tablename('ching_leeing_order') . ' where id=:id and status>=0  and uniacid=:uniacid limit 1', array(':id' => intval($orderid), ':uniacid' => $_W['uniacid']));
        }
        if (empty($order)) {
            return;
        }
        $parentid = $order['parentid'];
        $gosendtask = false;
        if ($order['status'] == 1) {
            $gosendtask = true;
            $sendpoint = 2;
        } else if ($order['status'] == 3) {
            if (0 < $parentid) {
                $num = pdo_fetchcolumn('select 1 from ' . tablename('ching_leeing_order') . "\n" . '                where parentid =:parentid and uniacid=:uniacid  and openid=:openid  and status<>3', array(':parentid' => intval($parentid), ':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
                if (empty($num)) {
                    $gosendtask = true;
                    $sendpoint = 1;
                }
            } else {
                $gosendtask = true;
                $sendpoint = 1;
            }
        }
        if ($gosendtask) {
            $list = $this->getOrderSendCoupons($orderid, $sendpoint, 1);
            if (!(empty($list)) && (0 < count($list))) {
                $this->posterbylist($list, $order['openid'], 6);
            }
        }
        $list2 = $this->getOrderSendCoupons($orderid, $sendpoint, 2);
        if (!(empty($list2)) && (0 < count($list2))) {
            $this->posterbylist($list2, $order['openid'], 6);
        }
    }

    public function getOrderSendCoupons($orderid, $sendpoint, $tasktype)
    {
        global $_W;
        if ($sendpoint == 2) {
            $taskdata = pdo_fetchall('select id, couponid, sendnum  from ' . tablename('ching_leeing_coupon_taskdata') . "\n" . '            where  status=0  and openid=:openid and uniacid=:uniacid and sendpoint=:sendpoint and tasktype=:tasktype' . "\n" . '            and (orderid=:orderid or parentorderid=:orderid)', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid'], ':sendpoint' => $sendpoint, ':tasktype' => $tasktype, ':orderid' => $orderid));
        } else {
            $taskdata = pdo_fetchall('select  id, couponid, sendnum  from ' . tablename('ching_leeing_coupon_taskdata') . "\n" . '            where  status=0  and openid=:openid and uniacid=:uniacid and sendpoint=:sendpoint and tasktype=:tasktype' . "\n" . '            and orderid=:orderid', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid'], ':sendpoint' => $sendpoint, ':tasktype' => $tasktype, ':orderid' => $orderid));
        }
        return $taskdata;
    }

    public function posterbylist($list, $openid, $gettype)
    {
        global $_W;
        global $_GPC;
        $pposter = p('poster');
        if (!($pposter)) {
            return;
        }
        $num = 0;
        $showkey = random(20);
        $data = m('common')->getPluginset('coupon');
        if (empty($data['showtemplate']) || ($data['showtemplate'] == 2)) {
            $url = mobileUrl('sale/coupon/my/showcoupons3', array('key' => $showkey), true);
        } else {
            $url = mobileUrl('sale/coupon/my/showcoupons', array('key' => $showkey), true);
        }
        foreach ($list as $taskdata) {
            $couponnum = 0;
            $couponnum = intval($taskdata['sendnum']);
            $num += $couponnum;
            $i = 1;
            while ($i <= $couponnum) {
                $couponlog = array('uniacid' => $_W['uniacid'], 'openid' => $openid, 'logno' => m('common')->createNO('coupon_log', 'logno', 'CC'), 'couponid' => $taskdata['couponid'], 'status' => 1, 'paystatus' => -1, 'creditstatus' => -1, 'createtime' => time(), 'getfrom' => intval($gettype));
                pdo_insert('ching_leeing_coupon_log', $couponlog);
                $data = array('uniacid' => $_W['uniacid'], 'openid' => $openid, 'couponid' => $taskdata['couponid'], 'gettype' => intval($gettype), 'gettime' => time());
                pdo_insert('ching_leeing_coupon_data', $data);
                $coupondataid = pdo_insertid();
                $data = array('showkey' => $showkey, 'uniacid' => $_W['uniacid'], 'openid' => $openid, 'coupondataid' => $coupondataid);
                pdo_insert('ching_leeing_coupon_sendshow', $data);
                ++$i;
            }
            pdo_update('ching_leeing_coupon_taskdata', array('status' => 1), array('id' => $taskdata['id']));
        }
        $msg = '恭喜您获得' . $num . '张优惠券!';
        $ret = m('message')->sendCustomNotice($openid, $msg, $url);
    }

    public function poster($member, $couponid, $couponnum)
    {
        global $_W;
        global $_GPC;
        $pposter = p('poster');
        if (!($pposter)) {
            return;
        }
        $coupon = $this->getCoupon($couponid);
        if (empty($coupon)) {
            return;
        }
        $i = 1;
        while ($i <= $couponnum) {
            $couponlog = array('uniacid' => $_W['uniacid'], 'openid' => $member['openid'], 'logno' => m('common')->createNO('coupon_log', 'logno', 'CC'), 'couponid' => $couponid, 'status' => 1, 'paystatus' => -1, 'creditstatus' => -1, 'createtime' => time(), 'getfrom' => 3);
            pdo_insert('ching_leeing_coupon_log', $couponlog);
            $data = array('uniacid' => $_W['uniacid'], 'openid' => $member['openid'], 'couponid' => $couponid, 'gettype' => 3, 'gettime' => time());
            pdo_insert('ching_leeing_coupon_data', $data);
            ++$i;
        }
        $set = m('common')->getPluginset('coupon');
        $this->sendMessage($coupon, $couponnum, $member);
    }

    public function getCashierCoupons($openid, $money = 0, $merchid = 0)
    {
        global $_W;
        global $_GPC;
        $time = time();
        $param = array();
        $param[':openid'] = $openid;
        $param[':uniacid'] = $_W['uniacid'];
        $param[':merchid'] = $merchid;
        $sql = 'select d.id,d.couponid,d.gettime,c.timelimit,c.timedays,c.timestart,c.timeend,c.thumb,c.couponname,c.enough,c.backtype,c.deduct,c.discount,c.backmoney,c.backcredit,c.backredpack,c.bgcolor,c.thumb,c.merchid,c.limitgoodcatetype,c.limitgoodtype,c.limitgoodcateids,c.limitgoodids  from ' . tablename('ching_leeing_coupon_data') . ' d';
        $sql .= ' left join ' . tablename('ching_leeing_coupon') . ' c on d.couponid = c.id';
        $sql .= ' where d.openid=:openid and d.uniacid=:uniacid and c.merchid=:merchid and d.merchid=:merchid  and c.coupontype=2 and d.used=0 ';
        if (0 < $money) {
            $sql .= 'and ' . $money . '>=c.enough ';
        }
        $sql .= ' and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<=' . $time . ' && c.timeend>=' . $time . ')) order by d.gettime desc';
        $list = pdo_fetchall($sql, $param);
        $list = set_medias($list, 'thumb');
        if (!(empty($list))) {
            foreach ($list as &$row) {
                $row['thumb'] = tomedia($row['thumb']);
                $row['timestr'] = '永久有效';
                if (empty($row['timelimit'])) {
                    if (!(empty($row['timedays']))) {
                        $row['timestr'] = date('Y-m-d H:i', $row['gettime'] + ($row['timedays'] * 86400));
                    }
                } else if ($time <= $row['timestart']) {
                    $row['timestr'] = date('Y-m-d H:i', $row['timestart']) . '-' . date('Y-m-d H:i', $row['timeend']);
                } else {
                    $row['timestr'] = date('Y-m-d H:i', $row['timeend']);
                }
                if ($row['backtype'] == 0) {
                    $row['backstr'] = '立减';
                    $row['css'] = 'deduct';
                    $row['backmoney'] = $row['deduct'];
                    $row['backpre'] = true;
                    if ($row['enough'] == '0') {
                        $row['color'] = 'org ';
                    } else {
                        $row['color'] = 'blue';
                    }
                } else if ($row['backtype'] == 1) {
                    $row['backstr'] = '折';
                    $row['css'] = 'discount';
                    $row['backmoney'] = $row['discount'];
                    $row['color'] = 'red ';
                } else if ($row['backtype'] == 2) {
                    if ($row['coupontype'] == '0') {
                        $row['color'] = 'red ';
                    } else {
                        $row['color'] = 'pink ';
                    }
                    if (0 < $row['backredpack']) {
                        $row['backstr'] = '返现';
                        $row['css'] = 'redpack';
                        $row['backmoney'] = $row['backredpack'];
                        $row['backpre'] = true;
                    } else if (0 < $row['backmoney']) {
                        $row['backstr'] = '返利';
                        $row['css'] = 'money';
                        $row['backmoney'] = $row['backmoney'];
                        $row['backpre'] = true;
                    } else if (!(empty($row['backcredit']))) {
                        $row['backstr'] = '返积分';
                        $row['css'] = 'credit';
                        $row['backmoney'] = $row['backcredit'];
                    }
                }
            }
            unset($row);
        }
        return $list;
    }

    public function getCoupons()
    {
        global $_W;
        $time = time();
        return pdo_fetchall('select * from ' . tablename('ching_leeing_coupon') . ' where  (timelimit = 0  or  (c.timelimit =1 and c.timestart<={$time} && timeend>={$time})) and uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
    }
}

?>