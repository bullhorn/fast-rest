<?php
namespace Phalcon\FastRest\Api\Models;
use Phalcon\Mvc\Model\Relation;
class CustomRelationship extends Relation {
	/** @var  string */
	private $alias;

	/**
	 * Getter
	 * @return string
	 */
	public function getAlias() {
		return $this->alias;
	}

	/**
	 * Setter
	 * @param string $alias
	 */
	public function setAlias($alias) {
		$this->alias = $alias;
	}



}