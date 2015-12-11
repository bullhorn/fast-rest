<?php
namespace Tests;

use Bullhorn\FastRest\UnitTestHelper\Base as UnitTestHelperBase;
use Base;
use Bullhorn\FastRest\UnitTestHelper\ClassPropertyTest;

class BaseTest extends UnitTestHelperBase {

    /**
     * Tests the getters, setters, and adders for all properties
     * @return VOID
     */
    public function testProperties() {
        $objectFactory = function () {
            return new Base();
        };
        $tester = new ClassPropertyTest($objectFactory, $this);
        $tester->testAllPropertiesGettersAndSetters();
    }

    /**
     * Tests the getters, setters, and adders for all properties
     * @return VOID
     */
    public function testProperties_abstractClass() {
        $objectFactory = function () {
            $base = $this->getMockBuilder(Base::class)
                ->setMethods(['listOfAbstractMethods'])
                ->getMock();
            return $base;
        };
        $tester = new ClassPropertyTest($objectFactory, $this, Base::class);
        $tester->testAllPropertiesGettersAndSetters();
    }

}