<?php
namespace Bullhorn\FastRest\Generator;
use Bullhorn\FastRest\Api\Services\Date\Date;
use Bullhorn\FastRest\Api\Services\Date\DateTime;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Bullhorn\FastRest\Base;
class Configuration extends Base {
	/** @var  string */
	private $rootNamespace;
	/** @var  string */
	private $modelSubNamespace = 'Instance';
	/** @var  string */
	private $rootDirectory;
	/** @var  string */
	private $rootTestDirectory;
	/** @var  string */
	private $connectionService = 'db';
	/** @var  string */
	private $dateClassName = Date::class;
	/** @var  string */
	private $dateTimeClassName = DateTime::class;

	/**
	 * Getter
	 * @return string
	 */
	public function getDateClassName() {
		return $this->dateClassName;
	}

	/**
	 * Setter
	 * @param string $dateClassName
	 */
	public function setDateClassName($dateClassName) {
		$this->dateClassName = $dateClassName;
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getDateTimeClassName() {
		return $this->dateTimeClassName;
	}

	/**
	 * Setter
	 * @param string $dateTimeClassName
	 */
	public function setDateTimeClassName($dateTimeClassName) {
		$this->dateTimeClassName = $dateTimeClassName;
	}


	/**
	 * Gets the database connection
	 * @return DbAdapter
	 */
	public function getConnection() {
		return $this->getDi()->get($this->getConnectionService());
	}

	/**
	 * Gets The connection to the information schema db
	 * @return DbAdapter
	 */
	public function getInformationSchemaConnection() {
		$descriptor = $this->getConnectionDescriptor();
		$descriptor['dbname'] = 'information_schema';
		return new DbAdapter($descriptor);
	}

	/**
	 * getConnectionDescriptor
	 * @return array
	 */
	public function getConnectionDescriptor() {
		$reflectionClass = new \ReflectionClass($this->getConnection());
		$reflectionProperty = $reflectionClass->getProperty('_descriptor');
		$reflectionProperty->setAccessible(true);
		return $reflectionProperty->getValue($this->getConnection());
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getConnectionService() {
		return $this->connectionService;
	}

	/**
	 * Setter
	 * @param string $connectionService
	 */
	public function setConnectionService($connectionService) {
		$this->connectionService = $connectionService;
	}


	/**
	 * Getter
	 * @return string
	 */
	public function getRootNamespace() {
		return $this->rootNamespace;
	}

	/**
	 * Setter
	 * @param string $rootNamespace
	 */
	public function setRootNamespace($rootNamespace) {
		$this->rootNamespace = $rootNamespace;
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getModelSubNamespace() {
		return $this->modelSubNamespace;
	}

	/**
	 * Setter
	 * @param string $modelSubNamespace
	 */
	public function setModelSubNamespace($modelSubNamespace) {
		$this->modelSubNamespace = $modelSubNamespace;
	}


	/**
	 * Getter
	 * @return string
	 */
	public function getRootDirectory() {
		return $this->rootDirectory;
	}

	/**
	 * Setter
	 * @param string $rootDirectory
	 */
	public function setRootDirectory($rootDirectory) {
		$this->rootDirectory = $rootDirectory;
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getRootTestDirectory() {
		return $this->rootTestDirectory;
	}

	/**
	 * Setter
	 * @param string $rootTestDirectory
	 */
	public function setRootTestDirectory($rootTestDirectory) {
		$this->rootTestDirectory = $rootTestDirectory;
	}


}