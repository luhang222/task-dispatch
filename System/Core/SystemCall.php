<?php
namespace PHPTask\System\Core;
/**
* 系统调用类
*/
class SystemCall
{
    /*
     * @var callable
     */
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


}
?>