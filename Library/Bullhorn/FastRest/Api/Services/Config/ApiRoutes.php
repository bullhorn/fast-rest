<?php
namespace Bullhorn\FastRest\Api\Services\Config;

use Phalcon\Mvc\Router;

class ApiRoutes {
    /** @type string */
    private $apiRootUrl;
    /** @type string */
    private $apiControllerRootNamespace;

    /**
     * ApiRoutes constructor.
     * @param string $apiRootUrl
     * @param string $apiControllerRootNamespace
     */
    public function __construct($apiRootUrl, $apiControllerRootNamespace) {
        $this->setApiRootUrl($apiRootUrl);
        $this->setApiControllerRootNamespace($apiControllerRootNamespace);
    }

    public function addRoutes(Router $router) {
        $router->add(
            '/' . $this->getApiRootUrl() . '(\/?)',
            array(
                'namespace' => $this->getApiControllerRootNamespace(),
                'controller' => 'Index',
                'action' => 'index'
            )
        );
        $router->addGet(
            '/' . $this->getApiRootUrl() . '/:controller/:params',
            array(
                'namespace' => $this->getApiControllerRootNamespace(),
                'controller' => 1,
                'action' => 'show',
                'params' => 2
            )
        );
        $router->addGet(
            '/' . $this->getApiRootUrl() . '/:controller/search/:params',
            array(
                'namespace' => $this->getApiControllerRootNamespace(),
                'controller' => 1,
                'action' => 'search',
                'params' => 2
            )
        );
        $router->addGet(
            '/' . $this->getApiRootUrl() . '/:controller',
            array(
                'namespace' => $this->getApiControllerRootNamespace(),
                'controller' => 1,
                'action' => 'index'
            )
        );
        $router->addPost(
            '/' . $this->getApiRootUrl() . '/:controller',
            array(
                'namespace' => $this->getApiControllerRootNamespace(),
                'controller' => 1,
                'action' => 'create'
            )
        );

        $router->addOptions(
            '/' . $this->getApiRootUrl() . '/:controller',
            array(
                'namespace' => $this->getApiControllerRootNamespace(),
                'controller' => 1,
                'action' => 'options'
            )
        );

        $router->addOptions(
            '/' . $this->getApiRootUrl() . '/:controller/:params',
            array(
                'namespace' => $this->getApiControllerRootNamespace(),
                'controller' => 1,
                'action' => 'options'
            )
        );

        $router->addDelete(
            '/' . $this->getApiRootUrl() . '/:controller/:params',
            array(
                'namespace' => $this->getApiControllerRootNamespace(),
                'controller' => 1,
                'action' => 'delete',
                'params' => 2
            )
        );
        $router->addPut(
            '/' . $this->getApiRootUrl() . '/:controller/:params',
            array(
                'namespace' => $this->getApiControllerRootNamespace(),
                'controller' => 1,
                'action' => 'update',
                'params' => 2
            )
        );
    }

    /**
     * Getter
     * @return string
     */
    private function getApiRootUrl() {
        return $this->apiRootUrl;
    }

    /**
     * Setter
     * @param string $apiRootUrl
     * @return ApiRoutes
     */
    private function setApiRootUrl($apiRootUrl) {
        $this->apiRootUrl = $apiRootUrl;
        return $this;
    }

    /**
     * Getter
     * @return string
     */
    private function getApiControllerRootNamespace() {
        return $this->apiControllerRootNamespace;
    }

    /**
     * Setter
     * @param string $apiRootNamespace
     * @return ApiRoutes
     */
    private function setApiControllerRootNamespace($apiRootNamespace) {
        $this->apiControllerRootNamespace = $apiRootNamespace;
        return $this;
    }


}