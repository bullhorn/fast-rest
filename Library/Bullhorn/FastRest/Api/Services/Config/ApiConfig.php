<?php
namespace Bullhorn\FastRest\Api\Services\Config;

use Phalcon\Mvc\Router;

class ApiConfig {
    const DI_NAME = 'ApiConfig';

    /** @var int  */
    private $indexMaxLimit = 500;

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


}