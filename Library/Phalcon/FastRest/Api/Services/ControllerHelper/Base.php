<?php
namespace Phalcon\FastRest\Api\Services\ControllerHelper;
use Phalcon\FastRest\Api\Services\Acl\AclInterface as Acl;
use Phalcon\FastRest\DependencyInjection;
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