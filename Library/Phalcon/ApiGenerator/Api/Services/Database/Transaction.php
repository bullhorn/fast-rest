<?php
namespace Phalcon\ApiGenerator\Api\Services\Database;
use Phalcon\ApiGenerator\DependencyInjection;
use Phalcon\DI\InjectionAwareInterface;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use \Phalcon\Mvc\Model\TransactionInterface;
class Transaction implements InjectionAwareInterface{
	use DependencyInjection;
	const DI_NAME = 'CurrentTransaction';
	private static $stackTrace;

	/**
	 * Constructor
	 */
	public function __construct() {
		if($this->getDi()->has(self::DI_NAME)) {
			throw new \Exception('You cannot start a new transaction once you are already in one (Originally started at: '.self::$stackTrace.')');
		} else {
			$e = new \Exception();
			self::$stackTrace = $e->getTraceAsString();
		}
		$transactionManager = new TransactionManager();
		$this->getDi()->set(self::DI_NAME, $transactionManager->get());
	}

	/**
	 * Gets the current transaction, throws an exception if there isn't one
	 * @return TransactionInterface
	 * @throws \Exception
	 */
	public function getTransaction() {
		if(!$this->getDi()->has(self::DI_NAME)) {
			throw new \Exception('This transaction has already been rolled back, or committed');
		}
		return $this->getDi()->get(self::DI_NAME);
	}

	/**
	 * Rollback a transaction
	 * @return void
	 * @throws \Exception
	 */
	public function rollback() {
		$this->getTransaction()->rollback();
		$this->getDi()->remove(self::DI_NAME);
	}

	/**
	 * Commit a transaction
	 * @return void
	 * @throws \Exception
	 */
	public function commit() {
		$this->getTransaction()->commit();
		$this->getDi()->remove(self::DI_NAME);
	}

}