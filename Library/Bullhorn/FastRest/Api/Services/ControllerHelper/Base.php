<?php
namespace Bullhorn\FastRest\Api\Services\ControllerHelper;

use Bullhorn\FastRest\Api\Services\Acl\AclInterface as Acl;
use Bullhorn\FastRest\DependencyInjection;

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