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
	const EVENT_ROLLBACK = 'transaction:rollback';
	const EVENT_COMMIT = 'transaction:commit';

	private static $stackTrace;
	/** @type  string */
	private $dbService;
	/** @type TransactionInterface[]  */
	private static $transactions = [];

	/**
	 * Transaction constructor.
	 * @param string $dbService
	 */
	public function __construct($dbService='db') {
		$this->setDbService($dbService);
	}

	/**
	 * Getter
	 * @return TransactionInterface[]
	 */
	private function getTransactions() {
		return self::$transactions;
	}

	/**
	 * Setter
	 * @param TransactionInterface[] $transactions
	 */
	private function setTransactions(array $transactions) {
		self::$transactions = $transactions;
	}

	/**
	 * setTransaction
	 * @param TransactionInterface $transaction
	 * @return void
	 */
	private function setTransaction(TransactionInterface $transaction) {
		$transactions = $this->getTransactions();
		$transactions[$this->getDbService()] = $transaction;
		$this->setTransactions($transactions);
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
		if($this->isInTransaction()) {
			throw new \Exception('You cannot start a new transaction once you are already in one (Originally started at: '.self::$stackTrace.')');
		} else {
			$e = new \Exception();
			self::$stackTrace = $e->getTraceAsString();
		}
		$transactionManager = new TransactionManager();
		$transactionManager->setDbService($this->getDbService());
		$this->setTransaction($transactionManager->get());
	}

	/**
	 * isInTransaction
	 * @return bool
	 */
	public function isInTransaction() {
		$transactions = $this->getTransactions();
		return array_key_exists($this->getDbService(), $transactions);
	}

	/**
	 * isInAnyTransaction
	 * @return bool
	 */
	public function isInAnyTransaction() {
		return !empty($this->getTransactions());
	}

	/**
	 * Gets the current transaction, throws an exception if there isn't one
	 * @return TransactionInterface
	 * @throws \Exception
	 */
	public function getTransaction() {
		if(!$this->isInTransaction()) {
			throw new \Exception('This transaction has already been rolled back, or committed, or has not began yet');
		}
		$transactions = $this->getTransactions();
		return $transactions[$this->getDbService()];
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
		$transactions = $this->getTransactions();
		unset($transactions[$this->getDbService()]);
		$this->setTransactions($transactions);

		$this->findEventsManager()->fire(self::EVENT_ROLLBACK, $this);
	}

	/**
	 * Commit a transaction
	 * @return void
	 * @throws \Exception
	 */
	public function commit() {
		$this->getTransaction()->commit();

		$transactions = $this->getTransactions();
		unset($transactions[$this->getDbService()]);
		$this->setTransactions($transactions);

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