<?php
//To use this, you must also implement \Phalcon\DI\InjectionAwareInterface
namespace Bullhorn\FastRest;

use Phalcon\DI\FactoryDefault;
use Phalcon\DiInterface;

trait DependencyInjection {

    public function getDi() {
        return DependencyInjectionHelper::getDi();
    }

    public function setDi(DiInterface $di) {
        DependencyInjectionHelper::setDi($di);
    }

}