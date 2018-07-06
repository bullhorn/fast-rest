<?php
namespace Bullhorn\FastRest\Api\Services\Config
{
    use Phalcon\Mvc\Router;

    class ApiRoutes 
    {
        /**
         * @type string *
         */
        protected $apiRootUrl;

        /**
         * @type string *
         */
        protected $apiControllerRootNamespace;

        /**
         * ApiRoutes constructor.
         *
         * @param string $apiRootUrl
         * @param string $apiControllerRootNamespace
         */
        public function __construct(string $apiRootUrl, string $apiControllerRootNamespace)
        {}

        public function addRoutes($router)
        {}

        /**
         * Getter
         *
         * @return string
         */
        protected function getApiRootUrl()
        {}

        /**
         * Setter
         *
         * @param string $apiRootUrl
         * @return ApiRoutes
         */
        protected function setApiRootUrl(string $apiRootUrl)
        {}

        /**
         * Getter
         *
         * @return string
         */
        protected function getApiControllerRootNamespace()
        {}

        /**
         * Setter
         *
         * @param string $apiRootNamespace
         * @return ApiRoutes
         */
        protected function setApiControllerRootNamespace(string $apiRootNamespace)
        {}

    }

}

