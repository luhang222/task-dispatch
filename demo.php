<?php 
require_once "System/Task.php";
require_once "System/Scheduler.php";
require_once "System/SystemCall.php";
require_once "System/SocketScheduler.php";
require_once "System/CoSocket.php";
require_once "System/CoroutineReturnValue.php";
require_once "System/TcpServer.php";

use classes\SocketScheduler;
use classes\TcpServer;

$server = new TcpServer();
$scheduler = new SocketScheduler;
$scheduler->newTask($server->run());
$scheduler->run();
?>