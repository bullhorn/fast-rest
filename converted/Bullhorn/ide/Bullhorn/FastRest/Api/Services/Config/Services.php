<?php
namespace Bullhorn\FastRest\Api\Services\Config
{
    use Bullhorn\FastRest\Api\Services\Model\Manager as ModelsManager;
    use Bullhorn\FastRest\Api\Services\Database\Connections;
    use Bullhorn\FastRest\DependencyInjection;
    use Bullhorn\FastRest\DependencyInjectionHelper;
    use Phalcon\Config;
    use Phalcon\Di\InjectionAwareInterface;
    use Phalcon\DiInterface;

    class Services implements InjectionAwareInterface 
    {
        public function getDi()
        {}

        public function setDi($di)
        {}

        /**
         * initialize
         *
         * @param Config $config
         * @return void
         */
        public function initialize($config)
        {}

        /**
         * addApiConfig
         *
         * @return void
         */
        protected function addApiConfig()
        {}

    }

}

