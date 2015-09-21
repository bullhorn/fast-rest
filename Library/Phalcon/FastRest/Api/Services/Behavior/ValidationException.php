<?php
namespace Phalcon\FastRest\Api\Services\Behavior;
use Phalcon\FastRest\Api\Models\GeneratedInterface;
use Phalcon\Validation\Exception as Exception ;
class ValidationException extends Exception {
	/** @var GeneratedInterface  */
	private $entity;

	/**
	 * Getter
	 * @return GeneratedInterface
	 */
	public function getEntity() {

		return $this->entity;
	}

	/**
	 * Setter
	 * @param GeneratedInterface $entity
	 */
	public function setEntity(GeneratedInterface $entity) {
		$this->entity = $entity;
	}


}