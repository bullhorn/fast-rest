<?php
namespace Bullhorn\FastRest\Api\Services\Helper;
use InvalidArgumentException;

class Json {
    public static function decode(string $string) {
        $returnVar = json_decode($string);
        if(json_last_error()!=JSON_ERROR_NONE) {
            $error = 'JSON Decode Error';
            switch(json_last_error()) {
                case JSON_ERROR_DEPTH:
                    $error .= ' - Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error .= ' - Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error .= ' - Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error .= ' - Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $error .= ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $error .= ' - Unknown error';
                    break;
            }
            throw new InvalidArgumentException($error);
        }
        return $returnVar;
    }
}