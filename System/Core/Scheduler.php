<?php
namespace PHPTask\System\Core;

/**
* 任务调度器
*/
class Scheduler
{
    /**
     * 最大任务ID
     * @var int
     */
	public $max_task_id;

    /**
     * 任务列表
     * @var array
     */
	public $task_map = [];

    /**
     * 进行中任务队列
     * @var \SplQueue
     */
	public $task_queue;

    /**
     * Scheduler constructor.
     */
	function __construct()
	{
	    $this->max_task_id = 0;
        $this->task_queue = new \SplQueue();
	}

    /**
     * 添加任务
     * @param $gen
     * @return int
     */
	public function newTask($gen){
		$task_id = ++$this->max_task_id;
		$task = new Task($task_id,$gen);
		$this->task_map[$task_id] = $task;
		$this->schedule($task);
		return $task_id;

	}

    /**
     * 移除任务
     * @param $id
     * @return bool
     */
	public function killTask($id){
		if(!isset($this->task_map[$id])){
			throw new \Exception("task not exist");
		}
		unset($this->task_map[$id]);
		foreach ($this->task_queue as $i => $task) {
	        if ($task->getId() === $id) {
	            unset($this->task_queue[$i]);
	            break;
	        }
	    }
	    return true;
	}

    /**
     * 任务入队
     * @param $task
     */
	public function schedule(Task $task){
        if(!$task->isFinished() && isset($this->task_map[$task->getId()])) {
            $this->task_queue->enqueue($task);
            return true;
        }
        return false;
	}

    /**
     *  运行
     */
	public function run(){
		while (!$this->task_queue->isEmpty()) {
			$task = $this->task_queue->dequeue();
			$ret = $task->run();
			if($ret instanceof SystemCall){
				$ret($task,$this);
				continue;
			}
			if($task->isFinished()){
				unset($this->task_map[$task->getId()]);
			}else{
				$this->schedule($task);
			}
		}
	}
}

?>