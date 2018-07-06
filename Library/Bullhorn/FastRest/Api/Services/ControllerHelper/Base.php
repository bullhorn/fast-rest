<?php
namespace Bullhorn\FastRest\Api\Services\ControllerHelper;

use Bullhorn\FastRest\Api\Services\Acl\AclInterface as Acl;
use Bullhorn\FastRest\DependencyInjection;
use Bullhorn\FastRest\DependencyInjectionHelper;
use Phalcon\DiInterface;

abstract class Base {

    public function getDi() {
        return DependencyInjectionHelper::getDi();
    }

    public function setDi(DiInterface $di) {
        DependencyInjectionHelper::setDi($di);
    }


    /**
     * Gets the Access Control Layer
     * @return Acl
     */
    public function getAcl() {
        return $this->getDi()->get('Acl');
    }

}