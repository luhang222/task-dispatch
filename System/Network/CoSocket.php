<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/10/20
 * Time: 16:23
 */

namespace PHPTask\System\NetWork;

use PHPTask\System\Core\CoroutineReturnValue;
use PHPTask\System\Core\SystemCall;
/**
 * Socket封装
 * Class CoSocket
 * @package classes
 */
class CoSocket {

    protected $socket;

    public function __construct($socket) {
        if(!is_resource($socket)) throw new \Exception("invalid socket");
        $this->socket = $socket;
    }

    public function accept() {
        yield SystemCall::WaitForRead($this->socket);
        yield CoroutineReturnValue::Retval(new self(stream_socket_accept($this->socket, 0)));
    }

    public function read($size) {
        yield SystemCall::WaitForRead($this->socket);
        yield CoroutineReturnValue::Retval(fread($this->socket, $size));
    }

    public function write($string) {
        yield SystemCall::WaitForWrite($this->socket);
        fwrite($this->socket, $string);
    }

    public function getName(){
        return stream_socket_get_name($this->socket,TRUE);
    }

    public function checkEof(){
        return feof($this->socket);
    }

    public function checkResource(){
        return is_resource($this->socket);
    }

    public function close() {
        @fclose($this->socket);
    }
}