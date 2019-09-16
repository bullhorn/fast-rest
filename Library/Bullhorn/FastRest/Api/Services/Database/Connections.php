<?php
namespace Bullhorn\FastRest\Api\Services\Database;
use Bullhorn\FastRest\DependencyInjection;
use PDOException;
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
            $dbAdapter = null;
		    for($i=0; $i < 3 && is_null($dbAdapter); $i++) {
                try {
                    $dbAdapter = new $className($configInfo);
                } catch(PDOException $e) {
                    if(false === strstr($e->getMessage(), 'reading initial communication packet')) {
                        throw $e;
                    }
                    usleep(1000);
                }
            }

			$dbAdapters[$key] = $dbAdapter;
			$this->setDbAdapters($dbAdapters);
		}
		return $dbAdapters[$key];
	}
}