<?php
namespace Bullhorn\FastRest\Api\Services\Database;
use Bullhorn\FastRest\DependencyInjection;
use Phalcon\Db\AdapterInterface;
use Phalcon\Di\InjectionAwareInterface;

class Connections implements InjectionAwareInterface {
    use DependencyInjection;
	const DI_NAME = 'DbConnections';
	/** @type  AdapterInterface[] */
	private $dbAdapters = [];

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