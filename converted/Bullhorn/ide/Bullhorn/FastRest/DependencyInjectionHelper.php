<?php
namespace Bullhorn\FastRest
{
    use Phalcon\DI\FactoryDefault;
    use Phalcon\DiInterface;

    /**
     * 
     *
     * @final
     */
    final class DependencyInjectionHelper 
    {
        /**
         * @var  DiInterface *
         * @static
         */
        protected static $di;

        /**
         * Gets the dependency injector
         *
         * @return DiInterface
         * @static
         */
        public static function getDi()
        {}

        /**
         * Sets the dependency injector
         * We cannot strict type the variable
         *
         * @param DiInterface $di
         * @return void
         * @static
         */
        public static function setDi($di)
        {}

    }

}

