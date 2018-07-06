namespace Bullhorn\FastRest\Api\Services\Config;

use Phalcon\Mvc\Router;
class ApiRoutes
{
    /** @type string */
    protected apiRootUrl;
    /** @type string */
    protected apiControllerRootNamespace;
    /**
     * ApiRoutes constructor.
     * @param string $apiRootUrl
     * @param string $apiControllerRootNamespace
     */
    public function __construct(string apiRootUrl, string apiControllerRootNamespace) -> void
    {
        this->setApiRootUrl(apiRootUrl);
        this->setApiControllerRootNamespace(apiControllerRootNamespace);
    }
    
    public function addRoutes(<Router> router) -> void
    {
        var tmpArray813af70dcc267601c54b831e267047b2, tmpArraye76a5e7926740d88d777d1283302fc0a, tmpArraybfdd616d613c96dad94a640ce37ed8ad, tmpArray04a84e73a7d5baebed7bda4ad128e04f, tmpArraya7610ff59f7fc67b1db432ad29454edf, tmpArray4ff06f12225b28eb382a068a686abc95, tmpArray2ff3be242435900626063027fb5904cb, tmpArrayb7c20f548eacfa7e68e5b5c6dacfe732, tmpArray51e1d0946255b83db778b956d3dc6bdb;
    
        let tmpArray813af70dcc267601c54b831e267047b2 = ["namespace" : this->getApiControllerRootNamespace(), "controller" : "Index", "action" : "index"];
        router->add("/" . this->getApiRootUrl() . "(\\/?)", tmpArray813af70dcc267601c54b831e267047b2);
        let tmpArraye76a5e7926740d88d777d1283302fc0a = ["namespace" : this->getApiControllerRootNamespace(), "controller" : 1, "action" : "show", "params" : 2];
        router->addGet("/" . this->getApiRootUrl() . "/:controller/:params", tmpArraye76a5e7926740d88d777d1283302fc0a);
        let tmpArraybfdd616d613c96dad94a640ce37ed8ad = ["namespace" : this->getApiControllerRootNamespace(), "controller" : 1, "action" : "search", "params" : 2];
        router->addGet("/" . this->getApiRootUrl() . "/:controller/search/:params", tmpArraybfdd616d613c96dad94a640ce37ed8ad);
        let tmpArray04a84e73a7d5baebed7bda4ad128e04f = ["namespace" : this->getApiControllerRootNamespace(), "controller" : 1, "action" : "index"];
        router->addGet("/" . this->getApiRootUrl() . "/:controller", tmpArray04a84e73a7d5baebed7bda4ad128e04f);
        let tmpArraya7610ff59f7fc67b1db432ad29454edf = ["namespace" : this->getApiControllerRootNamespace(), "controller" : 1, "action" : "create"];
        router->addPost("/" . this->getApiRootUrl() . "/:controller", tmpArraya7610ff59f7fc67b1db432ad29454edf);
        let tmpArray4ff06f12225b28eb382a068a686abc95 = ["namespace" : this->getApiControllerRootNamespace(), "controller" : 1, "action" : "options"];
        router->addOptions("/" . this->getApiRootUrl() . "/:controller", tmpArray4ff06f12225b28eb382a068a686abc95);
        let tmpArray2ff3be242435900626063027fb5904cb = ["namespace" : this->getApiControllerRootNamespace(), "controller" : 1, "action" : "options"];
        router->addOptions("/" . this->getApiRootUrl() . "/:controller/:params", tmpArray2ff3be242435900626063027fb5904cb);
        let tmpArrayb7c20f548eacfa7e68e5b5c6dacfe732 = ["namespace" : this->getApiControllerRootNamespace(), "controller" : 1, "action" : "delete", "params" : 2];
        router->addDelete("/" . this->getApiRootUrl() . "/:controller/:params", tmpArrayb7c20f548eacfa7e68e5b5c6dacfe732);
        let tmpArray51e1d0946255b83db778b956d3dc6bdb = ["namespace" : this->getApiControllerRootNamespace(), "controller" : 1, "action" : "update", "params" : 2];
        router->addPut("/" . this->getApiRootUrl() . "/:controller/:params", tmpArray51e1d0946255b83db778b956d3dc6bdb);
    }
    
    /**
     * Getter
     * @return string
     */
    protected function getApiRootUrl() -> string
    {
        return this->apiRootUrl;
    }
    
    /**
     * Setter
     * @param string $apiRootUrl
     * @return ApiRoutes
     */
    protected function setApiRootUrl(string apiRootUrl) -> <ApiRoutes>
    {
        let this->apiRootUrl = apiRootUrl;
        return this;
    }
    
    /**
     * Getter
     * @return string
     */
    protected function getApiControllerRootNamespace() -> string
    {
        return this->apiControllerRootNamespace;
    }
    
    /**
     * Setter
     * @param string $apiRootNamespace
     * @return ApiRoutes
     */
    protected function setApiControllerRootNamespace(string apiRootNamespace) -> <ApiRoutes>
    {
        let this->apiControllerRootNamespace = apiRootNamespace;
        return this;
    }

}