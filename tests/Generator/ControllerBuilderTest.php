<?php
namespace Tests\Generator;
use Bullhorn\FastRest\Generator\ControllerBuilder;
use Bullhorn\FastRest\Generator\Object;
use Bullhorn\FastRest\UnitTestHelper\Base;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionClass;

class ControllerBuilderTest extends Base {
	public function test__toString() {
		//Arrange
		$expected = 'theString';
		$object = $this->getMockBuilder(Object\Index::class)
			->disableOriginalConstructor()
			->getMock();

		$object->expects($this->once())
			->method('toString')
			->willReturn($expected);

		/** @type PHPUnit_Framework_MockObject_MockObject|ControllerBuilder $controllerBuilder */
		$controllerBuilder = $this->getMockBuilder(ControllerBuilder::class)
			->setMethods(['nonexistent'])
			->disableOriginalConstructor()
			->getMock();

		$reflectionClass = new ReflectionClass(ControllerBuilder::class);
		$reflectionMethod = $reflectionClass->getMethod('setObject');
		$reflectionMethod->setAccessible(true);
		$reflectionMethod->invoke($controllerBuilder, $object);

		//Act
		$actual = $controllerBuilder->__toString();

		//Assert
		$this->assertSame($expected, $actual);
	}
}