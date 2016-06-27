<?php
namespace Tests\Api\Services\DataTransform;
use Bullhorn\FastRest\Api\Services\DataTransform\Base;
use Bullhorn\FastRest\UnitTestHelper\Base as UnitTestBase;
class BaseTest extends UnitTestBase {
    /**
     * testGetParam
     * @return void
     */
    public function testGetParam() {
        //Arrange
        $params = (object)[
            'User' => (object)[
                'abc' => 'delta'
            ]
        ];
        /** @var \PHPUnit_Framework_MockObject_MockObject|Base $base */
        $base = $this->getMockBuilder(Base::class)
            ->setMethods(['transform'])
            ->setConstructorArgs([$params])
            ->getMock();

        //Act
        $actual = $base->getParam('User.abc');

        //Assert
        $this->assertSame('delta', $actual);
    }

}