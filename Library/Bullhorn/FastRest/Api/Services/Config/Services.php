<?php
namespace Bullhorn\FastRest\Api\Services\Config;
use Bullhorn\FastRest\Api\Services\Database\Connections;
use Bullhorn\FastRest\DependencyInjection;
use Phalcon\Config;
use Phalcon\Di\InjectionAwareInterface;

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
    }
}