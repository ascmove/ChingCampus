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

class Index_ChingLeeingPage extends MobilePage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        checkServant();
        $memberinfo = m('member')->getInfo($_W['openid']);
        $list = m('order')->getOrderDisplay($memberinfo['schoolid']);
        //if(in_array($_W['openid'],explode(',',CHING_LEEING_DEV_USER_OPENIDS))){
        if(beta($_W['openid'])){
            header('location:'.mobileUrl('order/hall'));exit;
        }else{
            include $this->template();
        }
    }
    public function get_list()
    {
        global $_W;
        global $_GPC;
        checkServant();
        $pindex = max(1, intval($_GPC['page']));
        $memberinfo = m('member')->getInfo($_W['openid']);
        $psize = 30;
        $list = m('order')->getOrderDisplay($memberinfo['schoolid'],$psize,$pindex);
        show_json(1, array('list' => $list, 'pagesize' => $psize, 'total' => count($list)));
    }
    function addToBlackList(){
        global $_W;
        global $_GPC;
        checkServant();
        $openid = trim($_GPC['openid']);
        $memberinfo = m('member')->getInfo($_W['openid']);
        $membernoticeblackarray = explode(',',$memberinfo['membernoticeblack']);
        if($membernoticeblackarray[0] == ''){
            unset($membernoticeblackarray[0]);
        }
        array_push($membernoticeblackarray,$openid);
        $membernoticeblackarray = array_unique($membernoticeblackarray);
        pdo_update('ching_leeing_member',array('membernoticeblack'=>implode(',',$membernoticeblackarray)),array('id'=>$memberinfo['id']));
        die_json(0);
    }
    function orderEvalSubmit(){
        global $_W;
        global $_GPC;
        checkServant();
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        if($orderinfo['sr_status'] != 0){
            die_json(1);
        }
        if($orderinfo['status'] != 4){
            die_json(1);
        }
        if($orderinfo['servant_openid'] == $_W['openid']){
            checkServant();
            if($orderinfo['status'] == 4){
                pdo_update('ching_leeing_order',array(
                    'status'=>5
                ),array('id'=>$orderinfo['id']));
                pdo_insert('ching_leeing_eval',array(
                    'uniacid'=>$_W['uniacid'],
                    'from_openid'=>$_W['openid'],
                    'to_openid'=>$orderinfo['openid'],
                    'orderid'=>$orderinfo['id'],
                    'score'=>intval($_GPC['score']),
                    'evaltags'=>strval($_GPC['evalTags']),
                    'evalcontent'=>trim($_GPC['evalContent']),
                    'useblack'=>intval($_GPC['defriend']),
                    'uniacid'=>$_W['uniacid'],
                    'evaltime'=>time()
                ));
                $articles[] = array('title' => urlencode('您的服务单评价成功'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n评价星级：'.intval($_GPC['score']).'星\n订单号：'.$outTradeNo.'\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                m('message')->sendNews($_W['openid'], $articles);
                die_json(0);
            }else{
                die_json(1);
            }
        }
    }
    function orderEval(){
        global $_W;
        global $_GPC;
        checkServant();
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        if($orderinfo['sr_status'] != 0){
            show_message('申诉订单不允许评价!');
        }
        include $this->template();
    }
    function addServiceTime(){
        global $_W;
        global $_GPC;
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        if($orderinfo['openid'] != $_W['openid']){
            die_json(1,array(
                'title'=>'错误',
                'tips'=>'您无权查看此订单!'
            ));
        }
        if(($orderinfo['ispay'] == 1)&&($orderinfo['status'] != 4)&&($orderinfo['status'] != 9)&&($orderinfo['status'] != 7)&&($orderinfo['status'] != 8)&&($orderinfo['openid'] == $_W['openid'])){
            pdo_update('ching_leeing_order',array('servicetime'=>$orderinfo['servicetime']+intval($_GPC['minutes'])*60),array('id'=>$orderinfo['id']));
            $articles[] = array('title' => urlencode('您的服务单修改成功'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n修改内容：增加服务时间\n服务限期：'.getTime($orderinfo['servicetime']+intval($_GPC['minutes'])*60).'\n订单号：'.$outTradeNo.'\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
            m('message')->sendNews($_W['openid'], $articles);
            die_json(0);
        }else{
            die_json(1);
        }
    }
    function modServiceAbstract(){
        global $_W;
        global $_GPC;
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        if($orderinfo['openid'] != $_W['openid']){
            die_json(1,array(
                'title'=>'错误',
                'tips'=>'您不是发布者!'
            ));
        }
        if(($orderinfo['ispay'] == 1)&&($orderinfo['status'] != 4)&&($orderinfo['status'] != 9)&&($orderinfo['status'] != 7)&&($orderinfo['status'] != 8)&&($orderinfo['openid'] == $_W['openid'])){
            pdo_update('ching_leeing_order',array('serviceabstract'=>trim($_GPC['serviceAbstract'])),array('id'=>$orderinfo['id']));
            $articles[] = array('title' => urlencode('您的服务单修改成功'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n修改内容：修改服务内容\n服务内容：'.trim($_GPC['serviceAbstract']).'\n订单号：'.$outTradeNo.'\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
            m('message')->sendNews($_W['openid'], $articles);
            die_json(0);
        }else{
            die_json(1);
        }
    }
    function extraFeeJspay(){
        global $_W;
        global $_GPC;
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        if(floatval($_GPC['extraFee']) < 0.5){
            die_json(1);
        }
        if($orderinfo['openid'] != $_W['openid']){
            die_json(1,array(
                'title'=>'错误',
                'tips'=>'您不是发布者!'
            ));
        }
        if(($orderinfo['status'] != 0)&&($orderinfo['status'] != 1)&&($orderinfo['status'] != 2)){
            die_json(1,array(
                'title'=>'错误',
                'tips'=>'订单状态不允许追加金额!'
            ));
        }
        $memberinfo = m('member')->getInfo($_W['openid']);
        if(($orderinfo['ispay'] == 1)&&($orderinfo['status'] != 4)&&($orderinfo['status'] != 9)&&($orderinfo['status'] != 7)&&($orderinfo['status'] != 8)&&($orderinfo['openid'] == $_W['openid'])){
            $outTradeNo = m('common')->createNO('paylog', 'tid', 'CH');
            $params = array();
            $params['tid'] = $outTradeNo;
            $params['user'] = $_W['openid'];
            $params['fee'] = (double)($_GPC['extraFee']);
            $params['title'] = $orderinfo['servicetypename'].'-追加付款';
            $setting = uni_setting($_W['uniacid'], array('payment'));
            if (is_array($setting['payment']))
            {
                $options = $setting['payment']['wechat'];
                $options['appid'] = $_W['account']['key'];
                $options['secret'] = $_W['account']['secret'];
            }
            $log = array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid'],'tid' => $outTradeNo, 'oid'=>$orderinfo['id'],'tag'=>'extraprice','money' => $params['fee'], 'status' => 0,'createtime'=>time());
            pdo_insert('ching_leeing_paylog', $log);
            $plid = pdo_insertid();
            $wechat = m('common')->wechat_build($params, $options, 4900011);
            //通过session防止支付状态丢失
            $_SESSION['log-'.$_W['openid']]['oid'] = $orderinfo['id'];
            $_SESSION['log-'.$_W['openid']]['plid'] = $plid;
            die_json(0,array(
                'jsPay'=>array(
                    'appId'=>$wechat['appId'],
                    'nonceStr'=>$wechat['nonceStr'],
                    'package'=>$wechat['package'],
                    'paySign'=>$wechat['paySign'],
                    'signType'=>$wechat['signType'],
                    'timeStamp'=>$wechat['timeStamp'],
                ),
                'outTradeNo'=>$outTradeNo,
                'timestamp'=>$wechat['timeStamp']
            ));
        }else{
            die_json(1);
        }
    }
    //抢单页面
    public function takeUserOrderDetail()
    {
        global $_W;
        global $_GPC;
        checkServant();
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        if($orderinfo['openid'] == CHING_LEEING_DEV_USER_OPENID){
            show_message('测试订单不允许接收');
        }
        if($orderinfo['status'] > 0){
            if($orderinfo['servant_openid'] != $_W['openid']){
                show_message('订单已被其他同学接收');
            }else{
                header('location: ' . mobileLink('orderhall/getuserorderdetail',array('outTradeNo'=>$outTradeNo)));
                exit;
            }
        }else if($orderinfo['status'] == -1){
            show_message('订单已取消');
        }else{
            include $this->template();
        }
    }
    public function takeOrder()
    {
        global $_W;
        global $_GPC;
        checkServant();
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        $info = m('order')->getOrderInfo($outTradeNo);
        $servant_memberinfo = m('member')->getInfo($_W['openid']);
        if($info['ispay'] == 0){
            die_json(1,array(
                'title'=>'提示',
                'tips'=>'订单未付款'
            ));
        }
        if((in_array($info['openid'],explode(',',CHING_LEEING_DEV_USER_OPENIDS)))&&(!in_array($_W['openid'],explode(',',CHING_LEEING_DEV_USER_OPENIDS)))){
            die_json(1,array(
                'title'=>'提示',
                'tips'=>'抱歉，内部测试订单不可接单'
            ));
        }
        if($info['status'] > 0){
            if($info['servant_openid'] != $_W['openid']){
                die_json(1,array(
                    'title'=>'提示',
                    'tips'=>'订单已被其他同学接收'
                ));
            }else{
                die_json(0);
            }
            die_json(1,array(
                'title'=>'提示',
                'tips'=>'订单未付款'
            ));
        }else if($info['status'] == -1){
            die_json(1,array(
                'title'=>'提示',
                'tips'=>'订单已取消'
            ));
        }else{
            $ret = pdo_update('ching_leeing_order',array(
                'servant_openid'=>$_W['openid'],
                'status'=>1,
                'orderrectime'=>time()
            ),array('id'=>$info['id']));
            if($ret){
                $articles[] = array('title' => urlencode('接单成功通知'), 'description' => urlencode('服务类型：'.$info['servicetypename'].'\n服务内容：'.$info['serviceabstract'].'\n服务者：'.$servant_memberinfo['realname'].'\n联系方式：'.$servant_memberinfo['mobile'].'\n订单号：'.$outTradeNo.'\n您可在订单详情页与对方在线沟通.\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                $ret = m('message')->sendNews($info['openid'], $articles);
                m('notice')->sendTakeOrderMessage($info['openid'],$info['servicetypename'],$info['serviceabstract'],$servant_memberinfo['realname'],$servant_memberinfo['mobile'],$outTradeNo);
                if(is_error($ret)){
                    m('notice')->sendTakeOrderMessage($info['openid'],$info['servicetypename'],$info['serviceabstract'],$servant_memberinfo['realname'],$servant_memberinfo['mobile'],$outTradeNo);
                }
                unset($articles);
                $articles[] = array('title' => urlencode('您已成功接单'), 'description' => urlencode('服务类型：'.$info['servicetypename'].'\n服务内容：'.$info['serviceabstract'].'\n完成时间：'.getTime($info['servicetime']+intval($_GPC['minutes'])*60).'\n订单号：'.$outTradeNo.'\n请您留意服务时间与要求,详细信息请与发布者沟通.\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                m('message')->sendNews($_W['openid'], $articles);
                die_json(0);
            }else{
                die_json(1,array(
                    'title'=>'提示',
                    'tips'=>'抢单失败!'
                ));
            }
        }
    }
    public function changeOrderStatusPublish()
    {
        global $_W;
        global $_GPC;
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        $status = sec_trim($_GPC['status']);
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        if($orderinfo['openid'] != $_W['openid']){
            die_json(1,array(
                'title'=>'错误',
                'tips'=>'您不是发布者!'
            ));
        }
        switch ($status)
        {
            //发布者申请仲裁
            case 505:
                if($orderinfo['sr_status'] >=6){
                    die_json(1,array(
                        'title'=>'提示',
                        'tips'=>'对方已经提交过申请,我们将尽快处理.'
                    ));
                }else if(($orderinfo['sr_status'] ==3)||($orderinfo['sr_status'] ==4)||($orderinfo['sr_status'] ==5)){
                    die_json(1,array(
                        'title'=>'提示',
                        'tips'=>'您已经提交过申请!'
                    ));
                }else{
                    pdo_update('ching_leeing_order',array(
                        'sr_status'=>5
                    ),array('id'=>$orderinfo['id']));
                    $articles[] = array('title' => urlencode('您的服务单正申请客服处理'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n订单号：'.$outTradeNo.'\n我们将尽快审核您的订单,并在必要时联系双方.!\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                    m('message')->sendNews($orderinfo['openid'], $articles);
                    die_json(0);
                }
                break;
            //取消退款操作
            case 3:
                if($orderinfo['status'] == 8){
                    pdo_update('ching_leeing_order',array(
                        'status'=>8,
                        'refundtime'=>time()
                    ),array('id'=>$orderinfo['id']));
                    die_json(0);
                }else{
                    die_json(1,array(
                        'title'=>'提示',
                        'tips'=>'订单状态错误!'
                    ));
                }
                break;
            //申请退款操作
            case 8:
                if(($orderinfo['status'] == 1)||($orderinfo['status'] == 2)||($orderinfo['status'] == 3)){
                    pdo_update('ching_leeing_order',array(
                        'status'=>8,
                        'refundtime'=>time()
                    ),array('id'=>$orderinfo['id']));
                    $articles[] = array('title' => urlencode('您的服务单正申请退款'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n订单号：'.$outTradeNo.'\n感谢您的使用!\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                    m('message')->sendNews($orderinfo['openid'], $articles);
                    unset($articles);
                    $articles[] = array('title' => urlencode('您的服务单对方正申请退款'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n订单号：'.$outTradeNo.'\n感谢您的使用!\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                    m('message')->sendNews($orderinfo['servant_openid'], $articles);
                    die_json(0);
                }else{
                    die_json(1,array(
                        'title'=>'提示',
                        'tips'=>'订单状态错误!'
                    ));
                }
                break;
            //订单完成操作
            //资金关键操作
            case 4:
                if(($orderinfo['status'] == 1)||($orderinfo['status'] == 2)||($orderinfo['status'] == 3)){
                    if(!empty($_W['appfee'])){
                        $rate = 1-$_W['appfee']*0.01;
                    }else{
                        $fx = pdo_fetchcolumn('select costpercent from '.tablename('ching_leeing_services'.' where id=:id',array(':id'=>$orderinfo['servicetypeid'])));
                        if(!empty($fx)){
                            $rate = 1-$fx*0.01;
                        }else{
                            $rate = 1;
                        }
                    }
                    if($rate > 1){
                        $rate = 1;
                    }
                    //酬金计算
                    $price = $orderinfo['price']*$rate+$orderinfo['extraprice'];
                    m('balance')->add($price,$orderinfo['servant_openid']);
                    pdo_update('ching_leeing_order',array(
                        'status'=>4,
                        'finishtime'=>time()
                    ),array('id'=>$orderinfo['id']));
                    //费率关键操作
                    $articles[] = array('title' => urlencode('您的服务单已经确认'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n订单号：'.$outTradeNo.'\n感谢您的使用!\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                    m('message')->sendNews($orderinfo['openid'], $articles);
                    unset($articles);
                    $articles[] = array('title' => urlencode('您的服务单已被确认'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n订单号：'.$outTradeNo.'\n服务小费已经添加到您的余额账户,请您关注余额变化!\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                    m('message')->sendNews($orderinfo['servant_openid'], $articles);
                    die_json(0);
                }else{
                    die_json(1,array(
                        'title'=>'提示',
                        'tips'=>'订单状态错误!'
                    ));
                }
                break;
            //取消订单
            case -1:
                if($orderinfo['status'] == 0){
                    $msg = m('order')->orderRefund($outTradeNo);
                    if(!is_error($msg)){
                        pdo_update('ching_leeing_order',array(
                            'status'=>-1,
                            'usercanceled'=>1,
                            'canceltime'=>time(),
                            'refundtime'=>time()
                        ),array('id'=>$orderinfo['id']));
                        $articles[] = array('title' => urlencode('您的服务单已经取消'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n订单号：'.$outTradeNo.'\n您的资金将原路退回退回,请您关注!\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                        m('message')->sendNews($_W['openid'], $articles);
                        die_json(0);
                    }else{
                        if(($msg['errno'] == -2)&&($msg['message'] == "OK | 可用余额不足，请充值后重新发起")){
                            //可用余额不足，请充值
                            die_json(1,array(
                                'title'=>'提示',
                                'tips'=>$msg['result']['message']
                            ));
                        }
                        die_json(1,array(
                            'title'=>'提示',
                            'tips'=>'退款失败,请联系客服处理!'
                        ));
                    }
                }else{
                    die_json(1,array(
                        'title'=>'提示',
                        'tips'=>'订单状态不允许取消!'
                    ));
                }
                break;
            default:
                die_json(1,array(
                    'title'=>'提示',
                    'tips'=>'参数错误!'
                ));
        }
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        die_json(0);
    }
    public function changeOrderStatusServant()
    {
        global $_W;
        global $_GPC;
        checkServant();
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        $status = sec_trim($_GPC['status']);
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        if($orderinfo['servant_openid'] != $_W['openid']){
            die_json(1,array(
                'title'=>'提示',
                'tips'=>'服务者不一致!'
            ));
        }
        switch ($status)
        {
            //服务者申请仲裁
            case 506:
                if(($orderinfo['sr_status'] <= 5)&&($orderinfo['sr_status'] > 0)){
                    die_json(1,array(
                        'title'=>'提示',
                        'tips'=>'对方已经提交过申请,我们将尽快处理.'
                    ));
                }else if(($orderinfo['sr_status'] ==7)||($orderinfo['sr_status'] ==8)||($orderinfo['sr_status'] ==6)){
                    die_json(1,array(
                        'title'=>'提示',
                        'tips'=>'您已经提交过申请!'
                    ));
                }else{
                    pdo_update('ching_leeing_order',array(
                        'sr_status'=>6
                    ),array('id'=>$orderinfo['id']));
                    $articles[] = array('title' => urlencode('您的服务单正申请客服处理'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n订单号：'.$outTradeNo.'\n我们将尽快审核您的订单,并在必要时联系双方.!\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                    m('message')->sendNews($orderinfo['servant_openid'], $articles);
                    die_json(0);
                }
                break;
            //同意退款操作
            //资金关键操作
            case 9:
                if($orderinfo['status'] == 8){
                    $ret = m('order')->orderRefund($outTradeNo);
                    if(!is_error($ret)){
                        pdo_update('ching_leeing_order',array(
                            'status'=>9,
                            'refundtime'=>time()
                        ),array('id'=>$orderinfo['id']));
                        $articles[] = array('title' => urlencode('对方同意了您的退款申请'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n订单号：'.$outTradeNo.'\n您的资金将原路退回,请关注微信支付通知!\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                        m('message')->sendNews($orderinfo['openid'], $articles);
                        unset($articles);
                        $articles[] = array('title' => urlencode('您的服务单已经退款'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n订单号：'.$outTradeNo.'\n欢迎您再次使用!\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                        m('message')->sendNews($orderinfo['servant_openid'], $articles);
                        die_json(0);
                    }else{
                        die_json(1,array(
                            'title'=>'提示',
                            'tips'=>$ret['result']['message']
                        ));
                    }
                }else{
                    die_json(1,array(
                        'title'=>'提示',
                        'tips'=>'订单状态错误!'
                    ));
                }
                break;
            //确认服务完成操作
            case 3:
                if($orderinfo['status'] == 2){
                    pdo_update('ching_leeing_order',array(
                        'status'=>3,
                        'orderfinishtime'=>time()
                    ),array('id'=>$orderinfo['id']));
                    $articles[] = array('title' => urlencode('您的服务已经完成'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n订单号：'.$outTradeNo.'\n服务者已经确认完成服务，请您核实服务订单是否已经完成。\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                    m('message')->sendNews($orderinfo['openid'], $articles);
                    die_json(0);
                }else if($orderinfo['status'] == 8){
                    //拒绝退款申请
                    pdo_update('ching_leeing_order',array(
                        'status'=>3,
                        'orderfinishtime'=>time()
                    ),array('id'=>$orderinfo['id']));
                    $articles[] = array('title' => urlencode('服务者拒绝了您的申请'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n订单号：'.$outTradeNo.'\n详细信息请与接单者沟通.\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                    m('message')->sendNews($orderinfo['openid'], $articles);
                    unset($articles);
                    $articles[] = array('title' => urlencode('您已拒绝对方退款申请'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n订单号：'.$outTradeNo.'\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                    m('message')->sendNews($orderinfo['servant_openid'], $articles);
                    die_json(0);
                }else{
                    die_json(1,array(
                        'title'=>'提示',
                        'tips'=>'订单状态错误!'
                    ));
                }
                break;
            //确认开始服务操作
            case 2:
                if($orderinfo['status'] == 1){
                    pdo_update('ching_leeing_order',array(
                        'status'=>2,
                        'orderstarttime'=>time()
                    ),array('id'=>$orderinfo['id']));
                    $articles[] = array('title' => urlencode('您的服务正在进行中'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n订单号：'.$outTradeNo.'\n详细信息请与接单者沟通.\n如有疑问或需要帮助请直接拨打我们的客服电话：'.$_W['apptel']), 'url' => mobileLink('order/detail',array('outTradeNo'=>$outTradeNo)));
                    m('message')->sendNews($orderinfo['openid'], $articles);
                    die_json(0);
                }else{
                    die_json(1,array(
                        'title'=>'提示',
                        'tips'=>'订单状态错误!'
                    ));
                }
                break;
            default:
                die_json(1,array(
                    'title'=>'提示',
                    'tips'=>'参数错误!'
                ));
        }
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        die_json(0);
    }
    public function getUserOrderDetail()
    {
        global $_W;
        global $_GPC;
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        $memberinfo = m('member')->getInfo($_W['openid']);
        $msg = pdo_fetchcolumn('select count(*) from '.tablename('ching_leeing_chat_log').' where uniacid=:uniacid and ordersn=:ordersn and to_openid=:to_openid and isread=0',array(':uniacid'=>$_W['uniacid'],':ordersn'=>$outTradeNo,':to_openid'=>$_W['openid']));
        include $this->template();
    }
    //支付完成跳转,检测支付结果
    public function getOrderPayResult()
    {
        global $_W;
        global $_GPC;
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        if($orderinfo['openid'] != $_W['openid']){
            die_json(1,array(
                'title'=>'错误',
                'tips'=>'您不是发布者!'
            ));
        }
        $memberinfo = m('member')->getInfo($_W['openid']);
        if(empty($orderinfo)){
            show_message('订单不存在!(ER34)');
        }
        if($orderinfo['openid'] != $_W['openid']){
            show_message('参数错误!(ER31)');
        }

        if($orderinfo['ispay'] == 1){
            header('location: ' . mobileLink('orderhall/getorderdetail',array('outTradeNo'=>$outTradeNo)));
            exit;
        }
    }
    //支付完成自动跳转
    public function getOrderDetail()
    {
        global $_W;
        global $_GPC;
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        $memberinfo = m('member')->getInfo($_W['openid']);
        if($orderinfo['openid'] != $_W['openid']){
            die_json(1,array(
                'title'=>'错误',
                'tips'=>'您不是发布者!'
            ));
        }
//        if($orderinfo['ispay'] != 1){
//            //主动查询
//            $row = pdo_get("uni_settings", array('uniacid' => $_W['uniacid']), array('payment'));
//            $payment = @iunserializer($row['payment']);
//            $wechat['appid'] = $_W['account']['key'];
//            $wechat['mch_id'] = $payment['wechat']['mchid'];
//            $wechat['apikey'] = $payment['wechat']['apikey'];
//            $ret = m('common')->wechat_order_query($outTradeNo, $orderinfo['price'], $wechat);
//            if(!is_error($ret)){
//                print_r($ret);exit;
//                if(($ret['return_code'] == 'SUCCESS')&&($ret['trade_state'] == 'SUCCESS')){
//                    //OK
//                    $tid = $outTradeNo;
//                    $sql = 'SELECT * FROM ' . tablename('ching_leeing_paylog') . ' WHERE `uniacid`=:uniacid and `tag`="price" AND `tid`=:tid  limit 1';
//                    $params = array();
//                    $params[':tid'] = $tid;
//                    $params[':uniacid'] = $_W['uniacid'];
//                    $log = pdo_fetch($sql, $params);
//                    if (!(empty($log)) && ($log['status'] == '0') && ($log['money'] == $orderinfo['price'])) {
//                        pdo_update('ching_leeing_paylog', array('status'=>1,'transid'=>$ret['transaction_id']), array('plid' => $log['plid']));
//                        $orderinfo = pdo_fetch('select * from ' . tablename('ching_leeing_order') . ' where uniacid=:uniacid and ordersn=:ordersn', array(':uniacid' => $_W['uniacid'],':ordersn'=>$log['tid']));
//                        if(($orderinfo['recservicenotify'] == 1)&&(m('service')->notifyStatus($orderinfo['servicetypeid']))){
//                            //群发通知
//                            unset($params);
//                            $params = array();
//                            $params[':uniacid'] = $_W['uniacid'];
//                            $params[':openid'] = $log['openid'];
//                            if($orderinfo['serviceschoolid'] != ''){
//                                $extra = ' and schoolid=:schoolid';
//                                $params[':schoolid'] = $orderinfo['serviceschoolid'];
//                            }
//                            $receiver = pdo_fetchall('select openid,servicenoticeblack,membernoticeblack,servicepushbutton,usersex from '.tablename('ching_leeing_member').' where serviceapplied=2 and uniacid=:uniacid and openid<>:openid'.$extra ,$params);
//                            $count = 0;
//                            $shouldpush = 0;
//                            foreach ($receiver as $rec){
//                                if($rec['servicepushbutton'] != 1){
//                                    continue;
//                                }
//                                $service_block = explode(',',$rec['servicenoticeblack']);
//                                if(in_array($orderinfo['servicetypeid'],$service_block)){
//                                    continue;
//                                }
//                                $member_block = explode(',',$rec['membernoticeblack']);
//                                if(in_array($orderinfo['openid'],$member_block)){
//                                    continue;
//                                }
//                                if($orderinfo['sexrequire'] == 1){
//                                    if($rec['usersex'] != 1){
//                                        continue;
//                                    }
//                                }
//                                if($orderinfo['sexrequire'] == 2){
//                                    if($rec['usersex'] != 2){
//                                        continue;
//                                    }
//                                }
//                                if($orderinfo['allowseeaddress'] == 1){
//                                    $address = $orderinfo['serviceschool'].' '.$orderinfo['address'];
//                                }else{
//                                    $address = '接单后可见';
//                                }
//                                $shouldpush += 1;
//                                //$push = m('notice')->sendNewOrderMessage($rec['openid'],$orderinfo['servicetypename'],getTime($orderinfo['servicetime']),$address,$orderinfo['price'],$orderinfo['serviceabstract'],$orderinfo['ordersn'],$_W['uniacid']);
//                                if(!is_error($push)){
//                                    $count += 1;
//                                }
//                            }
//                            if($count > 0){
//                                $extrastr = '\n您的服务单已推送到'.$count.'位服务者';
//                            }
//                        }
//                        $platform = m('common')->getSysset('platform',$_W['uniacid']);
//                        $_W['apptel'] = empty($platform['tel'])?'010-88886666':$platform['tel'];
//                        $link = str_replace("addons/ching_leeing/","",mobileLink('orderhall/getorderdetail',array('outTradeNo'=>$log['tid'])));
//                        $articles[] = array('title' => urlencode('您提交的服务单已成功付款'), 'description' => urlencode('服务类型：'.$orderinfo['servicetypename'].'\n服务内容：'.$orderinfo['serviceabstract'].'\n完成时间：'.getTime($orderinfo['servicetime']).' 前\n联系信息：'.$orderinfo['postname'].' '.$orderinfo['mobile'].'\n支付酬金：'.$log['money'].'元\n订单号：'.$log['tid'].$extrastr.'\n\n如有疑问或需要帮助请直接拨打我们的客服电话:'.$_W['apptel']), 'url' => $link);
//                        $account = m('common')->getAccount();
//                        m('message')->sendNews($orderinfo['openid'], $articles ,$account);
//                        pdo_update('ching_leeing_order',array('ispay'=>1,'pushed'=>$count,'shouldpush'=>$shouldpush,'transid'=>$ret['transaction_id']),array('id'=>$orderinfo['id']));
//                        //处理完成
//                    } else {
//                        show_message('订单状态错误!(ER34)');
//                    }
//                }
//            }else{
//                show_message('支付错误!(ER33)');
//            }
//        }
        if($orderinfo['ispay'] != 1){
            show_message('支付错误!(ER33)');
        }
        $msg = pdo_fetchcolumn('select count(*) from '.tablename('ching_leeing_chat_log').' where uniacid=:uniacid and ordersn=:ordersn and to_openid=:to_openid and isread=0',array(':uniacid'=>$_W['uniacid'],':ordersn'=>$outTradeNo,':to_openid'=>$_W['openid']));
        include $this->template();
    }
    //取消订单
    public function cancelOrder()
    {
        global $_W;
        global $_GPC;
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        if($orderinfo['openid'] != $_W['openid']){
            die_json(1,array(
                'title'=>'错误',
                'tips'=>'您不是发布者!'
            ));
        }
        if($orderinfo['status'] != 0){
            die_json(1,array(
                'title'=>'提示',
                'tips'=>'订单状态不允许取消!'
            ));
        }
        pdo_update('ching_leeing_order',array(
            'status'=>-1,
            'canceltime'=>time()
        ),array('id'=>$orderinfo['id']));
        die_json(0);
    }
    function setOrderPayStatus(){
        global $_W;
        global $_GPC;
        $outTradeNo = sec_trim($_GPC['outTradeNo']);
        if(isMyOrder($outTradeNo,$_W['openid'])){
            $status = trim($_GPC['status']);
            switch ($status){
                case 'ok':
                    $status = 'ok';
                    break;
                case 'cancel':
                    $status = 'cancel';
                    break;
                case 'fail':
                    $status = 'fail';
                    break;
                default:
                    return;
            }
            pdo_update('ching_leeing_order',array('paystatus'=>$status),array('ordersn'=>$outTradeNo));
        }
    }
}

?>