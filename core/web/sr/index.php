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

class Index_ChingLeeingPage extends WebPage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $orderData = $this->orderData(200, 'sr_going');
    }

    protected function orderData($status, $st)
    {
        global $_W;
        global $_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        if ($st == 'main') {
            $st = '';
        } else {
            $st = '.' . $st;
        }
        $schoolarray  = m('service')->getColleageList();
        $servicearray  = m('service')->getServiceList();
        $searchfieldarray = array(0 => array('key' => 'openid', 'name' => 'OpenID'),1 => array('key' => 'detail', 'name' => '服务说明'),2 => array('key' => 'sn', 'name' => '订单号'),3 => array('key' => 'tran', 'name' => '微信订单号'),4 => array('key' => 'addr', 'name' => '服务地址'),5 => array('key' => 'default', 'name' => '微信支付'),6 => array('key' => 'default', 'name' => '微信支付'),7 => array('key' => 'default', 'name' => '微信支付'),);

        $condition = ' uniacid = :uniacid and deleted=0';
        $uniacid = $_W['uniacid'];
        $paras = $paras1 = array(':uniacid' => $uniacid);
        switch ($status)
        {
            case -1:
                $condition .= ' and status=-1';
                break;
            case 0:
                if($status === 0){
                    $condition .= ' and status=0';
                }
                if($status === ''){
                    $condition .= '';
                }
                break;
            case 1:
                $condition .= ' and status=1';
                break;
            case 2:
                $condition .= ' and status=2';
                break;
            case 3:
                $condition .= ' and status=3';
                break;
            case 4:
                $condition .= ' and status=4';
                break;
            case 5:
                $condition .= ' and status=5';
                break;
            case 6:
                $condition .= ' and status=6';
                break;
            case 7:
                $condition .= ' and status=7';
                break;
            case 8:
                $condition .= ' and status=8';
                break;
            case 9:
                $condition .= ' and status=9';
                break;
            case 200:
                $condition .= ' and (sr_status=5 or sr_status=6) and status not in (9) ';
                break;
            case 404:
                $condition .= ' and (sr_status=3 or sr_status=4 or sr_status=7 or sr_status=8)';
                break;
            default:
                $condition .= ' and status=0';
        }
        if (empty($starttime) || empty($endtime)) {
            $starttime = strtotime('-1 month');
            $endtime = time();
        }
        //搜索学校
        $searchschool = trim($_GPC['searchschool']);
        if (!(empty($searchschool))) {
            $condition .= ' and serviceschoolid=:serviceschoolid  ';
            $paras[':serviceschoolid'] = $searchschool;
        }
        //搜索服务
        $searchservice = trim($_GPC['searchservice']);
        if (!(empty($searchservice))) {
            $condition .= ' and servicetypeid=:servicetypeid  ';
            $paras[':servicetypeid'] = $searchservice;
        }
        //搜索小费
        $searchtipslow = intval($_GPC['searchtipslow']);
        if (!(empty($searchtipslow))) {
            $condition .= ' and (price+extraprice)>=:searchtipslow  ';
            $paras[':searchtipslow'] = $searchtipslow;
        }
        $searchtipshigh = intval($_GPC['searchtipshigh']);
        if (!(empty($searchtipshigh))) {
            $condition .= ' and (price+extraprice)<=:searchtipshigh  ';
            $paras[':searchtipshigh'] = $searchtipshigh;
        }
        //自定义多条件搜索
        $searchfieldarray = array(0 => array('key' => 'openid', 'name' => 'OpenID'),1 => array('key' => 'detail', 'name' => '服务说明'),2 => array('key' => 'sn', 'name' => '订单号'),3 => array('key' => 'tran', 'name' => '微信订单号'),4 => array('key' => 'addr', 'name' => '服务地址'),5 => array('key' => 'default', 'name' => '微信支付'),6 => array('key' => 'default', 'name' => '微信支付'),7 => array('key' => 'default', 'name' => '微信支付'),);
        if (!(empty($_GPC['searchfield'])) && !(empty($_GPC['keyword']))) {
            $searchfield = trim(strtolower($_GPC['searchfield']));
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $paras[':keyword'] = htmlspecialchars_decode($_GPC['keyword'], ENT_QUOTES);
            $sqlcondition = '';
            if ($searchfield == 'ordersn') {
                $condition .= ' AND ordersn like :keyword';
                $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
            } else if ($searchfield == 'openid') {
                $condition .= ' AND openid like :keyword';
                $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
            } else if ($searchfield == 'detail') {
                $condition .= ' AND serviceabstract like :keyword';
                $params[':keyword'] = '%' . $_GPC['keyword'] . '%';
            }
        }
        $sql = 'select * from ' . tablename('ching_leeing_order') . ' where ' . $condition . ' ORDER BY createtime DESC  ';
        $sql .= 'LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
        $list = pdo_fetchall($sql, $paras);
        $sqlcount = 'select count(*) as count from ' . tablename('ching_leeing_order') . ' where ' . $condition;
        $sqlcount = pdo_fetch($sqlcount, $paras);
        $pager = pagination($sqlcount['count'], $pindex, $psize);
        load()->func('tpl');
        include $this->template('sr/list');
    }

    public function sr_done()
    {
        global $_W;
        global $_GPC;
        $orderData = $this->orderData(404, 'sr_done');
    }

    public function sr_going()
    {
        global $_W;
        global $_GPC;
        $orderData = $this->orderData(200, 'sr_going');
    }

    public function ajaxgettotals()
    {
        global $_GPC;
        $totals = m('order')->getTotals();
        $result = ((empty($totals) ? array() : $totals));
        show_json(1, $result);
    }
}

?>