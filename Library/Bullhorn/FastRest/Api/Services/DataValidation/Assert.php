<?php
namespace Bullhorn\FastRest\Api\Services\DataValidation;
class Assert {

    /**
     * Cleans bool input
     * @param string $input
     * @return bool
     */
    public static function isBool($input) {
        if(is_bool($input)) {
            return $input; //Already a boolean
        }
        if(is_int($input) || is_float($input)) {
            return $input != 0;
        }
        $input = strtolower(self::isString($input));
        return in_array($input, array('active', 'true', 'yes', '1', 'on', 'y'), TRUE);
    }

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
            throw new \InvalidArgumentException('Must be String: ' . print_r($value, true));
        }
    }

    /**
     * isInt
     * @param mixed $value
     * @return int
     */
    public static function isInt($value) {
        if(is_object($value)) {
            $value = Assert::isString($value);
        }
        if(is_int($value)) {
            return $value;
        } elseif(is_scalar($value) && preg_match('@^-?[0-9]+$@', $value)) {
            return (int)$value;
        } else {
            throw new \InvalidArgumentException('Must be Int: ' . print_r($value, true));
        }
    }

    /**
     * isFloat
     * @param mixed $value
     * @return float
     */
    public static function isFloat($value) {
        if(is_object($value)) {
            $value = Assert::isString($value);
        }
        if(is_float($value)) {
            return $value;
        } elseif(is_int($value)) {
            return (float)$value;
        } elseif(is_scalar($value) && (preg_match('@^-?[0-9]+(\.[0-9]+)?$@', $value) || trim($value)=='')) {
            return (float)$value;
        } else {
            throw new \InvalidArgumentException('Must be Float: ' . print_r($value, true));
        }
    }

    /**
     * isArray
     * @param mixed $value
     * @return array
     */
    public static function isArray($value) {
        if(!is_array($value)) {
            throw new \InvalidArgumentException('Must be Array: ' . print_r($value, true));
        }
        return $value;
    }

}