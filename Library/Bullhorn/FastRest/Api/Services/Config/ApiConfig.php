<?php
namespace Bullhorn\FastRest\Api\Services\Config;

use Phalcon\Mvc\Router;

class ApiConfig {
    const DI_NAME = 'ApiConfig';

    /** @var int  */
    private $indexMaxLimit = 500;

    /** @var int  */
    private $indexDefaultLimit = 50;

    /**
     * Getter
     * @return int
     */
    public function getIndexMaxLimit() {
        return $this->indexMaxLimit;
    }

    /**
     * Setter
     * @param int $indexMaxLimit
     * @return ApiRoutes
     */
    public function setIndexMaxLimit($indexMaxLimit) {
        $this->indexMaxLimit = $indexMaxLimit;
        return $this;
    }

    /**
     * Getter
     * @return int
     */
    public function getIndexDefaultLimit() {
        return $this->indexDefaultLimit;
    }

    /**
     * Setter
     * @param int $indexDefaultLimit
     * @return ApiConfig
     */
    public function setIndexDefaultLimit($indexDefaultLimit) {
        $this->indexDefaultLimit = $indexDefaultLimit;
        return $this;
    }




}