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

class Index_ChingLeeingPage extends WebPage
{
    public function main()
    {
        global $_W;
        include $this->template();
//        header('location: ' . webUrl('finance/withdraw'));
    }
    public function ajax()
    {
        $today = $this->platform(0);
        $yesterday = $this->platform(1,1);
        $sevendays = $this->platform(6,7);
        $thirtydays = $this->platform(29,30);
        $sixtydays = $this->platform(179,180);
        $total = $this->platform(365000);
        show_json(1, array('today' => $today,'yesterday' => $yesterday,'sevendays' => $sevendays,'thirtydays' => $thirtydays,'sixtydays' => $sixtydays,'total' => $total));
    }

    /**
     * ajax return 平台收支情况
     */
    protected function platform($daysToDisplayFromNow,$pastDuringModeDays)
    {
        global $_GPC;
        //$daysToDisplayFromNow = 0 为今日订单
        $daysToDisplayFromNow = (int)$daysToDisplayFromNow;
        $pastDuringModeDays = (int)$pastDuringModeDays;
        $orderPrice = $this->selectOrderPrice($daysToDisplayFromNow,$pastDuringModeDays);
        $orderPrice['incomeavg'] = (empty($orderPrice['incomecount']) ? 0 : round($orderPrice['incomeprice'] / $orderPrice['incomecount'], 1));
        $orderPrice['outavg'] = (empty($orderPrice['outcount']) ? 0 : round($orderPrice['outprice'] / $orderPrice['outcount'], 1));
        return array('income'=>array('count'=>$orderPrice['incomecount'],'price'=>$orderPrice['incomeprice'],'avg'=>$orderPrice['incomeavg'],'coupon'=>$orderPrice['incomecoupon']),'outcome'=>array('count'=>$orderPrice['outcount'],'price'=>$orderPrice['outprice'],'avg'=>$orderPrice['outavg']));
    }

    /**
     * 查询订单金额
     * @param int $day 查询天数
     * @return bool
     */
    protected function selectOrderPrice($daysToDisplayFromNow = 0,$pastDuringModeDays = false)
    {
        global $_W;
        $daysToDisplayFromNow = (int)$daysToDisplayFromNow;

        $createtime1 = strtotime(date('Y-m-d', time() - ($daysToDisplayFromNow * 3600 * 24)));
        if($pastDuringModeDays)
        {
            $createtime2 = $createtime1 + $pastDuringModeDays * 24 * 3600;
        }else{
            $createtime2 = time();
        }

//        $sql = 'select id,(price+extraprice) as price,createtime from ' . tablename('ching_leeing_order') . ' where uniacid = :uniacid and ispay=1 and deleted=0 and createtime between :createtime1 and :createtime2';
//        $param = array(':uniacid' => $_W['uniacid'], ':createtime1' => $createtime1, ':createtime2' => $createtime2);
        $sql = 'select money,deduct from ' . tablename('ching_leeing_paylog') .' where uniacid = :uniacid and status=1 and transid<> "" and createtime between :createtime1 and :createtime2';
        $param = array(':uniacid' => $_W['uniacid'], ':createtime1' => $createtime1, ':createtime2' => $createtime2);
        $pdo_res = pdo_fetchall($sql, $param);
        $price = 0;
        $out = 0;
        $incomecoupon = 0;
        foreach ($pdo_res as $arr) {
            $price += $arr['money'];
            $incomecoupon += $arr['deduct'];
        }

        $sql = 'select money from ' . tablename('ching_leeing_paylog') .' where uniacid = :uniacid and isrefund=1 and createtime between :createtime1 and :createtime2';
        $param = array(':uniacid' => $_W['uniacid'], ':createtime1' => $createtime1, ':createtime2' => $createtime2);
        $pdo_paylog = pdo_fetchall($sql, $param);
        foreach ($pdo_paylog as $arr) {
            $out += $arr['money'];
        }

        //datatype = 0为提现 1为充值
        $param = array(':uniacid' => $_W['uniacid'], ':createtime1' => $createtime1, ':createtime2' => $createtime2);
        $withdraw_log = pdo_fetchall('select money,charge from ' . tablename('ching_leeing_member_log') . ' where uniacid=:uniacid and money<>0 and datatype = 0 and status=1 and createtime between :createtime1 and :createtime2', $param);
        foreach ($withdraw_log as $arr) {
            $out += $arr['money']-$arr['charge'];
        }

        $result = array('incomeprice' => round($price, 1), 'outprice' => round($out, 1), 'incomecount' => count($pdo_res), 'outcount' => count($pdo_paylog)+count($withdraw_log), 'incomecoupon' => $incomecoupon);
        return $result;
    }
}

?>