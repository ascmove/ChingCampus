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
        if (cv('perm.role')) {
            header('location: ' . webUrl('perm/role'));
            exit();
            return NULL;
        }


        if (cv('perm.user')) {
            header('location: ' . webUrl('perm/user'));
            exit();
            return NULL;
        }


        if (cv('perm.log')) {
            header('location: ' . webUrl('perm/log'));
            exit();
        }

    }
}


?>