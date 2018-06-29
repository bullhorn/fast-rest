<?php
namespace Bullhorn\FastRest\Generator;

class PluralHelper {

    /**
     * pluralizes a string
     *
     * @param string $string
     *
     * @return string
     */
    public static function pluralize($string) {
        $string = trim($string);
        $testString = strtolower($string);
        if($testString == '') {
            return '';
        } elseif(substr($testString, -3) == 'day') {
            return $string.'s';
        } elseif(substr($testString, -2) == 'ch') {
            return $string.'es';
        } elseif(substr($testString, -1) == 'x') {
            return $string.'es';
        } elseif(substr($testString, -1) == 'y') {
            return substr($string, 0, -1).'ies';
        } else {
            return $string.'s';
        }
    }
}