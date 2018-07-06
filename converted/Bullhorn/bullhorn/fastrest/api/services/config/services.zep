namespace Bullhorn\FastRest\Api\Services\Config;

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
    {
        return DependencyInjectionHelper::getDi();
    }

    public function setDi(<DiInterface> di) -> void
    {
        DependencyInjectionHelper::setDi(di);
    }

    /**
     * initialize
     * @param Config $config
     * @return void
     */
    public function initialize(<Config> config)
    {
        this->getDi()->setShared(
            Connections::DI_NAME,
            function() {
                return new \Bullhorn\FastRest\Api\Services\Database\Connections();
            }
        );


        this->getDi()->setShared(
            "modelsManager",
            function() {
                return new \Bullhorn\FastRest\Api\Services\Model\Manager();
            }
        );

        this->addApiConfig();
    }

    /**
     * addApiConfig
     * @return void
     */
    protected function addApiConfig()
    {
        this->getDi()->setShared(ApiConfig::DI_NAME, new ApiConfig());
    }

}