<?php
namespace Phalcon\ApiGenerator\Generator;

class PluralHelper {
	/**
	 * pluralizes a string
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function pluralize($string) {
		if(preg_match('@ch$@', $string)) {
			$string .= 'es';
		} elseif(preg_match('@y$@', $string)) {
			$string = substr($string, 0, -1).'ies';
		} else {
			$string .= 's';
		}
		return $string;
	}
}