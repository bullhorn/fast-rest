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

	/**
	 * testIsFloat_negativeDecimal
	 * @return void
	 */
	public function testIsFloat_negativeDecimal() {
		$actual = Assert::isFloat('-124.23');
		$this->assertSame(-124.23, $actual);
	}

	/**
	 * testIsFloat_negativeDecimal
	 * @return void
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Must be Float: 124.
	 */
	public function testIsFloat_invalidDecimal() {
		Assert::isFloat('124.');
	}

	/**
	 * testIsFloat_negativeDecimal
	 * @return void
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Must be Float: Array
	 */
	public function testIsFloat_array() {
		Assert::isFloat(['a']);
	}

}