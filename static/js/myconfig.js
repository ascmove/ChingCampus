/*
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

//var version = +new Date();
var version = 1.1;
var myconfig = {
    path: '../addons/ching_leeing/static/js/',
    alias: {
        'jquery': 'dist/jquery/jquery-1.11.1.min',
        'jquery.form': 'dist/jquery/jquery.form',
        'jquery.gcjs': 'dist/jquery/jquery.gcjs',
        'jquery.validate': 'dist/jquery/jquery.validate.min',
        'jquery.nestable': 'dist/jquery/nestable/jquery.nestable',
        'bootstrap': 'dist/bootstrap/bootstrap.min',
        'bootstrap.suggest': 'dist/bootstrap/bootstrap-suggest.min',
        'bootbox': 'dist/bootbox/bootbox.min',
        'sweet': 'dist/sweetalert/sweetalert.min',
        'select2': 'dist/select2/select2.min',
        'jquery.confirm': 'dist/jquery/confirm/jquery-confirm',
        'jquery.contextMenu': 'dist/jquery/contextMenu/jquery.contextMenu',
        'switchery': 'dist/switchery/switchery',
        'echarts': 'dist/echarts/echarts-all',
        'toast': 'dist/jquery/toastr.min',
        'tpl': 'dist/tmodjs'
    },
    map: {
        'js': '.js?v=' + version,
        'css': '.css?v=' + version
    },
    css: {
        'jquery.confirm': 'dist/jquery/confirm/jquery-confirm',
        'sweet': 'dist/sweetalert/sweetalert',
        'select2': 'dist/select2/select2,dist/select2/select2-bootstrap',
        'jquery.nestable': 'dist/jquery/nestable/nestable',
        'jquery.contextMenu': 'dist/jquery/contextMenu/jquery.contextMenu',
        'switchery': 'dist/switchery/switchery'
    }
    , preload: ['jquery']

}

var myrequire = function (arr, callback) {
    var newarr = [];
    $.each(arr, function () {
        var js = this;

        if (myconfig.css[js]) {
            var css = myconfig.css[js].split(',');
            $.each(css, function () {
                newarr.push("css!" + myconfig.path + this + myconfig.map['css']);
            });


        }

        var jsitem = this;
        if (myconfig.alias[js]) {
            jsitem = myconfig.alias[js];

        }
        newarr.push(myconfig.path + jsitem + myconfig.map['js']);
    });
    require(newarr, callback);
}
/*芝麻校园分销系统*/
