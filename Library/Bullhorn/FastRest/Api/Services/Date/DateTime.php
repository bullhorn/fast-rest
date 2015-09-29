<?php
namespace Bullhorn\FastRest\Api\Services\Date;
class DateTime extends Date {
	/**
	 * Converts to a date time format
	 * @return string
	 */
	public function __toString() {
		return $this->toDateTime();
	}
}