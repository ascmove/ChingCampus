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
require_once IA_ROOT . '/addons/ching_leeing/vendor/autoload.php';
use Hashids\Hashids;
if (!(function_exists('encode')))
{
    function encode($id,$salt = 'ching_leeing',$minHashLength = 32)
    {
        //return $id;
        $hashids = new Hashids(CHING_TOURISM_HASHIDS_ENCODE_PREFIX.$salt,$minHashLength);
        return $hashids->encode($id);
    }
}

if (!(function_exists('decode')))
{
    function decode($string,$salt = 'ching_leeing',$minHashLength = 32)
    {
        //return $string;
        $hashids = new Hashids(CHING_TOURISM_HASHIDS_ENCODE_PREFIX.$salt,$minHashLength);
        $id = $hashids->decode($string);
        return $id[0];
    }
}

if (!(function_exists('beta'))) {
    function beta($openid){
        if(CHING_LEEING_NEW_ACCESS_PUBLIC){
            return true;
        }else{
            if(in_array($openid,explode(',',CHING_LEEING_DEV_USER_OPENIDS))){
                return true;
            }else{
                return false;
            }
        }
    }
}
if (!(function_exists('debug_group'))) {
    function debug_group($openid){
        if(in_array($openid,explode(',',CHING_LEEING_DEV_USER_OPENIDS))){
            return true;
        }else{
            return false;
        }
    }
}
if (!(function_exists('sstrval'))) {
    function sstrval($content,$length = 30){
        if(empty($content)){
            return true;
        }
        $len = mb_strlen(strval($content),'UTF8');
        if($len > $length){
            return false;
        }else{
            return strval($content);
        }
    }
}
if (!(function_exists('autoLog'))) {
    function autoLog($content){
        $filename = IA_ROOT . '/addons/ching_leeing/auto_log.log';
        $content1 = date('Y年m月d日 H:i:s') . "\tauto_log:\t";
        $fp = fopen($filename, 'a+');
        fwrite($fp, $content1.$content."\n");
        fclose($fp);
    }
}
if (!(function_exists('fetchChatLogsNumStr'))) {
    function fetchChatLogsNumStr($outTradeNo = '')
    {
        global $_W;
        $list = pdo_fetchall('select id from '.tablename('ching_leeing_chat_log').' where uniacid=:uniacid and ordersn=:ordersn',array(':uniacid'=>$_W['uniacid'],':ordersn'=>$outTradeNo));
        $count = count($list);
        if($count > 0){
            return $count;
        }else{
            return '0';
        }
    }
}
if (!(function_exists('parseSeconds'))) {
    function parseSeconds($time = 0)
    {
        if($time < 60){
            return $time.'秒';
        }
        if($time < 3600){
            return intval($time/60).'分钟';
        }
        if($time < 86400){
            return intval($time/3600).'小时';
        }
        if($time < 2592000){
            return intval($time/86400).'天';
        }
    }
}
if (!(function_exists('getTime'))) {
    function getTime($time = 0)
    {
        $w=date('w',$time);
        $week=array(
            "0"=>"星期日",
            "1"=>"星期一",
            "2"=>"星期二",
            "3"=>"星期三",
            "4"=>"星期四",
            "5"=>"星期五",
            "6"=>"星期六"
        );
        return date("m月d号 ",$time).$week[$w].date(" H:i",$time);
    }
}
if (!(function_exists('isMyOrder'))) {
    function isMyOrder($outTradeNo = '',$openid = '')
    {
        global $_W;
        if(empty($openid)){
            $openid = $_W['openid'];
            if(empty($openid)){
                return false;
            }
        }
        if(empty(trim($outTradeNo))){
            return false;
        }
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        if($orderinfo['openid'] == $openid){
            return true;
        }
        return false;
    }
}
if (!(function_exists('isMyService'))) {
    function isMyService($outTradeNo = '',$openid = '')
    {
        global $_W;
        if(empty($openid)){
            $openid = $_W['openid'];
            if(empty($openid)){
                return false;
            }
        }
        if(empty(trim($outTradeNo))){
            return false;
        }
        $orderinfo = m('order')->getOrderInfo($outTradeNo);
        if($orderinfo['servantopenid'] == $openid){
            return true;
        }
        return false;
    }
}
if (!(function_exists('checkServant'))) {
    function checkServant(){
        global $_W;
        $id = pdo_fetchcolumn('select serviceapplied from ' . tablename('ching_leeing_member') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
        if($id == 2){
            return true;
        }
        if($id == 1){
            header('location: ' . mobileLink('service/result'));
            exit;
        }
        show_message(array(
            'message'=>'您还没有成为'.$_W['appname'].'服务者!',
            'buttontext'=>'立即申请'
        ),mobileLink('service/apply'));
    }
}
if (!(function_exists('sec_trim'))) {
    function sec_trim($str = '')
    {
        if(empty($str)){
            show_message('参数错误!', '', 'error');
        }
        return trim($str);
    }
}
if (!(function_exists('die_json'))) {
    function die_json($status = 1, $return = NULL)
    {
        if(!empty($return)){
            if(is_array($return)){
                $ret = $return;
                $ret['code'] = $status;
            }else{
                $ret['msg'] = $return;
                $ret['code'] = $status;
            }
        }else{
            $ret['code'] = $status;
        }
        exit(json_encode($ret));
    }
}


if (!(function_exists('mobileLink'))) {
    function mobileLink($str,$arr)
    {
        global $_W;
        if (empty($str)) {
            return str_replace('./index.php',$_W['siteroot'].'app/index.php',mobileUrl());
        }
        return str_replace('./index.php',$_W['siteroot'].'app/index.php',mobileUrl($str,$arr));
    }
}


if (!(function_exists('o'))) {
    function o($data)
    {
        if (empty($data)) {
            return 0;
        }
        return 1;
    }
}

if (!(function_exists('ab'))) {
    function ab($data = '')
    {
        if (empty($data)) {
            return false;
        }
        return true;
    }
}


if (!(function_exists('m'))) {
    function m($name = '')
    {
        static $_modules = array();
        if (isset($_modules[$name])) {
            return $_modules[$name];
        }
        $model = CHING_LEEING_CORE . 'model/' . strtolower($name) . '.php';
        if (!(is_file($model))) {
            exit(' Model ' . $name . ' Not Found!');
        }
        require_once $model;
        $class_name = ucfirst($name) . '_ChingLeeingModel';
        $_modules[$name] = new $class_name();
        return $_modules[$name];
    }
}
if (!(function_exists('d'))) {
    function d($name = '')
    {
        static $_modules = array();
        if (isset($_modules[$name])) {
            return $_modules[$name];
        }
        $model = CHING_LEEING_CORE . 'data/' . strtolower($name) . '.php';
        if (!(is_file($model))) {
            exit(' Data Model ' . $name . ' Not Found!');
        }
        require_once CHING_LEEING_INC . 'data_model.php';
        require_once $model;
        $class_name = ucfirst($name) . '_ChingLeeingDataModel';
        $_modules[$name] = new $class_name();
        return $_modules[$name];
    }
}
if (!(function_exists('plugin_run'))) {
    function plugin_run($name = '')
    {
        $names = explode('::', $name);
        $plugin = p($names[0]);
        if (!($plugin)) {
            return false;
        }
        if (!(method_exists($plugin, $names[1]))) {
            return false;
        }
        $func_args = func_get_args();
        $args = array_splice($func_args, 1);
        return call_user_func_array(array($plugin, $names[1]), $args);
    }
}
/**
 * p 函数
 */
if (!(function_exists('p'))) {
    function p($name = '')
    {
        static $_plugins = array();
        if (isset($_plugins[$name])) {
            return $_plugins[$name];
        }
        $model = CHING_LEEING_PLUGIN . strtolower($name) . '/core/model.php';
        if (!(is_file($model))) {
            return false;
        }
        require_once CHING_LEEING_CORE . 'inc/plugin_model.php';
        require_once $model;
        $class_name = ucfirst($name) . 'Model';
        $_plugins[$name] = new $class_name($name);
        if (com_run('perm::check_plugin', $name)) {
            if ($name == 'seckill') {
                if (!(function_exists('redis')) || is_error(redis())) {
                    return false;
                }
            }
            return $_plugins[$name];
        }
        return false;
    }
}
if (!(function_exists('com'))) {
    function com($name = '')
    {
        static $_coms = array();
        if (isset($_coms[$name])) {
            return $_coms[$name];
        }
        $model = CHING_LEEING_CORE . 'com/' . strtolower($name) . '.php';
        if (!(is_file($model))) {
            return false;
        }
        require_once CHING_LEEING_CORE . 'inc/com_model.php';
        require_once $model;
        $class_name = ucfirst($name) . '_ChingLeeingComModel';
        $_coms[$name] = new $class_name($name);
        if ($name == 'perm') {
            return $_coms[$name];
        }
        if (com('perm')->check_com($name)) {
            return $_coms[$name];
        }
        return false;
    }
}
if (!(function_exists('com_run'))) {
    function com_run($name = '')
    {
        $names = explode('::', $name);
        $com = com($names[0]);
        if (!($com)) {
            return false;
        }
        if (!(method_exists($com, $names[1]))) {
            return false;
        }
        $func_args = func_get_args();
        $args = array_splice($func_args, 1);
        return call_user_func_array(array($com, $names[1]), $args);
    }
}
if (!(function_exists('byte_format'))) {
    function byte_format($input, $dec = 0)
    {
        $prefix_arr = array(' B', 'K', 'M', 'G', 'T');
        $value = round($input, $dec);
        $i = 0;
        while (1024 < $value) {
            $value /= 1024;
            ++$i;
        }
        $return_str = round($value, $dec) . $prefix_arr[$i];
        return $return_str;
    }
}
if (!(function_exists('is_array2'))) {
    function is_array2($array)
    {
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                return is_array($v);
            }
            return false;
        }
        return false;
    }
}
if (!(function_exists('set_medias'))) {
    function set_medias($list = array(), $fields = NULL)
    {
        if (empty($list)) {
            return '';
        }
        if (empty($fields)) {
            foreach ($list as &$row) {
                $row = tomedia($row);
            }
            return $list;
        }
        if (!(is_array($fields))) {
            $fields = explode(',', $fields);
        }
        if (is_array2($list)) {
            foreach ($list as $key => &$value) {
                foreach ($fields as $field) {
                    if (isset($list[$field])) {
                        $list[$field] = tomedia($list[$field]);
                    }
                    if (is_array($value) && isset($value[$field])) {
                        $value[$field] = tomedia($value[$field]);
                    }
                }
            }
            return $list;
        }
        foreach ($fields as $field) {
            if (isset($list[$field])) {
                $list[$field] = tomedia($list[$field]);
            }
        }
        return $list;
    }
}
if (!(function_exists('get_last_day'))) {
    function get_last_day($year, $month)
    {
        return date('t', strtotime($year . '-' . $month . ' -1'));
    }
}
if (!(function_exists('show_message'))) {
    function show_message($msg = '', $url = '', $type = '',$btn = '')
    {
        $site = new Page();
        $site->message($msg, $url, $type);
        exit();
    }
}
if (!(function_exists('show_json'))) {
    function show_json($status = 1, $return = NULL)
    {
        $ret = array('status' => $status, 'result' => ($status == 1 ? array('url' => referer()) : array()));
        if (!(is_array($return))) {
            if ($return) {
                $ret['result']['message'] = $return;
            }
            exit(json_encode($ret));
        } else {
            $ret['result'] = $return;
        }
        if (isset($return['url'])) {
            $ret['result']['url'] = $return['url'];
        } else if ($status == 1) {
            $ret['result']['url'] = referer();
        }
        exit(json_encode($ret));
    }
}
if (!(function_exists('is_weixin'))) {
    function is_weixin()
    {
        if (CHING_LEEING_DEBUG) {
            return true;
        }
        if (empty($_SERVER['HTTP_USER_AGENT']) || ((strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false))) {
            return false;
        }
        return true;
    }
}
if (!(function_exists('is_h5app'))) {
    function is_h5app()
    {
        if (!(empty($_SERVER['HTTP_USER_AGENT'])) && strpos($_SERVER['HTTP_USER_AGENT'], 'CK 2.0')) {
            return true;
        }
        return false;
    }
}
if (!(function_exists('is_ios'))) {
    function is_ios()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
            return true;
        }
        return false;
    }
}
if (!(function_exists('is_mobile'))) {
    function is_mobile()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/(android|bb\\d+|meego).+mobile|avantgo|bada\\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\\-(n|u)|c55\\/|capi|ccwa|cdm\\-|cell|chtm|cldc|cmd\\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\\-s|devi|dica|dmob|do(c|p)o|ds(12|\\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\\-|_)|g1 u|g560|gene|gf\\-5|g\\-mo|go(\\.w|od)|gr(ad|un)|haie|hcit|hd\\-(m|p|t)|hei\\-|hi(pt|ta)|hp( i|ip)|hs\\-c|ht(c(\\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\\-(20|go|ma)|i230|iac( |\\-|\\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\\/)|klon|kpt |kwc\\-|kyo(c|k)|le(no|xi)|lg( g|\\/(k|l|u)|50|54|\\-[a-w])|libw|lynx|m1\\-w|m3ga|m50\\/|ma(te|ui|xo)|mc(01|21|ca)|m\\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\\-2|po(ck|rt|se)|prox|psio|pt\\-g|qa\\-a|qc(07|12|21|32|60|\\-[2-7]|i\\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\\-|oo|p\\-)|sdk\\/|se(c(\\-|0|1)|47|mc|nd|ri)|sgh\\-|shar|sie(\\-|m)|sk\\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\\-|v\\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\\-|tdg\\-|tel(i|m)|tim\\-|t\\-mo|to(pl|sh)|ts(70|m\\-|m3|m5)|tx\\-9|up(\\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\\-|your|zeto|zte\\-/i', substr($useragent, 0, 4))) {
            return true;
        }
        return false;
    }
}
if (!(function_exists('b64_encode'))) {
    function b64_encode($obj)
    {
        if (is_array($obj)) {
            return urlencode(base64_encode(json_encode($obj)));
        }
        return urlencode(base64_encode($obj));
    }
}
if (!(function_exists('b64_decode'))) {
    function b64_decode($str, $is_array = true)
    {
        $str = base64_decode(urldecode($str));
        if ($is_array) {
            return json_decode($str, true);
        }
        return $str;
    }
}
if (!(function_exists('create_image'))) {
    function create_image($img)
    {
        $ext = strtolower(substr($img, strrpos($img, '.')));
        if ($ext == '.png') {
            $thumb = imagecreatefrompng($img);
        } else if ($ext == '.gif') {
            $thumb = imagecreatefromgif($img);
        } else {
            $thumb = imagecreatefromjpeg($img);
        }
        return $thumb;
    }
}
if (!(function_exists('get_authcode'))) {
    function get_authcode()
    {
        $auth = get_auth();
        return (empty($auth['code']) ? '' : $auth['code']);
    }
}
if (!(function_exists('get_auth'))) {
    function get_auth()
    {
        global $_W;
        $set = pdo_fetch('select sets from ' . tablename('ching_leeing_sysset') . ' order by id asc limit 1');
        $sets = iunserializer($set['sets']);
        if (is_array($sets)) {
            return (is_array($sets['auth']) ? $sets['auth'] : array());
        }
        return array();
    }
}
if (!(function_exists('rc'))) {
    function rc($plugin = '')
    {
        global $_W;
        global $_GPC;
        $domain = trim(preg_replace('/http(s)?:\\/\\//', '', rtrim($_W['siteroot'], '/')));
        $ip = gethostbyname($_SERVER['HTTP_HOST']);
        $setting = setting_load('site');
        $id = ((isset($setting['site']['key']) ? $setting['site']['key'] : '0'));
        $auth = get_auth();
        load()->func('communication');
        $resp = ihttp_request(CHING_LEEING_AUTH_URL, array('ip' => $ip, 'id' => $id, 'code' => $auth['code'], 'domain' => $domain, 'plugin' => $plugin), NULL, 1);
        $result = @json_decode($resp['content'], true);
        if (!(empty($result['status']))) {
            return true;
        }
        return false;
    }
}
if (!(function_exists('shop_template_compile'))) {
    function shop_template_compile($from, $to, $inmodule = false)
    {
        $path = dirname($to);
        if (!(is_dir($path))) {
            load()->func('file');
            mkdirs($path);
        }
        $content = shop_template_parse(file_get_contents($from), $inmodule);
        if ((IMS_FAMILY == 'x') && !(preg_match('/(footer|header|account\\/welcome|login|register)+/', $from))) {
            $content = str_replace('微擎', '系统', $content);
        }
        file_put_contents($to, $content);
    }
}
if (!(function_exists('shop_template_parse'))) {
    function shop_template_parse($str, $inmodule = false)
    {
        global $_W;
        $str = template_parse($str, $inmodule);
        if (strexists($_W['siteurl'], 'merchant.php')) {
            if (p('merch')) {
                $str = preg_replace('/{ifp\\s+(.+?)}/', '<?php if(mcv($1)) { ?>', $str);
                $str = preg_replace('/{ifpp\\s+(.+?)}/', '<?php if(mcp($1)) { ?>', $str);
                $str = preg_replace('/{ife\\s+(\\S+)\\s+(\\S+)}/', '<?php if( mce($1 ,$2) ) { ?>', $str);
                $str = preg_replace('/{ifab\\s+(.+?)}/', '<?php if(ab($1)) { ?>', $str);
                return $str;
            }
        }
        $str = preg_replace('/{ifp\\s+(.+?)}/', '<?php if(cv($1)) { ?>', $str);
        $str = preg_replace('/{ifpp\\s+(.+?)}/', '<?php if(cp($1)) { ?>', $str);
        $str = preg_replace('/{ife\\s+(\\S+)\\s+(\\S+)}/', '<?php if( ce($1 ,$2) ) { ?>', $str);
        $str = preg_replace('/{ifab\\s+(.+?)}/', '<?php if(ab($1)) { ?>', $str);
        return $str;
    }
}
if (!(function_exists('ce'))) {
    function ce($permtype = '', $item = NULL)
    {
        $perm = com_run('perm::check_edit', $permtype, $item);
        return $perm;
    }
}
if (!(function_exists('cv'))) {
    function cv($permtypes = '')
    {
        return true;
    }
}
if (!(function_exists('ca'))) {
    function ca($permtypes = '')
    {
        global $_W;
        $err = '您没有权限操作，请联系管理员!';
        if (!(cv($permtypes))) {
            if ($_W['isajax']) {
                show_json(0, $err);
            }
            show_message($err, '', 'error');
        }
    }
}
if (!(function_exists('cp'))) {
    function cp($pluginname = '')
    {
        $perm = com('perm');
        if ($perm) {
            return $perm->check_plugin($pluginname);
        }
        return true;
    }
}
if (!(function_exists('cpa'))) {
    function cpa($pluginname = '')
    {
        if (!(cp($pluginname))) {
            show_message('您没有权限操作，请联系管理员!', '', 'error');
        }
    }
}
if (!(function_exists('plog'))) {
    function plog($type = '', $op = '')
    {
        com_run('perm::log', $type, $op);
    }
}
if (!(function_exists('tpl_selector'))) {
    function tpl_selector($name, $options = array())
    {
        $options['multi'] = intval($options['multi']);
        $options['buttontext'] = ((isset($options['buttontext']) ? $options['buttontext'] : '请选择'));
        $options['items'] = ((isset($options['items']) && $options['items'] ? $options['items'] : array()));
        $options['readonly'] = ((isset($options['readonly']) ? $options['readonly'] : true));
        $options['callback'] = ((isset($options['callback']) ? $options['callback'] : ''));
        $options['key'] = ((isset($options['key']) ? $options['key'] : 'id'));
        $options['text'] = ((isset($options['text']) ? $options['text'] : 'title'));
        $options['thumb'] = ((isset($options['thumb']) ? $options['thumb'] : 'thumb'));
        $options['preview'] = ((isset($options['preview']) ? $options['preview'] : true));
        $options['type'] = ((isset($options['type']) ? $options['type'] : 'image'));
        $options['input'] = ((isset($options['input']) ? $options['input'] : true));
        $options['required'] = ((isset($options['required']) ? $options['required'] : false));
        $options['nokeywords'] = ((isset($options['nokeywords']) ? $options['nokeywords'] : 0));
        $options['placeholder'] = ((isset($options['placeholder']) ? $options['placeholder'] : '请输入关键词'));
        $options['autosearch'] = ((isset($options['autosearch']) ? $options['autosearch'] : 0));
        if (empty($options['items'])) {
            $options['items'] = array();
        } else if (!(is_array2($options['items']))) {
            $options['items'] = array($options['items']);
        }
        $options['name'] = $name;
        $titles = '';
        foreach ($options['items'] as $item) {
            $titles .= $item[$options['text']];
            if (1 < count($options['items'])) {
                $titles .= '; ';
            }
        }
        $options['value'] = ((isset($options['value']) ? $options['value'] : $titles));
        $readonly = (($options['readonly'] ? 'readonly' : ''));
        $required = (($options['required'] ? ' data-rule-required="true"' : ''));
        $callback = ((!(empty($options['callback'])) ? ', ' . $options['callback'] : ''));
        $id = (($options['multi'] ? $name . '[]' : $name));
        $html = '<div id=\'' . $name . '_selector\' class=\'selector\' ' . "\r\n" . '                     data-type="' . $options['type'] . '"' . "\r\n" . '                     data-key="' . $options['key'] . '"' . "\r\n" . '                     data-text="' . $options['text'] . '"' . "\r\n" . '                     data-thumb="' . $options['thumb'] . '"' . "\r\n" . '                     data-multi="' . $options['multi'] . '"' . "\r\n" . '                     data-callback="' . $options['callback'] . '"' . "\r\n" . '                     data-url="' . $options['url'] . '",' . "\r\n" . '                     data-nokeywords="' . $options['nokeywords'] . '"' . "\r\n" . '                  data-autosearch="' . $options['autosearch'] . '"' . "\r\n\r\n" . '                 >';
        if ($options['input']) {
            $html .= '<div class=\'input-group\'>' . '<input type=\'text\' id=\'' . $name . '_text\' name=\'' . $name . '_text\'  value=\'' . $options['value'] . '\' class=\'form-control text\'  ' . $readonly . '  ' . $required . '/>' . '<div class=\'input-group-btn\'>';
        }
        $html .= '<button class=\'btn btn-primary\' type=\'button\' onclick=\'biz.selector.select(' . json_encode($options) . ');\'>' . $options['buttontext'] . '</button>';
        if ($options['input']) {
            $html .= '</div>';
            $html .= '</div>';
        }
        $show = (($options['preview'] ? '' : ' style=\'display:none\''));
        if ($options['type'] == 'image') {
            $html .= '<div class=\'input-group multi-img-details container\' ' . $show . '>';
        } else {
            $html .= '<div class=\'input-group multi-audio-details container\' ' . $show . '>';
        }
        foreach ($options['items'] as $item) {
            if ($options['type'] == 'image') {
                $html .= '<div class=\'multi-item\' data-' . $options['key'] . '=\'' . $item[$options['key']] . '\' data-name=\'' . $name . '\'>' . "\r\n" . '                                      <img class=\'img-responsive img-thumbnail\' src=\'' . tomedia($item[$options['thumb']]) . '\'>' . "\r\n" . '                                      <div class=\'img-nickname\'>' . $item[$options['text']] . '</div>' . "\r\n" . '                                     <input type=\'hidden\' value=\'' . $item[$options['key']] . '\' name=\'' . $id . '\'>' . "\r\n" . '                                     <em onclick=\'biz.selector.remove(this,"' . $name . '")\'  class=\'close\'>×</em>' . "\r\n" . '                            <div style=\'clear:both;\'></div>' . "\r\n" . '                         </div>';
            } else {
                $html .= '<div class=\'multi-audio-item \' data-' . $options['key'] . '=\'' . $item[$options['key']] . '\' >' . "\r\n" . '                       <div class=\'input-group\'>' . "\r\n" . '                       <input type=\'text\' class=\'form-control img-textname\' readonly=\'\' value=\'' . $item[$options['text']] . '\'>' . "\r\n" . '                       <input type=\'hidden\'  value=\'' . $item[$options['key']] . '\' name=\'' . $id . '\'> ' . "\r\n" . '                       <div class=\'input-group-btn\'><button class=\'btn btn-default\' onclick=\'biz.selector.remove(this,"' . $name . '")\' type=\'button\'><i class=\'fa fa-remove\'></i></button>' . "\r\n" . '                       </div></div></div>';
            }
        }
        $html .= '</div></div>';
        return $html;
    }
}
if (!(function_exists('tpl_selector_new'))) {
    function tpl_selector_new($name, $options = array())
    {
        $options['multi'] = intval($options['multi']);
        $options['buttontext'] = ((isset($options['buttontext']) ? $options['buttontext'] : '请选择'));
        $options['items'] = ((isset($options['items']) && $options['items'] ? $options['items'] : array()));
        $options['readonly'] = ((isset($options['readonly']) ? $options['readonly'] : true));
        $options['callback'] = ((isset($options['callback']) ? $options['callback'] : ''));
        $options['key'] = ((isset($options['key']) ? $options['key'] : 'id'));
        $options['text'] = ((isset($options['text']) ? $options['text'] : 'title'));
        $options['thumb'] = ((isset($options['thumb']) ? $options['thumb'] : 'thumb'));
        $options['preview'] = ((isset($options['preview']) ? $options['preview'] : true));
        $options['type'] = ((isset($options['type']) ? $options['type'] : 'image'));
        $options['input'] = ((isset($options['input']) ? $options['input'] : true));
        $options['required'] = ((isset($options['required']) ? $options['required'] : false));
        $options['nokeywords'] = ((isset($options['nokeywords']) ? $options['nokeywords'] : 0));
        $options['placeholder'] = ((isset($options['placeholder']) ? $options['placeholder'] : '请输入关键词'));
        $options['autosearch'] = ((isset($options['autosearch']) ? $options['autosearch'] : 0));
        $options['optionurl'] = ((isset($options['optionurl']) ? $options['optionurl'] : ''));
        $options['selectorid'] = ((isset($options['selectorid']) ? $options['selectorid'] : ''));
        if (empty($options['items'])) {
            $options['items'] = array();
        } else if (!(is_array2($options['items']))) {
            $options['items'] = array($options['items']);
        }
        $options['name'] = $name;
        $titles = '';
        foreach ($options['items'] as $item) {
            $titles .= $item[$options['text']];
            if (1 < count($options['items'])) {
                $titles .= '; ';
            }
        }
        $options['value'] = ((isset($options['value']) ? $options['value'] : $titles));
        $readonly = (($options['readonly'] ? 'readonly' : ''));
        $required = (($options['required'] ? ' data-rule-required="true"' : ''));
        $callback = ((!(empty($options['callback'])) ? ', ' . $options['callback'] : ''));
        $id = (($options['multi'] ? $name . '[]' : $name));
        $html = '<div id=\'' . $name . '_selector\' class=\'selector\'' . "\r\n" . '                     data-type="' . $options['type'] . '"' . "\r\n" . '                     data-key="' . $options['key'] . '"' . "\r\n" . '                     data-text="' . $options['text'] . '"' . "\r\n" . '                     data-thumb="' . $options['thumb'] . '"' . "\r\n" . '                     data-multi="' . $options['multi'] . '"' . "\r\n" . '                     data-callback="' . $options['callback'] . '"' . "\r\n" . '                     data-url="' . $options['url'] . '",' . "\r\n" . '                     data-nokeywords="' . $options['nokeywords'] . '" ' . "\r\n" . '                     data-autosearch="' . $options['autosearch'] . '"' . "\r\n" . '                     data-optionurl="' . $options['optionurl'] . '"' . "\r\n" . '                     data-selectorid="' . $options['selectorid'] . '"' . "\r\n" . ' ' . "\r\n" . '                 >';
        if ($options['input']) {
            $html .= '<div class=\'input-group\'>' . '<input type=\'text\' id=\'' . $name . '_text\' name=\'' . $name . '_text\'  value=\'' . $options['value'] . '\' class=\'form-control text\'  ' . $readonly . '  ' . $required . '/>' . '<div class=\'input-group-btn\'>';
        }
        $html .= '<button class=\'btn btn-primary\' type=\'button\' onclick=\'biz.selector_new.select(' . json_encode($options) . ');\'>' . $options['buttontext'] . '</button>';
        if ($options['input']) {
            $html .= '</div>';
            $html .= '</div>';
        }
        $show = (($options['preview'] ? '' : ' style=\'display:none\''));
        if ($options['type'] == 'image') {
            $html .= '<div class=\'input-group multi-img-details container\' ' . $show . '>';
        } else {
            $html .= '<div class=\'input-group multi-audio-details container\' ' . $show . '>' . "\r\n" . '<table class=\'table\' style=\'width:600px;\'>' . "\r\n" . '                    <thead>' . "\r\n" . '                        <tr>' . "\r\n" . '                            <th style=\'width:80px;\'>商品名称</th>' . "\r\n" . '                            <th style=\'width:220px;\'></th>' . "\r\n" . '                            <th>价格/分销佣金</th>' . "\r\n" . '                            <th style=\'width:50px;\'>操作</th>' . "\r\n" . '                        </tr>' . "\r\n" . '                    </thead>' . "\r\n" . '                    <tbody id=\'param-items' . $options['selectorid'] . '\' class=\'ui-sortable\'>';
        }
        foreach ($options['items'] as $item) {
            if ($options['type'] == 'image') {
                $html .= '<div class=\'multi-item\' data-' . $options['key'] . '=\'' . $item[$options['key']] . '\' data-name=\'' . $name . '\'>' . "\r\n" . '                                      <img class=\'img-responsive img-thumbnail\' src=\'' . tomedia($item[$options['thumb']]) . '\'>' . "\r\n" . '                                      <div class=\'img-nickname\'>' . $item[$options['text']] . '</div>' . "\r\n" . '                                     <input type=\'hidden\' value=\'' . $item[$options['key']] . '\' name=\'' . $id . '\'>' . "\r\n" . '                                     <em onclick=\'biz.selector_new.remove(this,"' . $name . '")\'  class=\'close\'>×</em>' . "\r\n" . '                         </div>';
            } else if ($options['type'] == 'product') {
                if ($item['optiontitle']) {
                    $optiontitle = $item['optiontitle'][0]['title'] . '...';
                } else {
                    $optiontitle = '&yen;' . $item['packageprice'];
                }
                $html .= "\r\n" . '                    <tr class=\'multi-product-item\' data-' . $options['key'] . '=\'' . $item['goodsid'] . '\' >' . "\r\n" . '                        <input type=\'hidden\' class=\'form-control img-textname\' readonly=\'\' value=\'' . $item[$options['text']] . '\'>' . "\r\n" . '                       <input type=\'hidden\'  value=\'' . $item['goodsid'] . '\' name=\'' . $id . '\'>' . "\r\n" . '                        <td style=\'width:80px;\'>' . "\r\n" . '                            <img src=\'' . tomedia($item[$options['thumb']]) . '\' style=\'width:70px;border:1px solid #ccc;padding:1px\'>' . "\r\n" . '                        </td>' . "\r\n" . '                        <td style=\'width:220px;\'>' . $item[$options['text']] . '</td>' . "\r\n" . '                        <td>';
                $optionurl = ((empty($options['optionurl']) ? 'sale/package/hasoption' : str_replace('.', '/', $options['optionurl'])));
                if ($item['optiontitle']) {
                    $html .= '<a class=\'btn btn-default btn-sm\' data-toggle=\'ajaxModal\' href=\'' . webUrl($optionurl, array('goodsid' => $item['goodsid'], 'pid' => $item['pid'], 'selectorid' => $options['selectorid'])) . '\' id=\'' . $options['selectorid'] . 'optiontitle' . $item['goodsid'] . '\'>' . $optiontitle . '</a>' . "\r\n" . '                            <input type=\'hidden\' id=\'' . $options['selectorid'] . 'packagegoods' . $item['goodsid'] . '\' value=\'' . $item['option'] . '\' name=\'' . $options['selectorid'] . 'packagegoods[' . $item['goodsid'] . ']\'>';
                    foreach ($item['optiontitle'] as $option) {
                        $total = ((isset($option['total']) ? ',' . $option['total'] : ''));
                        $maxbuy = ((isset($option['maxbuy']) ? ',' . $option['maxbuy'] : ''));
                        $totalmaxbuy = ((isset($option['totalmaxbuy']) ? ',' . $option['totalmaxbuy'] : ''));
                        $html .= '<input type=\'hidden\' value=\'' . $option['packageprice'] . ',' . $option['commission1'] . ',' . $option['commission2'] . ',' . $option['commission3'] . $total . $maxbuy . $totalmaxbuy . '\'' . "\r\n" . '                        name=\'' . $options['selectorid'] . 'packagegoodsoption' . $option['optionid'] . '\' >';
                    }
                } else {
                    $total = ((isset($item['total']) ? ',' . $item['total'] : ''));
                    $maxbuy = ((isset($item['maxbuy']) ? ',' . $item['maxbuy'] : ''));
                    $totalmaxbuy = ((isset($item['totalmaxbuy']) ? ',' . $item['totalmaxbuy'] : ''));
                    $html .= '<a class=\'btn btn-default btn-sm\' data-toggle=\'ajaxModal\' href=\'' . webUrl($optionurl, array('goodsid' => $item['goodsid'], 'pid' => $item['pid'], 'selectorid' => $options['selectorid'])) . '\' id=\'' . $options['selectorid'] . 'optiontitle' . $item['goodsid'] . '\'>&yen;' . $item['packageprice'] . '</a>' . "\r\n" . '                            <input type=\'hidden\' id=\'' . $options['selectorid'] . 'packagegoods' . $item['goodsid'] . '\' value=\'\' name=\'' . $options['selectorid'] . 'packagegoods[' . $item['goodsid'] . ']\'>' . "\r\n" . '                    <input type=\'hidden\' value=\'' . $item['packageprice'] . ',' . $item['commission1'] . ',' . $item['commission2'] . ',' . $item['commission3'] . $total . $maxbuy . $totalmaxbuy . '\' name=\'' . $options['selectorid'] . 'packgoods' . $item['goodsid'] . '\' >';
                }
                $html .= "\r\n" . '                        </td>' . "\r\n" . '                        <td><a href=\'javascript:void(0);\' class=\'btn btn-default btn-sm\' onclick=\'biz.selector_new.remove(this,"' . $name . '")\' title=\'删除\'>' . "\r\n" . '                        <i class=\'fa fa-times\'></i></a></td>' . "\r\n" . '                    </tr>';
            } else {
                $html .= '<div class=\'multi-audio-item \' data-' . $options['key'] . '=\'' . $item[$options['c']] . '\' >' . "\r\n" . '                       <div class=\'input-group\'>' . "\r\n" . '                       <input type=\'text\' class=\'form-control img-textname\' readonly=\'\' value=\'' . $item[$options['text']] . '\'>' . "\r\n" . '                       <input type=\'hidden\'  value=\'' . $item[$options['key']] . '\' name=\'' . $id . '\'>' . "\r\n" . '                       <div class=\'input-group-btn\'><button class=\'btn btn-default\' onclick=\'biz.selector_new.remove(this,"' . $name . '")\' type=\'button\'><i class=\'fa fa-remove\'></i></button>' . "\r\n" . '                       </div></div></div>';
            }
        }
        $html .= '</tbody>' . "\r\n" . '                </table></div></div>';
        return $html;
    }
}
if (!(function_exists('tpl_daterange'))) {
    function tpl_daterange($name, $value = array(), $time = false)
    {
        global $_GPC;
        $placeholder = ((isset($value['placeholder']) ? $value['placeholder'] : ''));
        $s = '';
        if (empty($time) && !(defined('TPL_INIT_DATERANGE_DATE'))) {
            $s = "\r\n" . '<script type="text/javascript">' . "\r\n\t" . 'require(["daterangepicker"], function($){' . "\r\n\t\t" . '$(function(){' . "\r\n\t\t\t" . '$(".daterange.daterange-date").each(function(){' . "\r\n" . '         ' . "\r\n\t\t\t\t" . 'var elm = this;' . "\r\n" . '                                        var container =$(elm).parent().prev(); ' . "\r\n\t\t\t\t" . '$(this).daterangepicker({' . "\r\n\t\t\t\t\t" . ' ' . "\r\n\t\t\t\t\t" . 'format: "YYYY-MM-DD"' . "\r\n\t\t\t\t" . '}, function(start, end){' . "\r\n\t\t\t\t\t" . '$(elm).find(".date-title").html(start.toDateStr() + " 至 " + end.toDateStr());' . "\r\n\t\t\t\t\t" . 'container.find(":input:first").val(start.toDateTimeStr());' . "\r\n\t\t\t\t\t" . 'container.find(":input:last").val(end.toDateTimeStr());' . "\r\n\t\t\t\t" . '});' . "\r\n\t\t\t" . '});' . "\r\n\t\t" . '});' . "\r\n\t" . '});' . "\r\n" . '</script> ' . "\r\n";
            define('TPL_INIT_DATERANGE_DATE', true);
        }
        if (!(empty($time)) && !(defined('TPL_INIT_DATERANGE_TIME'))) {
            $s = "\r\n" . '<script type="text/javascript">' . "\r\n\t" . 'require(["daterangepicker"], function($){' . "\r\n\t\t" . '$(function(){' . "\r\n\t\t\t" . '$(".daterange.daterange-time").each(function(){' . "\r\n" . '               ' . "\r\n\t\t\t\t" . 'var elm = this;' . "\r\n" . '                                       var container =$(elm).parent().prev(); ' . "\r\n\t\t\t\t" . '$(this).daterangepicker({' . "\r\n\t\t\t\t\r\n\t\t\t\t\t" . 'format: "YYYY-MM-DD HH:mm",' . "\r\n\t\t\t\t\t" . 'timePicker: true,' . "\r\n\t\t\t\t\t" . 'timePicker12Hour : false,' . "\r\n\t\t\t\t\t" . 'timePickerIncrement: 1,' . "\r\n\t\t\t\t\t" . 'minuteStep: 1' . "\r\n\t\t\t\t" . '}, function(start, end){' . "\r\n\t\t\t\t\t" . '$(elm).find(".date-title").html(start.toDateTimeStr() + " 至 " + end.toDateTimeStr());' . "\r\n\t\t\t\t\t" . 'container.find(":input:first").val(start.toDateTimeStr());' . "\r\n\t\t\t\t\t" . 'container.find(":input:last").val(end.toDateTimeStr());' . "\r\n\t\t\t\t" . '});' . "\r\n\t\t\t" . '});' . "\r\n\t\t" . '});' . "\r\n\t" . '});' . "\r\n" . '     function clearTime(obj){' . "\r\n" . '         ' . "\r\n" . '              $(obj).prev().html("<span class=date-title>" + $(obj).attr("placeholder") + "</span>");' . "\r\n" . '              $(obj).parent().prev().find("input").val("");' . "\r\n" . '    }' . "\r\n" . '</script>' . "\r\n";
            define('TPL_INIT_DATERANGE_TIME', true);
        }
        $str = $placeholder;
        $small = ((isset($value['sm']) ? $value['sm'] : true));
        $value['starttime'] = ((isset($value['starttime']) ? $value['starttime'] : (($_GPC[$name]['start'] ? $_GPC[$name]['start'] : ''))));
        $value['endtime'] = ((isset($value['endtime']) ? $value['endtime'] : (($_GPC[$name]['end'] ? $_GPC[$name]['end'] : ''))));
        if ($value['starttime'] && $value['endtime']) {
            if (empty($time)) {
                $str = date('Y-m-d', strtotime($value['starttime'])) . '至 ' . date('Y-m-d', strtotime($value['endtime']));
            } else {
                $str = date('Y-m-d H:i', strtotime($value['starttime'])) . ' 至 ' . date('Y-m-d  H:i', strtotime($value['endtime']));
            }
        }
        $s .= '<div style="float:left">' . "\r\n\t" . '<input name="' . $name . '[start]' . '" type="hidden" value="' . $value['starttime'] . '" />' . "\r\n\t" . '<input name="' . $name . '[end]' . '" type="hidden" value="' . $value['endtime'] . '" />' . "\r\n" . '           </div>' . "\r\n" . '          <div class="btn-group ' . (($small ? 'btn-group-sm' : '')) . '" style="padding-right:0;"  >' . "\r\n" . '          ' . "\r\n\t" . '<button style="width:240px" class="btn btn-default daterange ' . ((!(empty($time)) ? 'daterange-time' : 'daterange-date')) . '"  type="button"><span class="date-title">' . $str . '</span></button>' . "\r\n" . '        <button class="btn btn-default ' . (($small ? 'btn-sm' : '')) . '" " type="button" onclick="clearTime(this)" placeholder="' . $placeholder . '"><i class="fa fa-remove"></i></button>' . "\r\n" . '         </div>' . "\r\n\t";
        return $s;
    }
}
if (!(function_exists('mobileUrl'))) {
    function mobileUrl($do = '', $query = NULL, $full = false)
    {
        global $_W;
        global $_GPC;
        !($query) && ($query = array());
        $dos = explode('/', trim($do));
        $routes = array();
        $routes[] = $dos[0];
        if (isset($dos[1])) {
            $routes[] = $dos[1];
        }
        if (isset($dos[2])) {
            $routes[] = $dos[2];
        }
        if (isset($dos[3])) {
            $routes[] = $dos[3];
        }
        $r = implode('.', $routes);
        if (!(empty($r))) {
            $query = array_merge(array('r' => $r), $query);
        }
        $query = array_merge(array('do' => 'mobile'), $query);
        $query = array_merge(array('m' => 'ching_leeing'), $query);
        if (empty($query['mid'])) {
            $mid = intval($_GPC['mid']);
            if (!(empty($mid))) {
                $query['mid'] = $mid;
            }
            if (!(empty($_W['openid'])) && !(is_weixin()) && !(is_h5app())) {
                $myid = m('member')->getMid();
                if (!(empty($myid))) {
                    $member = pdo_fetch('select id,isagent,status from' . tablename('ching_leeing_member') . 'where id=' . $myid);
                    if (!(empty($member['isagent'])) && !(empty($member['status']))) {
                        $query['mid'] = $member['id'];
                    }
                }
            }
        }
        if (empty($query['merchid'])) {
            $merchid = intval($_GPC['merchid']);
            if (!(empty($merchid))) {
                $query['merchid'] = $merchid;
            }
        } else if ($query['merchid'] < 0) {
            unset($query['merchid']);
        }
        if ($full) {
            return $_W['siteroot'] . 'app/' . substr(murl('entry', $query, true), 2);
        }
        return murl('entry', $query, true);
    }
}
if (!(function_exists('webUrl'))) {
    function webUrl($do = '', $query = array(), $full = true)
    {
        global $_W;
        global $_GPC;
        if (!(empty($_W['plugin']))) {
            if ($_W['plugin'] == 'merch') {
                if (function_exists('merchUrl')) {
                    return merchUrl($do, $query, $full);
                }
            }
        }
        $dos = explode('/', trim($do));
        $routes = array();
        $routes[] = $dos[0];
        if (isset($dos[1])) {
            $routes[] = $dos[1];
        }
        if (isset($dos[2])) {
            $routes[] = $dos[2];
        }
        if (isset($dos[3])) {
            $routes[] = $dos[3];
        }
        $r = implode('.', $routes);
        if (!(empty($r))) {
            $query = array_merge(array('r' => $r), $query);
        }
        $query = array_merge(array('do' => 'web'), $query);
        $query = array_merge(array('m' => 'ching_leeing'), $query);
        if ($full) {
            return $_W['siteroot'] . 'web/' . substr(wurl('site/entry', $query), 2);
        }
        return wurl('site/entry', $query);
    }
}
if (!(function_exists('dump'))) {
    function dump()
    {
        $args = func_get_args();
        foreach ($args as $val) {
            echo '<pre style="color: red">';
            echo '</pre>';
        }
    }
}
if (!(function_exists('my_scandir'))) {
    function my_scandir($dir)
    {
        global $my_scenfiles;
        if ($handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false) {
                if (($file != '..') && ($file != '.')) {
                    if (is_dir($dir . '/' . $file)) {
                        my_scandir($dir . '/' . $file);
                    } else {
                        $my_scenfiles[] = $dir . '/' . $file;
                    }
                }
            }
            closedir($handle);
        }
    }

    $my_scenfiles = array();
}
if (!(function_exists('cut_str'))) {
    function cut_str($string, $sublen, $start = 0, $code = 'UTF-8')
    {
        if ($code == 'UTF-8') {
            $pa = '/[' . "\x1" . '-]|[' . "\xc2" . '-' . "\xdf" . '][' . "\x80" . '-' . "\xbf" . ']|' . "\xe0" . '[' . "\xa0" . '-' . "\xbf" . '][' . "\x80" . '-' . "\xbf" . ']|[' . "\xe1" . '-' . "\xef" . '][' . "\x80" . '-' . "\xbf" . '][' . "\x80" . '-' . "\xbf" . ']|' . "\xf0" . '[' . "\x90" . '-' . "\xbf" . '][' . "\x80" . '-' . "\xbf" . '][' . "\x80" . '-' . "\xbf" . ']|[' . "\xf1" . '-' . "\xf7" . '][' . "\x80" . '-' . "\xbf" . '][' . "\x80" . '-' . "\xbf" . '][' . "\x80" . '-' . "\xbf" . ']/';
            preg_match_all($pa, $string, $t_string);
            if ($sublen < (count($t_string[0]) - $start)) {
                return join('', array_slice($t_string[0], $start, $sublen));
            }
            return join('', array_slice($t_string[0], $start, $sublen));
        }
        $start = $start * 2;
        $sublen = $sublen * 2;
        $strlen = strlen($string);
        $tmpstr = '';
        $i = 0;
        while ($i < $strlen) {
            if (($start <= $i) && ($i < ($start + $sublen))) {
                if (129 < ord(substr($string, $i, 1))) {
                    $tmpstr .= substr($string, $i, 2);
                } else {
                    $tmpstr .= substr($string, $i, 1);
                }
            }
            if (129 < ord(substr($string, $i, 1))) {
                ++$i;
            }
            ++$i;
        }
        return $tmpstr;
    }
}
if (!(function_exists('save_media'))) {
    function save_media($url, $enforceQiniu = false)
    {
        global $_W;
        static $com;
        if (!($com)) {
            $com = com('qiniu');
        }
        if ($com) {
            $qiniu_url = $com->save($url, NULL, $enforceQiniu);
            if (!(empty($qiniu_url))) {
                return $qiniu_url;
            }
        }
        $ext = strrchr($url, '.');
        if (($ext != '.jpeg') && ($ext != '.gif') && ($ext != '.jpg') && ($ext != '.png')) {
            return $url;
        }
        if (!(empty($_W['setting']['remote']['type'])) && !(empty($url)) && !(strexists($url, 'http:') || strexists($url, 'https:'))) {
            if (is_file(ATTACHMENT_ROOT . $url)) {
                load()->func('file');
                $remotestatus = file_remote_upload($url, false);
                if (!(is_error($remotestatus))) {
                    $remoteurl = $_W['attachurl_remote'] . $url;
                    return $remoteurl;
                }
            }
        }
        return $url;
    }
}
if (!(function_exists('tpl_form_field_category_3level'))) {
    function tpl_form_field_category_3level($name, $parents, $children, $parentid, $childid, $thirdid)
    {
        $html = "\r\n" . '<script type="text/javascript">' . "\r\n\t" . 'window._' . $name . ' = ' . json_encode($children) . ';' . "\r\n" . '</script>';
        if (!(defined('TPL_INIT_CATEGORY_THIRD'))) {
            $html .= "\t\r\n" . '<script type="text/javascript">' . "\r\n\t" . '  function renderCategoryThird(obj, name){' . "\r\n\t\t" . 'var index = obj.options[obj.selectedIndex].value;' . "\r\n\t\t" . 'require([\'jquery\', \'util\'], function($, u){' . "\r\n\t\t\t" . '$selectChild = $(\'#\'+name+\'_child\');' . "\r\n" . '                                                      $selectThird = $(\'#\'+name+\'_third\');' . "\r\n\t\t\t" . 'var html = \'<option value="0">请选择二级分类</option>\';' . "\r\n" . '                                                      var html1 = \'<option value="0">请选择三级分类</option>\';' . "\r\n\t\t\t" . 'if (!window[\'_\'+name] || !window[\'_\'+name][index]) {' . "\r\n\t\t\t\t" . '$selectChild.html(html); ' . "\r\n" . '                                                                        $selectThird.html(html1);' . "\r\n\t\t\t\t" . 'return false;' . "\r\n\t\t\t" . '}' . "\r\n\t\t\t" . 'for(var i=0; i< window[\'_\'+name][index].length; i++){' . "\r\n\t\t\t\t" . 'html += \'<option value="\'+window[\'_\'+name][index][i][\'id\']+\'">\'+window[\'_\'+name][index][i][\'name\']+\'</option>\';' . "\r\n\t\t\t" . '}' . "\r\n\t\t\t" . '$selectChild.html(html);' . "\r\n" . '                                                    $selectThird.html(html1);' . "\r\n\t\t" . '});' . "\r\n\t" . '}' . "\r\n" . '        function renderCategoryThird1(obj, name){' . "\r\n\t\t" . 'var index = obj.options[obj.selectedIndex].value;' . "\r\n\t\t" . 'require([\'jquery\', \'util\'], function($, u){' . "\r\n\t\t\t" . '$selectChild = $(\'#\'+name+\'_third\');' . "\r\n\t\t\t" . 'var html = \'<option value="0">请选择三级分类</option>\';' . "\r\n\t\t\t" . 'if (!window[\'_\'+name] || !window[\'_\'+name][index]) {' . "\r\n\t\t\t\t" . '$selectChild.html(html);' . "\r\n\t\t\t\t" . 'return false;' . "\r\n\t\t\t" . '}' . "\r\n\t\t\t" . 'for(var i=0; i< window[\'_\'+name][index].length; i++){' . "\r\n\t\t\t\t" . 'html += \'<option value="\'+window[\'_\'+name][index][i][\'id\']+\'">\'+window[\'_\'+name][index][i][\'name\']+\'</option>\';' . "\r\n\t\t\t" . '}' . "\r\n\t\t\t" . '$selectChild.html(html);' . "\r\n\t\t" . '});' . "\r\n\t" . '}' . "\r\n" . '</script>' . "\r\n\t\t\t";
            define('TPL_INIT_CATEGORY_THIRD', true);
        }
        $html .= '<div class="row row-fix tpl-category-container">' . "\r\n\t" . '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">' . "\r\n\t\t" . '<select class="form-control tpl-category-parent" id="' . $name . '_parent" name="' . $name . '[parentid]" onchange="renderCategoryThird(this,\'' . $name . '\')">' . "\r\n\t\t\t" . '<option value="0">请选择一级分类</option>';
        $ops = '';
        foreach ($parents as $row) {
            $html .= "\r\n\t\t\t" . '<option value="' . $row['id'] . '" ' . (($row['id'] == $parentid ? 'selected="selected"' : '')) . '>' . $row['name'] . '</option>';
        }
        $html .= "\r\n\t\t" . '</select>' . "\r\n\t" . '</div>' . "\r\n\t" . '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">' . "\r\n\t\t" . '<select class="form-control tpl-category-child" id="' . $name . '_child" name="' . $name . '[childid]" onchange="renderCategoryThird1(this,\'' . $name . '\')">' . "\r\n\t\t\t" . '<option value="0">请选择二级分类</option>';
        if (!(empty($parentid)) && !(empty($children[$parentid]))) {
            foreach ($children[$parentid] as $row) {
                $html .= "\r\n\t\t\t" . '<option value="' . $row['id'] . '"' . (($row['id'] == $childid ? 'selected="selected"' : '')) . '>' . $row['name'] . '</option>';
            }
        }
        $html .= "\r\n\t\t" . '</select> ' . "\r\n\t" . '</div> ' . "\r\n" . '                  <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">' . "\r\n\t\t" . '<select class="form-control tpl-category-child" id="' . $name . '_third" name="' . $name . '[thirdid]">' . "\r\n\t\t\t" . '<option value="0">请选择三级分类</option>';
        if (!(empty($childid)) && !(empty($children[$childid]))) {
            foreach ($children[$childid] as $row) {
                $html .= "\r\n\t\t\t" . '<option value="' . $row['id'] . '"' . (($row['id'] == $thirdid ? 'selected="selected"' : '')) . '>' . $row['name'] . '</option>';
            }
        }
        $html .= '</select>' . "\r\n\t" . '</div>' . "\r\n" . '</div>';
        return $html;
    }
}
if (!(function_exists('array_column'))) {
    function array_column($input, $column_key, $index_key = NULL)
    {
        $arr = array();
        foreach ($input as $d) {
            if (!(isset($d[$column_key]))) {
                return;
            }
            if ($index_key !== NULL) {
                return array($d[$index_key] => $d[$column_key]);
            }
            $arr[] = $d[$column_key];
        }
        if ($index_key !== NULL) {
            $tmp = array();
            foreach ($arr as $ar) {
                $tmp[key($ar)] = current($ar);
            }
            $arr = $tmp;
        }
        return $arr;
    }
}
if (!(function_exists('is_utf8'))) {
    function is_utf8($str)
    {
        return preg_match('%^(?:' . "\r\n" . '            [\\x09\\x0A\\x0D\\x20-\\x7E]              # ASCII' . "\r\n" . '            | [\\xC2-\\xDF][\\x80-\\xBF]             # non-overlong 2-byte' . "\r\n" . '            | \\xE0[\\xA0-\\xBF][\\x80-\\xBF]         # excluding overlongs' . "\r\n" . '            | [\\xE1-\\xEC\\xEE\\xEF][\\x80-\\xBF]{2}  # straight 3-byte' . "\r\n" . '            | \\xED[\\x80-\\x9F][\\x80-\\xBF]         # excluding surrogates' . "\r\n" . '            | \\xF0[\\x90-\\xBF][\\x80-\\xBF]{2}      # planes 1-3' . "\r\n" . '            | [\\xF1-\\xF3][\\x80-\\xBF]{3}          # planes 4-15' . "\r\n" . '            | \\xF4[\\x80-\\x8F][\\x80-\\xBF]{2}      # plane 16' . "\r\n" . '            )*$%xs', $str);
    }
}
if (!(function_exists('price_format'))) {
    function price_format($price)
    {
        $prices = explode('.', $price);
        if (intval($prices[1]) <= 0) {
            $price = $prices[0];
        } else if (isset($prices[1][1]) && ($prices[1][1] <= 0)) {
            $price = $prices[0] . '.' . $prices[1][0];
        }
        return $price;
    }
}
if (!(function_exists('redis'))) {
    function redis()
    {
        global $_W;
        static $redis;
        if (is_null($redis)) {
            if (!(extension_loaded('redis'))) {
                return error(-1, 'PHP 未安装 redis 扩展');
            }
            if (!(isset($_W['config']['setting']['redis']))) {
                return error(-1, '未配置 redis, 请检查 data/config.php 中参数设置');
            }
            $config = $_W['config']['setting']['redis'];
            if (empty($config['server'])) {
                $config['server'] = '127.0.0.1';
            }
            if (empty($config['port'])) {
                $config['port'] = '6379';
            }
            $redis_temp = new Redis();
            if ($config['pconnect']) {
                $connect = $redis_temp->pconnect($config['server'], $config['port'], $config['timeout']);
            } else {
                $connect = $redis_temp->connect($config['server'], $config['port'], $config['timeout']);
            }
            if (!($connect)) {
                return error(-1, 'redis 连接失败, 请检查 data/config.php 中参数设置');
            }
            if (!(empty($config['requirepass']))) {
                $redis_temp->auth($config['requirepass']);
            }
            $ping = $redis_temp->ping();
            if ($ping != '+PONG') {
                return error(-1, 'redis 无法正常工作，请检查 redis 服务');
            }
            $redis = $redis_temp;
        }
        return $redis;
    }
}
?>