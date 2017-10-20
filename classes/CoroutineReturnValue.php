<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/10/20
 * Time: 16:28
 */

namespace classes;

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