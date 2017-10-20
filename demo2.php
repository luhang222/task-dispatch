<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/10/19
 * Time: 10:48
 */

$server = stream_socket_server("tcp://localhost:8001",$errno,$errstr);
//$content = file_get_contents("presell.png");
//$content = str_pad("end", 10000, "hahaha ", STR_PAD_LEFT);
//$content = "jhahaha";
if(!$server){
    exit($errno . ":" . $errstr);
}
echo "server start at 8001......";
while (true){
    $socket = stream_socket_accept($server,819200);
    $data = fread($socket, 65535);
    $msg = "Received following request:\n\n$data";
    $msgLength = strlen($msg);
    $response = <<<RES
HTTP/1.1 200 OK\r
Content-Type: plain/text\r
Content-Length: 1000000\r
Connection: keep-alive\r
\r
$msg
RES;
    $size = fwrite($socket, $response,strlen($response));
    //断开连接
    fclose($socket);
}