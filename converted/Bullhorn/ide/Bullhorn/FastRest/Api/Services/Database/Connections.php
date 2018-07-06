<?php
namespace Bullhorn\FastRest\Api\Services\Database
{
    use Bullhorn\FastRest\DependencyInjection;
    use Bullhorn\FastRest\DependencyInjectionHelper;
    use Phalcon\Db\AdapterInterface;
    use Phalcon\Di\InjectionAwareInterface;
    use Phalcon\DiInterface;

    class Connections implements InjectionAwareInterface 
    {
        const DI_NAME = 'DbConnections';

        /**
         * @type  AdapterInterface[] *
         */
        protected $dbAdapters;

        public function getDi()
        {}

        public function setDi($di)
        {}

        /**
         * Getter
         *
         * @return AdapterInterface[]
         */
        protected function getDbAdapters()
        {}

        /**
         * Setter
         *
         * @param AdapterInterface[] $dbAdapters
         */
        protected function setDbAdapters(array $dbAdapters)
        {}

        /**
         * generateAdapter
         *
         * @param string $className
         * @param array  $configInfo
         * @return AdapterInterface
         */
        public function generateAdapter(string $className, array $configInfo)
        {}

    }

}

