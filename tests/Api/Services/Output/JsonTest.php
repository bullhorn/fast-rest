<?php
namespace Tests\Api\Services\Output;

use Bullhorn\FastRest\Api\Services\Output\Json;
use Bullhorn\FastRest\UnitTestHelper\Base;
use \Phalcon\Http\Response;

class JsonTest extends Base {

	/**
	 * Test the output is called correctly
	 * @return void
	 */
	public function testOutput() {
		$object = new \stdClass();
		$json = new Json();

		/** @var Response|\PHPUnit_Framework_MockObject_MockObject $response */
		$response = $this->getMockBuilder(Response::class)
											->setMethods(array('setContentType', 'setJsonContent', 'send'))
											->getMock();

		$response->expects($this->once())
				  ->method('setContentType')
				  ->with($this->equalTo('application/json'));

		$response->expects($this->once())
				  ->method('setJsonContent')
				  ->with($this->equalTo($object));

		$response->expects($this->once())
				  ->method('setJsonContent')
				  ->with();

		$json->output($object, $response);
	}
}
