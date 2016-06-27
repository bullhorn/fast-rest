<?php
namespace Tests\Api\Services\ControllerHelper;
use Bullhorn\FastRest\Api\Services\ControllerHelper\SplitHelper;
use Bullhorn\FastRest\UnitTestHelper\Base;

class SplitHelperTest extends Base {

    /**
     * testConvert_upperCase
     * @return void
     * @throws \Phalcon\Http\Request\Exception
     */
    public function testConvert_upperCase() {
        //Arrange
        $splitHelper = new SplitHelper('.');
        $input = [
            'user.abc'=>'alpha',
            'User.abd'=>'delta'
        ];
        //Act
        $actual = $splitHelper->convert($input);

        //Assert
        $expected = (object)[
            'User' => (object)[
                'abc' => 'alpha',
                'abd' => 'delta'
            ]
        ];
        $this->assertEquals($expected, $actual);

    }

}