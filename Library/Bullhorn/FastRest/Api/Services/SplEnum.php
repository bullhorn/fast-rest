<?php
namespace Bullhorn\FastRest\Api\Services;
//Fix for default SplEnum not being available in windows
abstract class SplEnum implements \JsonSerializable {
    /** @var  string */
    private $value;

    final public function __construct($value) {
        $c = new \ReflectionClass($this);
        if(!in_array($value, $c->getConstants())) {
            throw new \InvalidArgumentException('Invalid Value: '.$value);
        }
        $this->value = $value;
    }

    final public function __toString() {
        return $this->value;
    }

    public function jsonSerialize() {
        return $this->__toString();
    }


}