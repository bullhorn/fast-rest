<?php
namespace Tests\Api\Services\DataValidation;
use Bullhorn\FastRest\Api\Services\DataValidation\Assert;
use Bullhorn\FastRest\UnitTestHelper\Base;

class AssertTest extends Base {
	/**
	 * testIsArray_notArray
	 * @param mixed $value
	 * @return void
	 * @dataProvider notArrayProvider
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMethod Must be Array
	 */
	public function testIsArray_notArray($value) {
		Assert::isArray($value);
	}

	/**
	 * testIsArray_valid
	 * @return void
	 */
	public function testIsArray_valid() {
		$expected = ['a'];
		$actual = Assert::isArray($expected);
		$this->assertSame($expected, $actual);
	}

	/**
	 * notArrayProvider
	 * @return array
	 */
	public function notArrayProvider() {
		return [
			[''],
			[1],
			[null],
			[false],
			[new \stdClass()]
		];
	}
}