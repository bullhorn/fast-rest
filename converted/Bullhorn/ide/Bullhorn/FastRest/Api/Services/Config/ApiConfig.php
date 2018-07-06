<?php
namespace Bullhorn\FastRest\Api\Services\Config
{
    use Phalcon\Mvc\Router;

    class ApiConfig 
    {
        const DI_NAME = 'ApiConfig';

        /**
         * @var int  *
         */
        protected $indexMaxLimit;

        /**
         * @var int  *
         */
        protected $indexDefaultLimit;

        /**
         * Getter
         *
         * @return int
         */
        public function getIndexMaxLimit()
        {}

        /**
         * Setter
         *
         * @param int $indexMaxLimit
         * @return ApiRoutes
         */
        public function setIndexMaxLimit(int $indexMaxLimit)
        {}

        /**
         * Getter
         *
         * @return int
         */
        public function getIndexDefaultLimit()
        {}

        /**
         * Setter
         *
         * @param int $indexDefaultLimit
         * @return ApiConfig
         */
        public function setIndexDefaultLimit(int $indexDefaultLimit)
        {}

    }

}

