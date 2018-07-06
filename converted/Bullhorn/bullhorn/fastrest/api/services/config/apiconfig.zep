namespace Bullhorn\FastRest\Api\Services\Config;

use Phalcon\Mvc\Router;
class ApiConfig
{
    const DI_NAME = "ApiConfig";
    /** @var int  */
    protected indexMaxLimit = 500;
    /** @var int  */
    protected indexDefaultLimit = 50;
    /**
     * Getter
     * @return int
     */
    public function getIndexMaxLimit() -> int
    {
        return this->indexMaxLimit;
    }
    
    /**
     * Setter
     * @param int $indexMaxLimit
     * @return ApiRoutes
     */
    public function setIndexMaxLimit(int indexMaxLimit) -> <ApiRoutes>
    {
        let this->indexMaxLimit = indexMaxLimit;
        return this;
    }
    
    /**
     * Getter
     * @return int
     */
    public function getIndexDefaultLimit() -> int
    {
        return this->indexDefaultLimit;
    }
    
    /**
     * Setter
     * @param int $indexDefaultLimit
     * @return ApiConfig
     */
    public function setIndexDefaultLimit(int indexDefaultLimit) -> <ApiConfig>
    {
        let this->indexDefaultLimit = indexDefaultLimit;
        return this;
    }

}