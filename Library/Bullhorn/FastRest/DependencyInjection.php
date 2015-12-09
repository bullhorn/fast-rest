<?php
//To use this, you must also implement \Phalcon\DI\InjectionAwareInterface
namespace Bullhorn\FastRest;

use Phalcon\DI\FactoryDefault;
use Phalcon\DiInterface;

trait DependencyInjection {
    /** @var  DiInterface */
    private $di;

    /**
     * Gets the dependency injector
     * @return DiInterface
     */
    public function getDi() {
        if(is_null($this->di)) {
            $this->di = FactoryDefault::getDefault();
            if(is_null($this->di)) {
                $this->di = new FactoryDefault();
            }
        }
        return $this->di;
    }

    /**
     * Sets the dependency injector
     * We cannot strict type the variable
     * @param DiInterface $di
     *
     * @return void
     */
    public function setDi(DiInterface $di) {
        $this->di = $di;
    }
}