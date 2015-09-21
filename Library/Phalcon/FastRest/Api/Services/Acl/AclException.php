<?php
namespace Phalcon\FastRest\Api\Services\Acl;

use Phalcon\Validation\Exception as Exception ;
class AclException extends Exception {
	/** @var EntityInterface  */
	private $entity;

	/**
	 * Getter
	 * @return EntityInterface
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 * Setter
	 * @param EntityInterface $entity
	 */
	public function setEntity(EntityInterface $entity) {
		$this->entity = $entity;
	}


}