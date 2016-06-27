<?php
namespace Tests\Generator;
use Bullhorn\FastRest\Generator\Database\Field;
use Bullhorn\FastRest\Generator\ModelBuilder;
use Bullhorn\FastRest\UnitTestHelper\Base;
use ReflectionClass;

class ModelBuilderTest extends Base {
    /**
     * testBuildSetterBoolContent
     * @return void
     */
    public function testBuildSetterBoolContent() {
        //Arrange
        $modelBuilder = $this->getMockBuilder(ModelBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['nonexistent'])
            ->getMock();

        $field = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $field->expects($this->any())
            ->method('getShortName')
            ->willReturn('ShortName');

        $content = 'The Beginning Content';

        //Act
        $reflectionClass = new ReflectionClass(ModelBuilder::class);
        $reflectionMethod = $reflectionClass->getMethod('buildSetterBoolContent');
        $reflectionMethod->setAccessible(true);
        $actual = $reflectionMethod->invoke($modelBuilder, $field, $content);

        //Assert
        $expected = 'The Beginning Content		$preFilterValue = $ShortName;
        if(!is_null($preFilterValue)) {
            $ShortName = $this->getFilter()->sanitize($ShortName, \'boolean\');
            if(is_null($ShortName)) {
                throw new \InvalidArgumentException(\'Expected Type of boolean (1, true, on, yes, 0, false, off, no, ""), Invalid Value: \'.$preFilterValue);
            }
        } else {
            $ShortName = $preFilterValue;
        }';
        $this->assertEquals(str_replace("\r\n","\n",trim($expected)), str_replace("\r\n","\n",trim($actual)));
    }
}