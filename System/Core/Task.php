<?php
namespace PHPTask\System\Core;

/**
* Task Class
*/
class Task
{
    /**
     * Task ID
     * @var
     */
	protected $task_id;

    /**
     * Task Stacked Generator
     * @var void
     */
	protected $gen;

    /**
     * Task Send Value
     * @var
     */
	protected $send_value;


    /**
     * First Flag
     * @var bool
     */
	protected $before_first = true;


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
     * Set Send Value
     * @param $send
     */
	public function setSendValue($send){
		$this->send_value = $send;
	}

    /**
     * Get ID
     * @return mixed
     */
	public function getId(){
		return $this->task_id;
	}

    /**
     * Check finish status
     * @return bool
     */
	public function isFinished(){
		return !$this->gen->valid();
	}

    /**
     * Run the task
     * @return mixed
     */
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
     * return Stacked Generator
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