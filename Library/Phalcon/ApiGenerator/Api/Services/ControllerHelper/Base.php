<?php
namespace Phalcon\ApiGenerator\Api\Services\ControllerHelper;
use Phalcon\ApiGenerator\Api\Services\Acl\AclInterface as Acl;
use Phalcon\ApiGenerator\DependencyInjection;
abstract class Base {
	use DependencyInjection;

	/**
	 * Gets the Access Control Layer
	 * @return Acl
	 */
	public function getAcl() {
		return $this->getDi()->get('Acl');
	}

}