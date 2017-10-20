<?php
namespace classes;

/**
 * Socket任务调度器
 */
class SocketScheduler extends Scheduler
{
    /**
     * 等待读写的socket
     * @var array
     */
    protected $waitForRead;

    protected $waitForWrite;

    /**
     * Scheduler constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->waitForRead = $this->waitForWrite = [];
        //初始加入内置的循环检测任务
        $this->newTask($this->ioTask());
    }

    public function waitForRead($socket, $task){
        if(isset($this->waitForRead[(int)$socket])){
            $this->waitForRead[(int)$socket][1][] = $task;
        }else{
            $this->waitForRead[(int)$socket] = [$socket,[$task]];
        }
    }

    public function waitForWrite($socket, $task){
        if(isset($this->waitForWrite[(int)$socket])){
            $this->waitForWrite[(int)$socket][1][] = $task;
        }else{
            $this->waitForWrite[(int)$socket] = [$socket,[$task]];
        }
    }

    /**
     *  检测准备好的socket，并将其中的任务放入任务队列
     */
    public function ioPoll($timeout = 0){
        $rsock = [];
        $wsock = [];
        $esock = [];

        foreach($this->waitForRead as list($socket)){
            $rsock[] = $socket;
        }
        foreach($this->waitForWrite as list($socket)){
            $wsock[] = $socket;
        }
        if(!@stream_select($rsock,$wsock,$esock,$timeout)){
            return; //没有准备好的socket
        }

        foreach ($rsock as $socket) {
            foreach ($this->waitForRead[(int)$socket][1] as $task) {
                $this->schedule($task);
            }
            unset($this->waitForRead[(int) $socket]);
        }

        foreach ($wsock as $socket) {
            foreach ($this->waitForWrite[(int)$socket][1] as $task) {
                $this->schedule($task);

            }
            unset($this->waitForWrite[(int) $socket]);
        }
    }

    /**
     *  内置循环检测任务
     */
    public function ioTask(){
        while (true){
            if($this->task_queue->isEmpty()){
                //任务队列是空的时候等待，节省CPU资源
                $this->ioPoll(null);
            }else{
                //任务队列不是空的时候立即返回
                $this->ioPoll(0);
            }
            //中断等待下一次调用
            yield;
        }

    }

}

?>