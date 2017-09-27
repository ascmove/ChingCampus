<?php
/**
 * author: Ching
 * createTime: 2016/12/9 0009 下午 4:17
 */
namespace Ching;

class SocketChat
{
    private $port = 2000;  //监听端口
    private static $connectPool = [];  //连接池
    private static $maxConnectNum = 10000; //最大连接数
    private static $onlineUser = [];  //在线用户
    private static $connectPairs = [];  //在线用户


    public function __construct( $port = 0 )
    {
        !empty( $port ) && $this->port = $port;
        $this->startServer();
    }

    //开始服务器
    public function startServer()
    {
        $this->master = socket_create_listen( $this->port );
        if( !$this->master ) throw new \ErrorException("listen {$this->port} fail !");
        $this->runLog("Web-Chat Server Started : ".date('Y-m-d H:i:s'));
        $this->runLog("Listening on   : 127.0.0.1 port " . $this->port);
        self::$connectPool[] = $this->master;
        while( true ){
            $readFds = self::$connectPool;
            //阻塞接收客户端链接
            @socket_select( $readFds, $writeFds, $e = null, $this->timeout );

            foreach( $readFds as $socket ){
                //当前链接 是主进程
                if( $this->master == $socket ){

                    $client = socket_accept( $this->master );  //接收新的链接
                    $this->handShake = False;

                    if ($client < 0){
                        $this->log('clinet connect false!');
                        continue;
                    } else{
                        //超过最大连接数
                        if( count( self::$connectPool ) > self::$maxConnectNum )
                            continue;

                        //加入连接池
                        $this->connect( $client );
                    }

                }else{
                    //不是主进程,开始接收数据
                    $bytes = @socket_recv($socket, $buffer, 2048, 0);
                    //未读取到数据
                    if( $bytes <= 16 ){
                        $this->disConnect( $socket );
                    }else{
                        //未握手 先握手
                        if( !$this->handShake ){
                            $this->doHandShake( $socket, $buffer );
                        }else{
                            $buffer = $this->decode( $buffer );
                            $buffer = json_decode($buffer,true);
                            $buffer['ticket'] = CHING_LEEING_WS_TICKET;
                            $dat = ihttp_request( CHING_LEEING_WS_API , $buffer );
                            $data = json_decode($dat['content'],true);
                            if($data['code'] == 500){
                                $this->send( $socket , array('type'=>1) );
                                continue;
                            }
                            if($data['code'] == 404){
                                $this->disConnect( $socket );
                                continue;
                            }

                            $this->bind( $socket, $buffer['openid'] );
                            $online = $this->getOnlineUser( $buffer['toOpenid'] , $socket );
                            if(!empty($online)){
                                $socket0 = (int) $socket;
                                $socket1 = (int) $online['socket'];
                                foreach ( self::$connectPairs as $key => $Pairs ){
                                    if($Pairs['openid'] == $buffer['toOpenid']){
                                        unset(self::$connectPairs[$key]);
                                    }
                                    if($Pairs['openid'] == $buffer['openid']){
                                        unset(self::$connectPairs[$key]);
                                    }
                                }
                                self::$connectPairs[$socket0] = array('socket'=>$socket1,'openid'=>$buffer['toOpenid']);
                                self::$connectPairs[$socket1] = array('socket'=>$socket,'openid'=>$buffer['openid']);
                                $this->send( $socket , array('type'=>2,'openid'=>$buffer['toOpenid'] ) );
                                $this->send( $socket , array('type'=>2,'openid'=>$buffer['openid'] ) );
                                $this->send( $online['socket'] , array('type'=>2,'openid'=>$buffer['openid'] ) );
                                $this->send( $online['socket'] , array('type'=>2,'openid'=>$buffer['toOpenid'] ) );
                            }else{
                                $this->send( $socket , array('type'=>2,'openid'=>$buffer['openid'] ) );
                                $this->send( $socket , array('type'=>3,'openid'=>$buffer['toOpenid'] ) );
                            }

                            if($buffer['type'] == 4){
                                $ret = $this->send( $online['socket'] , array('type'=>4,'content'=>$buffer['content'],'toOpenid'=>$buffer['toOpenid'] ) );
                                if($ret){
                                    $buffer['pdo_insertid'] = $data['pdo_insertid'];
                                    $buffer['label'] = 1;
                                    $buffer['ticket'] = CHING_LEEING_WS_TICKET;
                                    ihttp_request( CHING_LEEING_WS_API , $buffer );
                                }else{
                                    $buffer['label'] = 2;
                                    $buffer['ticket'] = CHING_LEEING_WS_TICKET;
                                    $dat = ihttp_request( CHING_LEEING_WS_API , $buffer );
                                    $data = json_decode($dat['content'],true);
                                    $this->send( $socket , array('type'=>0,'openid'=>$buffer['openid'],'content'=>$data['pushToWeixinContent'] ) );
                                }
                            }
                        }
                    }

                }
            }

        }
    }

