<?php
namespace Tests\Api\Controllers;
use Bullhorn\FastRest\Api\Controllers\Base;
use Bullhorn\FastRest\Api\Services\Output\OutputInterface;
use Bullhorn\FastRest\UnitTestHelper\Base as UnitTestHelperBase;
use Phalcon\Http\ResponseInterface;
use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use Phalcon\Http\Request\Exception;
use Phalcon\Mvc\ViewInterface;

class BaseTest extends UnitTestHelperBase {
	/**
	 * getBase
	 * @param string[] $extraMethods
	 * @return Base|PHPUnit_Framework_MockObject_MockObject
	 */
	private function getBase($extraMethods=[]) {
		return $this->getMockBuilder(Base::class)
			->setMethods(array_merge(['generateEntity', 'getQueryWhiteList', 'validateLogin'], $extraMethods))
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * testAfterExecuteRoute_invalidOutput
	 * @return void
	 * @expectedException \Exception
	 * @expectedExceptionMethod The Output must implement: Bullhorn\FastRest\Api\Services\Output\OutputInterface
	 */
	public function testAfterExecuteRoute_invalidOutput() {
		//Arrange
		$this->getDi()->set(Base::DI_NAME_OUTPUT, new stdClass());
		/** @type PHPUnit_Framework_MockObject_MockObject|Base $base */
		$base = $this->getBase();
		//Act
		$base->afterExecuteRoute();
	}

	/**
	 * testAfterExecuteRoute_invalidOutput
	 * @return void
	 */
	public function testAfterExecuteRoute_valid() {
		//Arrange
		$statusCode = 123;
		$errorMessage = 'errorMessage';
		$errorStatusCode = 124;
		$errors = [
			new Exception($errorMessage, $errorStatusCode)
		];
		$object = new stdClass();
		$object->statusCode = $statusCode;
		$response = $this->getMockBuilder(ResponseInterface::class)->getMock();

		$response->expects($this->once())
			->method('setStatusCode')
			->with($errorStatusCode, 'Check Document Body For More Details');

		$view = $this->getMockBuilder(ViewInterface::class)->getMock();
		$view->expects($this->once())
			->method('disable');

		$output = $this->getMockBuilder(OutputInterface::class)->getMock();
		$output->expects($this->once())
			->method('output')
			->with($object, $response);
		$this->getDi()->set(Base::DI_NAME_OUTPUT, $output);
		/** @type PHPUnit_Framework_MockObject_MockObject|Base $base */
		$base = $this->getBase(['getOutputObject', 'getErrors', 'getStatusCode']);

		$base->expects($this->once())
			->method('getOutputObject')
			->willReturn($object);

		$base->expects($this->once())
			->method('getErrors')
			->willReturn($errors);

		$base->expects($this->once())
			->method('getStatusCode')
			->willReturn($statusCode);

		$base->response = $response;
		$base->view = $view;
		//Act
		$base->afterExecuteRoute();
	}
}