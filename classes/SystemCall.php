<?php
namespace classes;

/**
* 系统调用类
*/
class SystemCall
{
	protected $callback;

    public function __construct(callable $callback) {
        $this->callback = $callback;
    }

    public function __invoke(Task $task, Scheduler $scheduler) {
        $callback = $this->callback;
        return $callback($task, $scheduler);
    }

    /**
     * 使用系统调用创建一个新的任务，并将其入队
     * @param \Generator $gen
     */
    public static function NewTask(\Generator $gen){
        return new self(function(Task $task,Scheduler $scheduler) use ($gen){
            $scheduler->newTask($gen);
            $scheduler->schedule($task);
        });
    }

    /**
     *  将任务放入等待读取/写入队列
     *  此处仅为scheduler中方法代理
     */
    public static function WaitForRead($socket){
        return new self(function(Task $task,SocketScheduler $scheduler) use ($socket) {
            $scheduler->waitForRead($socket,$task);
        });
    }

    public static function WaitForWrite($socket){
        return new SystemCall(function(Task $task,SocketScheduler $scheduler) use ($socket) {
            $scheduler->waitForWrite($socket,$task);
        });
    }
}
?>