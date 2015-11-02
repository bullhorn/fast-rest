<?php
namespace YourService\Models\Database;
use Phalcon\Filter;
use Bullhorn\FastRest\Api\Models\Base as FastRestBase;
abstract class Base extends FastRestBase {

	/**
	 * Getter
	 * @return string[]
	 */
	public function getAutomaticallyUpdatedFields() {
		return [];
	}

	/**
	 * getDefaultRelationships
	 * @return array
	 */
	public function getDefaultRelationships() {
		return [];
	}

}