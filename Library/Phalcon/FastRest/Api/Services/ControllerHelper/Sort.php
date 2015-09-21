<?php
namespace Phalcon\FastRest\Api\Services\ControllerHelper;
class Sort {
	/** @var  bool */
	private $asc;
	/** @var  string[] */
	private $fields;

	/**
	 * @return boolean
	 */
	public function isAsc() {
		return $this->asc;
	}

	/**
	 * @param boolean $asc
	 */
	public function setAsc($asc) {
		$this->asc = $asc;
	}

	/**
	 * @return \string[]
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @param \string[] $fields
	 */
	public function setFields($fields) {
		$this->fields = $fields;
	}



}