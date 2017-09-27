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

class Message_ChingLeeingModel
{
    public function sendTplNotice($touser, $template_id, $postdata, $url = '', $account = NULL)
    {
        if (!$account) {
            $account = m('common')->getAccount();
        }
        if (!$account) {
            return NULL;
        }
        return $account->sendTplNotice($touser, $template_id, $postdata, $url);
    }

    public function sendCustomNotice($openid, $msg, $url = '', $account = NULL)
    {
        if (!$account) {
            $account = m('common')->getAccount();
        }
        if (!$account) {
            return NULL;
        }
        $content = '';
        if (is_array($msg)) {
            foreach ($msg as $key => $value) {
                if (!empty($value['title'])) {
                    $content .= $value['title'] . ':' . $value['value'] . "\n";
                } else {
                    $content .= $value['value'] . "\n";
                    if ($key == 0) {
                        $content .= "\n";
                    }
                }
            }
        } else {
            $content = $msg;
        }
        if (!empty($url)) {
            $content .= '<a href=\'' . $url . '\'>点击查看详情</a>';
        }
        return $account->sendCustomNotice(array('touser' => $openid, 'msgtype' => 'text', 'text' => array('content' => urlencode($content))));
    }

    public function sendImage($openid, $mediaid)
    {
        $account = m('common')->getAccount();
        return $account->sendCustomNotice(array('touser' => $openid, 'msgtype' => 'image', 'image' => array('media_id' => $mediaid)));
    }

    public function sendNews($openid, $articles, $account = NULL)
    {
        if (!$account) {
            $account = m('common')->getAccount();
        }
        return $account->sendCustomNotice(array('touser' => $openid, 'msgtype' => 'news', 'news' => array('articles' => $articles)));
    }
}

?>