<?php
namespace Tests\Api\Services\Config;
use Bullhorn\FastRest\Api\Services\Config\ApiRoutes;
use Bullhorn\FastRest\UnitTestHelper\Base;
use Phalcon\Mvc\Router;
use PHPUnit_Framework_MockObject_MockObject;

class ApiRoutesTest extends Base {
	public function testAddRoutes() {
		//Arrange
		$apiControllerRootNamespace = 'apiControllerRootNamespace';
		$apiRootUrl = 'apiRootUrl';

		/** @type PHPUnit_Framework_MockObject_MockObject|Router $router */
		$router = $this->getMockBuilder(Router::class)->getMock();

		$router->expects($this->once())
			->method('add')
			->with(
				'/'.$apiRootUrl.'(\/?)',
				array(
					'namespace'  => $apiControllerRootNamespace,
					'controller' => 'Index',
					'action'     => 'index'
				)
			);

		$router->expects($this->exactly(2))
			->method('addGet')
			->withConsecutive(
				[
					'/'.$apiRootUrl.'/:controller/:params',
					array(
						'namespace'  => $apiControllerRootNamespace,
						'controller' => 1,
						'action'     => 'show',
						'params'     => 2
					)
				],
				[
					'/'.$apiRootUrl.'/:controller',
					array(
						'namespace'  => $apiControllerRootNamespace,
						'controller' => 1,
						'action'     => 'index'
					)
				]
			);

		$router->expects($this->once())
			->method('addPost')
			->with(
				'/'.$apiRootUrl.'/:controller',
				array(
					'namespace'  => $apiControllerRootNamespace,
					'controller' => 1,
					'action'     => 'create'
				)
			);

		$router->expects($this->exactly(2))
			->method('addOptions')
			->withConsecutive(
				[
					'/'.$apiRootUrl.'/:controller',
					array(
						'namespace'  => $apiControllerRootNamespace,
						'controller' => 1,
						'action'     => 'options'
					)
				],
				[
					'/'.$apiRootUrl.'/:controller/:params',
					array(
						'namespace'  => $apiControllerRootNamespace,
						'controller' => 1,
						'action'     => 'options'
					)
				]
			);

		$router->expects($this->once())
			->method('addDelete')
			->with(
				'/'.$apiRootUrl.'/:controller/:params',
				array(
					'namespace'  => $apiControllerRootNamespace,
					'controller' => 1,
					'action'     => 'delete',
					'params'     => 2
				)
			);

		$router->expects($this->once())
			->method('addPut')
			->with(
				'/'.$apiRootUrl.'/:controller/:params',
				array(
					'namespace'  => $apiControllerRootNamespace,
					'controller' => 1,
					'action'     => 'update',
					'params'     => 2
				)
			);

		//Act
		$apiRoutes = new ApiRoutes($apiRootUrl, $apiControllerRootNamespace);
		$apiRoutes->addRoutes($router);

	}

}