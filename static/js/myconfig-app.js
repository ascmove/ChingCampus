/*
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

var version = 2.90;

require.config({
    urlArgs: 'v=' + version,
    baseUrl: '../addons/ching_leeing/static/js/app',
    paths: {
        'jquery': '../dist/jquery/jquery-1.11.1.min',
        'jquery.gcjs': '../dist/jquery/jquery.gcjs',
        'tpl': '../dist/tmodjs',
        'foxui': '../dist/foxui/js/foxui.min',
        'foxui.picker': '../dist/foxui/js/foxui.picker.min',
        'foxui.citydata': '../dist/foxui/js/foxui.citydata.min',
        'jquery.qrcode': '../dist/jquery/jquery.qrcode.min',
        'ydb': '../dist/Ydb/YdbOnline',
        'swiper': '../dist/swiper/swiper.min'
    },
    shim: {
        'foxui': {
            deps: ['jquery']
        },
        'foxui.picker': {
            exports: "foxui",
            deps: ['foxui', 'foxui.citydata']
        },
        'jquery.gcjs': {
            deps: ['jquery']
        }
    },
    waitSeconds: 0
});
