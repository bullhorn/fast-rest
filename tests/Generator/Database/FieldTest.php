<?php
namespace Tests\Generator\Database\Field;

use Bullhorn\FastRest\Generator\Database\Field;
use Bullhorn\FastRest\UnitTestHelper\Base;


class FieldTest extends Base {

    /**
     * Returns a mock std class object
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\stdClass
     */
    public function getMockStdClass() {
        $mockStdClass = $this->getMockBuilder(\stdClass::class)
            ->setMethods([])
            ->getMock();

        return $mockStdClass;
    }

    /**
     * To be used when testing constructor
     *
     * @return Field
     */
    private function constructorStuff($type, $key = "PRI", $extra = "auto_increment", $null = "YES", $comment = "comment") {
        //ARRANGE
        $field = "Field";

        $mockStdClass = $this->getMockStdClass();
        $mockStdClass->Field = $field;
        $mockStdClass->Key = $key;
        $mockStdClass->Extra = $extra;
        $mockStdClass->Type = $type;
        $mockStdClass->Null = $null;

        $otherMockStdClass = $this->getMockStdClass();
        $otherMockStdClass->COLUMN_COMMENT = $comment;

        $table = "tableName";

        //ACT
        $result = new Field($mockStdClass, $table, $otherMockStdClass);

        //ASSERT
        $this->assertEquals($field, $result->getName());
        if($key == "PRI") {
            $this->assertTrue($result->isPrimary());
        } else {
            $this->assertFalse($result->isPrimary());
        }
        if($extra == "auto_increment") {
            $this->assertTrue($result->isAutoIncrementing());
        } else {
            $this->assertFalse($result->isAutoIncrementing());
        }
        if($null == "YES") {
            $this->assertTrue($result->isNullable());
        } else {
            $this->assertFalse($result->isNullable());
        }
        if($comment == "") {
            $this->assertEquals(ucfirst($field), $result->getDescription());
        } else {
            $this->assertEquals($comment, $result->getDescription());
        }
        $this->assertEquals($field, $result->getName());

        return $result;
    }

    /**
     * Test Field::__construct
     *
     * @return void
     */
    public function testConstructor_tinyIntFieldType() {
        //ARRANGE
        $field = "tinyint(1)";

        //ACT
        $result = $this->constructorStuff($field);

        //ASSERT
        $this->assertEquals("bool", $result->getType());
        $this->assertEquals("boolean", $result->getSwaggerType());
        $this->assertEquals("", $result->getSwaggerFormat());
    }

    /**
     * Test Field::__construct
     *
     * @return void
     */
    public function testConstructor_unknownFieldType() {
        //ARRANGE
        $field = "unknown";

        //ACT
        $result = $this->constructorStuff($field);

        //ASSERT
        $this->assertEquals("string", $result->getType());
        $this->assertEquals("string", $result->getSwaggerType());
        $this->assertEquals("", $result->getSwaggerFormat());
    }

    /**
     * Test Field::__construct
     *
     * @return void
     */
    public function testConstructor_intFieldType() {
        //ARRANGE
        $field = "int(11)";

        //ACT
        $result = $this->constructorStuff($field);

        //ASSERT
        $this->assertEquals("int", $result->getType());
        $this->assertEquals("integer", $result->getSwaggerType());
        $this->assertEquals("int32", $result->getSwaggerFormat());
    }

    /**
     * Test Field::__construct
     *
     * @return void
     */
    public function testConstructor_varcharFieldType() {
        //ARRANGE
        $length = 32;
        $field = "varchar(" . $length . ")";

        //ACT
        $result = $this->constructorStuff($field);

        //ASSERT
        $this->assertEquals($length, $result->getLength());

        $this->assertEquals("string", $result->getType());
        $this->assertEquals("string", $result->getSwaggerType());
        $this->assertEquals("", $result->getSwaggerFormat());
    }

    /**
     * Test Field::__construct
     *
     * @return void
     */
    public function testConstructor_decimalFieldType() {
        //ARRANGE
        $field = "decimal";

        //ACT
        $result = $this->constructorStuff($field);

        //ASSERT
        $this->assertEquals("double", $result->getType());
        $this->assertEquals("number", $result->getSwaggerType());
        $this->assertEquals("double", $result->getSwaggerFormat());
    }

    /**
     * Test Field::__construct
     *
     * @return void
     */
    public function testConstructor_floatFieldType() {
        //ARRANGE
        $field = "float";

        //ACT
        $result = $this->constructorStuff($field);

        //ASSERT
        $this->assertEquals("double", $result->getType());
        $this->assertEquals("number", $result->getSwaggerType());
        $this->assertEquals("double", $result->getSwaggerFormat());
    }

    /**
     * Test Field::__construct
     *
     * @return void
     */
    public function testConstructor_enumFieldType() {
        //ARRANGE
        $enums = "'1','2','3'";
        $field = "enum(" . $enums . ")";

        //ACT
        $result = $this->constructorStuff($field);

        //ASSERT
        $this->assertEquals(explode(',', str_replace("'", "", $enums)), $result->getEnumOptions());

        $this->assertEquals("string", $result->getType());
        $this->assertEquals("string", $result->getSwaggerType());
        $this->assertEquals("", $result->getSwaggerFormat());
    }

    /**
     * Test Field::__construct
     *
     * @return void
     */
    public function testConstructor_datetimeFieldType() {
        //ARRANGE
        $field = "datetime";

        //ACT
        $result = $this->constructorStuff($field);

        //ASSERT
        $this->assertEquals("DateTime", $result->getType());
        $this->assertEquals("string", $result->getSwaggerType());
        $this->assertEquals("datetime", $result->getSwaggerFormat());
    }

    /**
     * Test Field::__construct
     *
     * @return void
     */
    public function testConstructor_timestampFieldType() {
        //ARRANGE
        $field = "timestamp";

        //ACT
        $result = $this->constructorStuff($field);

        //ASSERT
        $this->assertEquals("DateTime", $result->getType());
        $this->assertEquals("string", $result->getSwaggerType());
        $this->assertEquals("datetime", $result->getSwaggerFormat());
    }

    /**
     * Test Field::__construct
     *
     * @return void
     */
    public function testConstructor_dateFieldType() {
        //ARRANGE
        $field = "date";

        //ACT
        $result = $this->constructorStuff($field);

        //ASSERT
        $this->assertEquals("Date", $result->getType());
        $this->assertEquals("string", $result->getSwaggerType());
        $this->assertEquals("date", $result->getSwaggerFormat());
    }
}
