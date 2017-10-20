<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/10/20
 * Time: 16:23
 */

namespace classes;


class CoSocket {

    protected $socket;

    public function __construct($socket) {
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

    public function close() {
        @fclose($this->socket);
    }
}