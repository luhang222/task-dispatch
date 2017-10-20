<?php
namespace classes;

/**
* 任务类
*/
class Task
{
	protected $task_id; //ID

	public $gen; //生成器

	protected $send_value; //接受值

	protected $before_first = true; //第一次进入

    /**
     * Task constructor.
     * @param $id
     * @param \Generator $gen
     */
	public function __construct($id,\Generator $gen)
	{
		$this->task_id = $id;
		$this->gen = $this->stackedCoroutine($gen);
	}

    /**
     * 设置发送值
     * @param $send
     */
	public function setSendValue($send){
		$this->send_value = $send;
	}

    /**
     * 获得ID
     * @return mixed
     */
	public function getId(){
		return $this->task_id;
	}

	public function isFinished(){
		return !$this->gen->valid();
	}

	public function run(){
		if($this->before_first){
			$this->before_first = false;
			return $this->gen->current();
		}else{
			$ret = $this->gen->send($this->send_value);
			$this->send_value = null;
			return $ret;
		}
	}

    /**
     * 协程堆栈
     * @param \Generator $gen
     */
    protected function stackedCoroutine(\Generator $gen) {
        $stack = new \SplStack;
        while(true) {
            $value = $gen->current();
            if ($value instanceof \Generator) {
                $stack->push($gen);
                $gen = $value;
                continue;
            }
            $isReturnValue = $value instanceof CoroutineReturnValue;
            if (!$gen->valid() || $isReturnValue) {
                if ($stack->isEmpty()) {
                    return;
                }
                $gen = $stack->pop();
                $gen->send($isReturnValue ? $value->getValue() : NULL);
                continue;
            }
            $gen->send(yield $gen->key() => $value);
        }
    }
}
?>