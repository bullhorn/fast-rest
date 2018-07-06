<?php
namespace Bullhorn\FastRest\Api\Services\Database;
use Bullhorn\FastRest\DependencyInjection;
use Bullhorn\FastRest\DependencyInjectionHelper;
use Phalcon\Db\AdapterInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;

class Connections implements InjectionAwareInterface {
	const DI_NAME = 'DbConnections';
	/** @type  AdapterInterface[] */
	private $dbAdapters = [];

    public function getDi() {
        return DependencyInjectionHelper::getDi();
    }

    public function setDi(DiInterface $di) {
        DependencyInjectionHelper::setDi($di);
    }

    /**
	 * Getter
	 * @return AdapterInterface[]
	 */
	private function getDbAdapters() {
		return $this->dbAdapters;
	}

	/**
	 * Setter
	 * @param AdapterInterface[] $dbAdapters
	 */
	private function setDbAdapters(array $dbAdapters) {
		$this->dbAdapters = $dbAdapters;
	}

	/**
	 * generateAdapter
	 * @param string $className
	 * @param array  $configInfo
	 * @return AdapterInterface
	 */
	public function generateAdapter($className, array $configInfo) {
        $configInfo['charset'] = 'utf8';
		$key = json_encode($configInfo);
		$dbAdapters = $this->getDbAdapters();
		if(!array_key_exists($key, $dbAdapters)) {
			$dbAdapter = new $className($configInfo);
			$dbAdapters[$key] = $dbAdapter;
			$this->setDbAdapters($dbAdapters);
		}
		return $dbAdapters[$key];
	}
}