<?php
/**
 * Created by PhpStorm.
 * User: luhang
 * Date: 2017/10/19
 * Time: 16:35
 */

define("ACCEPT_TIMEOUT",65535);
define("APPLICATION_PORT",52535);
define("READ_SIZE",8192);
/**
 *  只在CLI模式运行
 */
if(strpos("cli",php_sapi_name()) === FALSE) exit;

$sockets = [];
$ssread = [];
$read = [];
$write = [];
$e = [];

$serv = stream_socket_server("tcp://localhost:".APPLICATION_PORT,$errno,$errstr);

if(!$serv) throw new Exception("$errno : $errstr");

echo "server running at " . APPLICATION_PORT . "\n";

$sockets[(int) $serv] = [$serv,'','','localhost'];
$read[] = $serv;

while(true){
    //wait until one socket status change
    $sread = $read;
    $swrite = $write;

    if(!@stream_select($sread ,$swrite, $e, null)) continue;
    foreach ($sread as $sock) {
        if($sock === $serv){
            //accept instantly
            $client = stream_socket_accept($serv, 0, $client_name);
            echo $client_name . "has connected ...\n";
            $read[] = $client;
            $sockets[(int) $client] = [$client,'','',$client_name];
        }else{
            $sockets[(int) $sock][1] .= fread($sock,READ_SIZE);
            if(strpos($sockets[(int) $sock][1],"\n") !== FALSE){
                echo $sockets[(int) $sock][3] . " said : " .$sockets[(int) $sock][1];
                foreach ($sockets as list($socket)) {
                    if($socket === $serv) continue;
                    fwrite($socket,$sockets[(int) $sock][3] . "said : " .$sockets[(int) $sock][1]);
                }
                $sockets[(int) $sock][1] = '';
            }
        }
    }

}



