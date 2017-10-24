<?php
namespace PHPTask\System\Core;

/**
 * Class CoroutineReturnValue
 * @package classes
 */
class CoroutineReturnValue {
    protected $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }

    public static function Retval($value){
        return new self($value);
    }
}