    //用户--链接 绑定
    public function bind( $socket, $openid )
    {
        foreach ( self::$onlineUser as $key => $user ){
            if($user['openid'] == $openid){
                self::$onlineUser[$key]['socket'] = $socket;
                return;
            }
        }
        if(!empty($openid)){
            self::$onlineUser[(int) $socket] = array(
                'openid' => $openid,
                'socket'=>$socket
            );
        }
    }

    //用户--链接 解绑
    public function unBind( $socket )
    {
        unset( self::$onlineUser[(int) $socket] );
    }

    //获取在线用户
    public function getOnlineUser( $openid , $socket )
    {
        //print_r(self::$onlineUser);
        foreach( self::$onlineUser as $user ){
            if( ($user['openid'] == $openid)&&($user['socket'] != (int) $socket)  ){
                return array(
                    'socket'=>$user['socket'],
                    'openid'=>$user['openid']
                );
            }
        }
    }

    //处理发送信息
   public function send( $client, $msg )
    {
        $msg = $this->frame( json_encode( $msg ) );
        $ret = socket_write( $client, $msg, strlen($msg) );
        return $ret;
    }

    //握手协议
    function doHandShake($socket, $buffer)
    {
        list($resource, $host, $origin, $key) = $this->getHeaders($buffer);
        $upgrade  = "HTTP/1.1 101 Switching Protocol\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: " . $this->calcKey($key) . "\r\n\r\n";  //必须以两个回车结尾

        socket_write($socket, $upgrade, strlen($upgrade));
        $this->handShake = true;
        return true;
    }

    //获取请求头
    function getHeaders( $req )
    {
        $r = $h = $o = $key = null;
        if (preg_match("/GET (.*) HTTP/"              , $req, $match)) { $r = $match[1]; }
        if (preg_match("/Host: (.*)\r\n/"             , $req, $match)) { $h = $match[1]; }
        if (preg_match("/Origin: (.*)\r\n/"           , $req, $match)) { $o = $match[1]; }
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $req, $match)) { $key = $match[1]; }
        return [$r, $h, $o, $key];
    }

    //验证socket
    function calcKey( $key )
    {
        //基于websocket version 13
        $accept = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        return $accept;
    }


    //打包函数 返回帧处理
    public function frame( $buffer )
    {
        $len = strlen($buffer);
        if ($len <= 125) {

            return "\x81" . chr($len) . $buffer;
        } else if ($len <= 65535) {

            return "\x81" . chr(126) . pack("n", $len) . $buffer;
        } else {

            return "\x81" . char(127) . pack("xxxxN", $len) . $buffer;
        }
    }

    //解码 解析数据帧
    function decode( $buffer )
    {
        $len = $masks = $data = $decoded = null;
        $len = ord($buffer[1]) & 127;

        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        }
        else if ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        }
        else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        return $decoded;
    }

    //客户端链接处理函数
    function connect( $socket )
    {
        array_push( self::$connectPool, $socket );
    }

    //客户端断开链接函数
    function disConnect( $socket )
    {
        $index = array_search( $socket, self::$connectPool );
        socket_close( $socket );
        $this->unBind( $socket );
        if ($index >= 0){
            array_splice( self::$connectPool, $index, 1 );
        }
        $to_openid = self::$connectPairs[(int) $socket]['openid'];
        $to_socket = self::$connectPairs[(int) $socket]['socket'];
        $openid = self::$connectPairs[(int) $to_socket]['openid'];
        unset(self::$connectPairs[(int) $socket]);
        unset(self::$connectPairs[(int) $to_socket]);
        $this->send( $to_socket , array('type'=>3,'openid'=>$openid) );
        $this->unBind( $socket );
    }

    //打印运行信息
    public function runLog( $mess = '' )
    {
        echo $mess . PHP_EOL;
    }
}
