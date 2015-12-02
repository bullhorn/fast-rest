<?php
namespace Bullhorn\FastRest\UnitTestHelper;
use Bullhorn\FastRest\DependencyInjection;
use Phalcon\Di;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\ServiceInterface;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\Query\Builder;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

abstract class Base extends PHPUnit_Framework_TestCase implements InjectionAwareInterface {
	use DependencyInjection;
	const PHPUNIT_RUNNING = 'PHPUNIT_RUNNING';

	/** @type  string */
	private $connectionService = 'db';
	/** @type  string */
	private $modelSubNamespace;
	/** @var  string */
	public $phalconHelperNamespace = '';
	/** @var  ServiceInterface[] */
	private $startingServices;

	/**
	 * Getter
	 * @return string
	 */
	public function getPhalconHelperNamespace() {
		return $this->phalconHelperNamespace;
	}

	/**
	 * Setter
	 * @param string $phalconHelperNamespace
	 */
	public function setPhalconHelperNamespace($phalconHelperNamespace) {
		$this->phalconHelperNamespace = $phalconHelperNamespace;
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
	 * gets the db connection service
	 * @return string
	 */
	public function getConnectionService() {
		return $this->connectionService;
	}

	/**
	 * Sets the db connection service
	 * @param string $connectionService
	 */
	public function setConnectionService($connectionService) {
		$this->connectionService = $connectionService;
	}

	/**
	 * Tear down.
	 * @return void
	 */
	public function tearDown() {
		$this->resetDi();
	}

	/**
	 * Setup
	 * @return void
	 */
	protected function setUp() {
		if(!defined(self::PHPUNIT_RUNNING)) {
			define(self::PHPUNIT_RUNNING, true);
		}
		$_POST = [];
		$_GET = [];
		$this->setStartingServices($this->getDi()->getServices());
		$dbMock = new MockDbAdapter([]);
		$dbMock->setPhalconHelperNamespace($this->getPhalconHelperNamespace());
		$dbMock->setModelSubNamespace($this->getModelSubNamespace());
		$this->getDI()->set($this->getConnectionService(), $dbMock);
	}

	/**
	 * resetDi
	 * @return void
	 */
	private function resetDi() {
		if(is_null($this->getStartingServices())) {
			throw new \Exception('Starting Services was not set');
		}
		$reflectionClass = new ReflectionClass(FactoryDefault::class);
		$reflectionProperty = $reflectionClass->getProperty('_services');
		$reflectionProperty->setAccessible(true);
		$reflectionProperty->setValue($this->getDi(), $this->getStartingServices());
	}

	/**
	 * validatePhql
	 * @param string    $modelName
	 * @param int|array $parameters
	 * @return mixed
	 */
	protected function validateFindPhql($modelName, $parameters) {
		$this->validatePhqlGenerateModelFactory();

		/** @type Model $modelFactory */
		$modelFactory = new $modelName();
		$modelFactory->find($parameters);

		return $parameters;
	}

	/**
	 * validatePhql
	 * @param string    $modelName
	 * @param int|array $parameters
	 * @return mixed
	 */
	protected function validateFindFirstPhql($modelName, $parameters) {
		$this->validatePhqlGenerateModelFactory();

		/** @type Model $modelFactory */
		$modelFactory = new $modelName();
		$modelFactory->findFirst($parameters);

		return $parameters;
	}

	/**
	 * validatePhqlGenerateModelFactory
	 * @return void
	 */
	private function validatePhqlGenerateModelFactory() {
		$modelsManager = $this->getMockBuilder(Manager::class)
			->setMethods(['createBuilder'])
			->getMock();

		$modelsManager->expects($this->once())
			->method('createBuilder')
			->will(
				$this->returnCallback(
					function ($params) {
						return $this->validatePhqlGenerateBuilder($params);
					}
				)
			);
		$this->getDi()->remove('modelsManager');
		$this->getDi()->setShared('modelsManager', $modelsManager);
	}

	/**
	 * validatePhqlGenerateBuilder
	 * @param mixed $params
	 * @return Builder|PHPUnit_Framework_MockObject_MockObject
	 */
	private function validatePhqlGenerateBuilder($params) {
		/** @type Builder|PHPUnit_Framework_MockObject_MockObject $builder */
		$builder = $this->getMockBuilder(Builder::class)
			->setConstructorArgs([$params])
			->setMethods(['getQuery'])
			->getMock();

		$builder->expects($this->once())
			->method('getQuery')
			->will(
				$this->returnCallback(
					function () use ($builder) {
						return $this->validatePhqlGenerateQuery($builder);
					}
				)
			);
		return $builder;
	}

	/**
	 * validatePhqlGenerateQuery
	 * @param PHPUnit_Framework_MockObject_MockObject|Builder $builder
	 * @return Query|PHPUnit_Framework_MockObject_MockObject
	 */
	private function validatePhqlGenerateQuery(PHPUnit_Framework_MockObject_MockObject $builder) {
		/** @type PHPUnit_Framework_MockObject_MockObject|Query $query */
		$query = $this->getMockBuilder(Query::class)
			->setConstructorArgs([$builder->getPhql(), $this->getDi()])
			->setMethods(['execute'])
			->getMock();

		$query->expects($this->once())
			->method('execute')
			->will(
				$this->returnCallback(
					function () use ($query) {
						return $query->parse();
					}
				)
			);

		return $query;
	}

	/**
	 * Getter
	 * @return Di\ServiceInterface[]
	 */
	private function getStartingServices() {
		return $this->startingServices;
	}

	/**
	 * Setter
	 * @param Di\ServiceInterface[] $startingServices
	 * @return Base
	 */
	private function setStartingServices(array $startingServices) {
		$this->startingServices = $startingServices;
		return $this;
	}


}