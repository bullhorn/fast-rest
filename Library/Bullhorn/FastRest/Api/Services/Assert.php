<?php
namespace Bullhorn\FastRest\Api\Services;
class Assert {
	/**
	 * isString
	 * @param mixed $value
	 * @return string
	 */
	public static function isString($value) {
		if(is_scalar($value)) {
			return (string)$value;
		} elseif(is_object($value) && method_exists($value, '__toString')) {
			return $value->__toString();
		} else {
			throw new \InvalidArgumentException('Must be String: '.print_r($value, true));
		}
	}

	/**
	 * isInt
	 * @param mixed $value
	 * @return int
	 */
	public static function isInt($value) {
		if(is_int($value)) {
			return $value;
		} elseif(is_scalar($value) && preg_match('@^[0-9]+$', $value)) {
			return (int)$value;
		} else {
			throw new \InvalidArgumentException('Must be Int: '.print_r($value, true));
		}
	}
}