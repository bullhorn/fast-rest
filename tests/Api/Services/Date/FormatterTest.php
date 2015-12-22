<?php
namespace Tests\Api\Services\Date;


use Bullhorn\FastRest\Api\Services\Date\Formatter;
use Bullhorn\FastRest\UnitTestHelper\Base;

class FormatterTest extends Base {

	public function testGetDefault_returnsCachedSelf() {
		//arrange
		$originalFormatter = Formatter::getDefault();

		//act
		$cachedFormatter = Formatter::getDefault();

		//assert
		$this->assertSame($originalFormatter, $cachedFormatter);
	}
}