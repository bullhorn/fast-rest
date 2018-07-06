namespace Bullhorn\FastRest\Api\Services\Database;

use Bullhorn\FastRest\DependencyInjection;
use Bullhorn\FastRest\DependencyInjectionHelper;
use Phalcon\Db\AdapterInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;
class Connections implements InjectionAwareInterface
{
    const DI_NAME = "DbConnections";
    /** @type  AdapterInterface[] */
    protected dbAdapters = [];
    public function getDi()
    {
        return DependencyInjectionHelper::getDi();
    }
    
    public function setDi(<DiInterface> di) -> void
    {
        DependencyInjectionHelper::setDi(di);
    }
    
    /**
     * Getter
     * @return AdapterInterface[]
     */
    protected function getDbAdapters() -> array
    {
        return this->dbAdapters;
    }
    
    /**
     * Setter
     * @param AdapterInterface[] $dbAdapters
     */
    protected function setDbAdapters(array dbAdapters) -> void
    {
        let this->dbAdapters = dbAdapters;
    }
    
    /**
     * generateAdapter
     * @param string $className
     * @param array  $configInfo
     * @return AdapterInterface
     */
    public function generateAdapter(string className, array configInfo) -> <AdapterInterface>
    {
        var key, dbAdapters, dbAdapter;
    
        let configInfo["charset"] = "utf8";
        let key =  json_encode(configInfo);
        let dbAdapters =  this->getDbAdapters();
        if !(array_key_exists(key, dbAdapters)) {
            let dbAdapter =  new {className}(configInfo);
            let dbAdapters[key] = dbAdapter;
            this->setDbAdapters(dbAdapters);
        }
        return dbAdapters[key];
    }

}