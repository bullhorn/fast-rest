<?php
namespace Phalcon\ApiGenerator\Api\Services\Database;
use Phalcon\ApiGenerator\DependencyInjection;
use Phalcon\DI\InjectionAwareInterface;
use Phalcon\Mvc\Model\Transaction\Failed;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use \Phalcon\Mvc\Model\TransactionInterface;
use Phalcon\Events\Manager as EventsManager;
class Transaction implements InjectionAwareInterface {
	use DependencyInjection;
	const DI_NAME = 'CurrentTransaction';
	const EVENT_ROLLBACK = 'transaction:rollback';
	const EVENT_COMMIT = 'transaction:commit';

	private static $stackTrace;
	/** @type  string */
	private $dbService;

	/**
	 * Transaction constructor.
	 * @param string $dbService
	 */
	public function __construct($dbService='db') {
		$this->setDbService($dbService);
	}

	/**
	 * Getter
	 * @return string
	 */
	private function getDbService() {
		return $this->dbService;
	}

	/**
	 * Setter
	 * @param string $dbService
	 */
	private function setDbService($dbService) {
		$this->dbService = $dbService;
	}




	/**
	 * Begin a transaction
	 * @return void
	 * @throws \Exception
	 */
	public function begin() {
		if($this->getDi()->has(self::DI_NAME)) {
			throw new \Exception('You cannot start a new transaction once you are already in one (Originally started at: '.self::$stackTrace.')');
		} else {
			$e = new \Exception();
			self::$stackTrace = $e->getTraceAsString();
		}
		$transactionManager = new TransactionManager();
		$transactionManager->setDbService($this->getDbService());
		$this->getDi()->set(self::DI_NAME, $transactionManager->get());
	}

	/**
	 * isInTransaction
	 * @return bool
	 */
	public function isInTransaction() {
		return $this->getDi()->has(self::DI_NAME);
	}

	/**
	 * Gets the current transaction, throws an exception if there isn't one
	 * @return TransactionInterface
	 * @throws \Exception
	 */
	public function getTransaction() {
		if(!$this->getDi()->has(self::DI_NAME)) {
			throw new \Exception('This transaction has already been rolled back, or committed, or has not began yet');
		}
		return $this->getDi()->get(self::DI_NAME);
	}

	/**
	 * Rollback a transaction
	 * @return void
	 * @throws \Exception
	 */
	public function rollback() {
		try {
			$this->getTransaction()->rollback();
		} catch(Failed $e) {
			if($e->getMessage()!='Transaction aborted') {
				throw $e;
			}
		}
		$this->getDi()->remove(self::DI_NAME);
		$this->findEventsManager()->fire(self::EVENT_ROLLBACK, $this);
	}

	/**
	 * Commit a transaction
	 * @return void
	 * @throws \Exception
	 */
	public function commit() {
		$this->getTransaction()->commit();
		$this->getDi()->remove(self::DI_NAME);
		$this->findEventsManager()->fire(self::EVENT_COMMIT, $this);
	}
	
	/**
	 * findEventsManager
	 * @return EventsManager
	 */
	public function findEventsManager() {
		return $this->getDi()->get('eventsManager');
	}

}