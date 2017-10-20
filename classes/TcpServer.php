<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/10/20
 * Time: 14:33
 */

namespace classes;

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
            $this->clients[] = $client;
            yield SystemCall::NewTask($this->handleClient($client));
        }
    }

    protected function handleClient(CoSocket $client){
        while(1){
            $content = (yield $client->read(65535));
            $msg = "Received following request From {$client->getName()}:\r\n$content";
            echo $msg . "\n";

            $send = $client->getName() . " said : " . $content . "\r\n";

            foreach ($this->clients as $c) {
                yield $c->write($send);
            }
        }
    }

    public function run(){
        echo "server at $this->port starting ...\n";
        yield $this->accept();
    }

}