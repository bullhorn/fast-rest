<?php
namespace Tests\Generator;

use Bullhorn\FastRest\Generator\PluralHelper;
use Bullhorn\FastRest\UnitTestHelper\Base;

class PluralHelperTest extends Base {
    /**
     * testPluralize_endInCh
     * @return void
     */
    public function testPluralize_endInCh() {
        //arrange
        $string = 'match';
        $pluralHelper = new PluralHelper();

        //act
        $actual = $pluralHelper->pluralize($string);

        //Assert
        $this->assertSame('matches', $actual);
    }

    /**
     * testPluralize_endInY
     * @return void
     */
    public function testPluralize_endInY() {
        //arrange
        $string = 'activity';
        $pluralHelper = new PluralHelper();

        //act
        $actual = $pluralHelper->pluralize($string);

        //Assert
        $this->assertSame('activities', $actual);
    }

    /**
     * testPluralize_otherWords
     * @return void
     */
    public function testPluralize_otherWords() {
        //arrange
        $string = 'fund';
        $pluralHelper = new PluralHelper();

        //act
        $actual = $pluralHelper->pluralize($string);

        //Assert
        $this->assertSame('funds', $actual);
    }
}