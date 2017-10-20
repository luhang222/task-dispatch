<?php 
require_once "classes/Task.php";
require_once "classes/Scheduler.php";
require_once "classes/SystemCall.php";
require_once "classes/SocketScheduler.php";
require_once "classes/CoSocket.php";
require_once "classes/CoroutineReturnValue.php";
require_once "classes/TcpServer.php";

use classes\SocketScheduler;
use classes\TcpServer;

$server = new TcpServer();
$scheduler = new SocketScheduler;
$scheduler->newTask($server->run());
$scheduler->run();
?>