<?php
namespace Bullhorn\FastRest\Api\Services\Config;
use Bullhorn\FastRest\Api\Services\Model\Manager as ModelsManager;
use Bullhorn\FastRest\Api\Services\Database\Connections;
use Bullhorn\FastRest\DependencyInjection;
use Phalcon\Config;
use Phalcon\Di\InjectionAwareInterface;
use \Phalcon\Mvc\Dispatcher as PhDispatcher;

class Services implements InjectionAwareInterface {
    use DependencyInjection;

    /**
     * initialize
     * @param Config $config
     * @return void
     */
    public function initialize(Config $config) {
        $di = $this->getDi();

        $di->setShared(
            Connections::DI_NAME,
            function() {
                return new Connections();
            }
        );


        $this->getDi()->setShared(
            'modelsManager',
            function() {
                return new ModelsManager();
            }
        );

        $this->addApiConfig();
    }

    /**
     * addApiConfig
     * @return void
     */
    private function addApiConfig() {
        $this->getDi()->setShared(ApiConfig::DI_NAME, new ApiConfig());

        $di = $this->getDi();
        $di->set(
            'dispatcher',
            function() use ($di) {

                $evManager = $di->getShared('eventsManager');

                $evManager->attach(
                    "dispatch:beforeException",
                    function($event, $dependencyInjectorspatcher, $exception)
                    {
                        switch ($exception->getCode()) {
                            case PhDispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                            case PhDispatcher::EXCEPTION_ACTION_NOT_FOUND:
                                return false;
                        }
                    }
                );
                $dependencyInjectorsPatcher = new PhDispatcher();
                $dependencyInjectorsPatcher->setEventsManager($evManager);
                return $dependencyInjectorsPatcher;
            },
            true
        );

    }
}