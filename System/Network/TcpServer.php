<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/10/20
 * Time: 14:33
 */

namespace PHPTask\System\NetWork;

use PHPTask\System\Core\SystemCall;

/**
 * Class TcpServer
 * @package classes
 */
class TcpServer
{
    public static $Servers = [];

    public static $Max_id = 1;

    public $id;

    public $address;

    public $port;

    public $socket;

    public $clients = [];

    public $eventHandler = 'EventHandler'; //默认使用EventHandler处理事件



    protected $bytesRead = 0;
    protected $_recvBuffer = '';

    protected $_writeBuffer = '';


    public function __construct($address = 'localhost', $port = '8000')
    {
        $this->id = ++self::$Max_id;
        $this->address = $address;
        $this->port = $port;
        //创建服务端socket
        $socket = @stream_socket_server("tcp://$address:$port", $errNo, $errStr);
        if (!$socket) throw new \Exception($errStr, $errNo);
        stream_set_blocking($socket,0);
        $this->socket = new CoSocket($socket);
        self::$Servers[$this->id] = $this;
    }

    protected function accept(){
        while (true){
            $client = yield $this->socket->accept();
            echo $client->getName() . "   has connected \r\n";
            $this->clients[] = $client;
            yield SystemCall::NewTask($this->handleClient($client));
        }
    }

//    protected function handleClient(CoSocket $client){
//        while(1){
//            $content = (yield $client->read(65535));
//            $msg = "Received following request From {$client->getName()}:\r\n$content";
//            echo $msg . "\n";
//
//            $send = $client->getName() . " said : " . $content . "\r\n";
//
//            foreach ($this->clients as $c) {
//                yield $c->write($send);
//            }
//        }
//    }

    /**
     * 处理客户端连接
     * @param CoSocket $client
     */
    protected function handleClient(CoSocket $client){
        while(1){
            yield $this->read($client);
        }
    }

    protected function read(CoSocket $client, $check_eof = true){
        $buffer = yield $client->read(self::READ_BUFFER_SIZE);
        // Check connection closed.
        if ($buffer === '' || $buffer === false) {
            if ($check_eof && ($client->checkEof() || !$client->checkResource() || $buffer === false)) {
                $this->destroy();
                return;
            }
        } else {
            $this->bytesRead += strlen($buffer);
            $this->_recvBuffer .= $buffer;
        }

        if(strpos($this->_recvBuffer,"\n") !== FALSE){
            echo $this->_recvBuffer . "\n";
            yield $this->onMessage($client);
            $this->_recvBuffer = '';
        }
    }

    protected function onMessage(CoSocket $socket){
        foreach ($this->clients as $client) {
            $send = $socket->getName() . " said : " . $this->_recvBuffer . "\r\n";
            yield $client->write($send);
        }
    }


    protected function destroy(){
        echo "\ndestroy";
    }


    public function run(){
        echo "server at $this->port starting ...\n";
        yield $this->accept();
    }

}