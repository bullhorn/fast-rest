<?php
namespace Tests\Api\Services\Date;


use Bullhorn\FastRest\Api\Services\Date\Formatter;
use Bullhorn\FastRest\UnitTestHelper\Base;

use InvalidArgumentException;

class FormatterTest extends Base {

    /**
     * testGetDefault_returnsCachedSelf
     * @return void
     */
	public function testGetDefault_returnsCachedSelf() {
		//arrange
		$originalFormatter = Formatter::getDefault();

		//act
		$cachedFormatter = Formatter::getDefault();

		//assert
		$this->assertSame($originalFormatter, $cachedFormatter);
	}

    /**
     * testReset
     * @return void
     */
    public function testReset() {
        //Arrange
        $formatter = Formatter::getDefault();
        $formatter->setCurrentFormat(Formatter::DATE_FORMAT_UK);
        $formatter->setCurrentFormat(Formatter::DATE_FORMAT_EURO);
        $formatter->setWeekOffset(2);

        //Act
        $formatter->reset();

        //Assert
        $this->assertEquals(Formatter::DATE_FORMAT_US, $formatter->getCurrentFormat());
        $this->assertEquals(0, $formatter->getWeekOffset());
        $formatter->revertFormat();
        $this->assertEquals(Formatter::DATE_FORMAT_US, $formatter->getCurrentFormat());
    }

    /**
     * testCurrentFormat_setAndRevertDuplicateNonDefaultFormats
     *
     * @param string[] $formats
     * @param string   $expectedEndFormat
     *
     * @dataProvider duplicateFormats
     *
     * @return void
     */
    public function testSetCurrentFormat_setAndRevertDuplicateNonDefaultFormats($formats, $expectedEndFormat) {
        //arrange
        $dateFormatter = new Formatter();
        foreach($formats as $format){
            $dateFormatter->setCurrentFormat($format);
        }

        //act
        $dateFormatter->revertFormat();

        //assert
        $this->assertEquals($expectedEndFormat, $dateFormatter->getCurrentFormat());
    }

    /**
     * testSetCurrentFormat_invalidFormatSet
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid Date Format: some non sense, expected: d-m-Y, d/m/Y, m/d/Y
     *
     * @return void
     */
    public function testSetCurrentFormat_invalidFormatSet() {
        //arrange
        $dateFormatter = new Formatter();

        //act
        $dateFormatter->setCurrentFormat("some non sense");
    }

    /**
     * duplicateFormats
     * @return array
     */
    public function duplicateFormats() {
        return [
            [[Formatter::DATE_FORMAT_UK, Formatter::DATE_FORMAT_UK], Formatter::DATE_FORMAT_UK],
            [[Formatter::DATE_FORMAT_EURO, Formatter::DATE_FORMAT_EURO], Formatter::DATE_FORMAT_EURO],
            [[Formatter::DATE_FORMAT_US, Formatter::DATE_FORMAT_US], Formatter::DATE_FORMAT_US]
        ];
    }
}