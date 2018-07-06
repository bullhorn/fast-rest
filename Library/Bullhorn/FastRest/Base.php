<?php
namespace Bullhorn\FastRest;

use Phalcon\DI\InjectionAwareInterface;
use Phalcon\DiInterface;

abstract class Base implements InjectionAwareInterface {

    public function getDi() {
        return DependencyInjectionHelper::getDi();
    }

    public function setDi(DiInterface $di) {
        DependencyInjectionHelper::setDi($di);
    }

}
