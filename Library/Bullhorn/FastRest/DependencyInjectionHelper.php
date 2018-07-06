<?php
//To use this, you must also implement \Phalcon\DI\InjectionAwareInterface
namespace Bullhorn\FastRest;

use Phalcon\DI\FactoryDefault;
use Phalcon\DiInterface;

final class DependencyInjectionHelper {
    /** @var  DiInterface */
    private static $di;

    /**
     * Gets the dependency injector
     * @return DiInterface
     */
    public static function getDi() {
        if(is_null(self::$di)) {
            self::$di = FactoryDefault::getDefault();
            if(is_null(self::$di)) {
                self::$di = new FactoryDefault();
            }
        }
        return self::$di;
    }

    /**
     * Sets the dependency injector
     * We cannot strict type the variable
     * @param DiInterface $di
     *
     * @return void
     */
    public static function setDi(DiInterface $di) {
        self::$di = $di;
    }
}