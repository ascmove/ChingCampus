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

class Util_ChingLeeingPage extends WebPage
{
    public function autonum()
    {
        global $_W;
        global $_GPC;
        $num = $_GPC['num'];
        $len = intval($_GPC['len']);
        ($len == 0) && ($len = 1);
        $arr = array($num);
        $maxlen = strlen($num);
        $i = 1;

        while ($i <= $len) {
            $add = bcadd($num, $i) . '';
            $addlen = strlen($add);

            if ($maxlen < $addlen) {
                $maxlen = $addlen;
            }


            $arr[] = $add;
            ++$i;
        }

        $len = count($arr);
        $i = 0;

        while ($i < $len) {
            $zerocount = $maxlen - strlen($arr[$i]);

            if (0 < $zerocount) {
                $arr[$i] = str_pad($arr[$i], $maxlen, '0', STR_PAD_LEFT);
            }


            ++$i;
        }

        exit(json_encode($arr));
    }

    public function days()
    {
        global $_W;
        global $_GPC;
        $year = intval($_GPC['year']);
        $month = intval($_GPC['month']);
        exit(get_last_day($year, $month));
    }

    public function express()
    {
        global $_W;
        global $_GPC;
        $express = trim($_GPC['express']);
        $expresssn = trim($_GPC['expresssn']);
        $list = m('util')->getExpressList($express, $expresssn);
        include $this->template();
    }
}


?>