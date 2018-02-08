<?php
namespace Bullhorn\FastRest\Generator;

class PluralHelper {
    const RULES = [
        '@(^d|D)ay$@' => '\\1ays',
        '@(ch|x)$@' => '\\1es',
        '@y$@' => 'ies',
        '@^$@'=> '',
        '@$@' => 's',
    ];

    /**
     * pluralizes a string
     *
     * @param string $string
     *
     * @return string
     */
    public function pluralize($string) {
        $string = trim($string);
        foreach(self::RULES as $rule => $replace) {
            if(preg_match($rule, $string)) {
                return preg_replace($rule, $replace, $string);
            }
        }
    }
}