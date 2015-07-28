<?php
namespace Phalcon\ApiGenerator\Api\Services;
use Phalcon\Filter as PhalconFilter;
class Filter extends PhalconFilter {
	const FILTER_STRING = 'string';
	const FILTER_EMAIL = 'email';
	const FILTER_INT = 'int';
	const FILTER_FLOAT = 'float';
	const FILTER_ALPHA_NUM = 'alphanum';
	const FILTER_STRIP_TAGS = 'striptags';
	const FILTER_TRIM = 'trim';
	const FILTER_LOWER = 'lower';
	const FILTER_UPPER = 'upper';
	const FILTER_BOOLEAN = 'boolean';
	const FILTER_NULLIFY = 'nullify';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->add(
			'boolean',
			function($value) {
				return filter_var(
					$value,
					FILTER_VALIDATE_BOOLEAN,
					array(
						'flags' => FILTER_NULL_ON_FAILURE
					)
				);
			}
		);
		$this->add(
			'nullify',
			function($value) {
				if($value ==='' || $value ==='null') {
					return null;
				} else {
					return $value;
				}
			}
		);
	}
}