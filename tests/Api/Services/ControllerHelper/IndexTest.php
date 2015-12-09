<?php
namespace Tests\Api\Services\ControllerHelper;

use Bullhorn\FastRest\Api\Models\ApiInterface;
use Bullhorn\FastRest\Api\Services\ControllerHelper\Index;
use Bullhorn\FastRest\Api\Services\Database\CriteriaHelper;
use Bullhorn\FastRest\UnitTestHelper\Base;
use Phalcon\Http\Request\Exception;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Mvc\Model\Criteria;

class IndexTest extends Base {
    /** @var  \PHPUnit_Framework_MockObject_MockObject|Criteria */
    private $criteriaMock;

    /**
     * {REPLACE_ME!}
     * @return \PHPUnit_Framework_MockObject_MockObject|ApiInterface
     */
    public function getMockApiInterface() {
        $mockApiInterface = $this->getMockBuilder(ApiInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        return $mockApiInterface;
    }

    /**
     * {REPLACE_ME!}
     * @return \PHPUnit_Framework_MockObject_MockObject|Criteria
     */
    public function getMockCriteria() {
        if(is_null($this->criteriaMock)) {
            $criteriaMock = $this->getMockBuilder(Criteria::class)
                ->disableOriginalConstructor()
                ->setMethods(['andWhere'])
                ->getMock();
            $this->criteriaMock = $criteriaMock;
        }
        return $this->criteriaMock;
    }

    /**
     * {REPLACE_ME!}
     *
     * @return \PHPUnit_Framework_MockObject_MockBuilder|CriteriaHelper
     */
    public function getMockCriteriaHelper() {
        $criteriaHelperMock = $this->getMockBuilder(CriteriaHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParamId', 'andWhere'])
            ->getMock();
        $criteriaHelperMock->expects($this->any())
            ->method('getParamId')
            ->willReturn(1);

        return $criteriaHelperMock;
    }

    /**
     * {REPLACE_ME!}
     * @return \PHPUnit_Framework_MockObject_MockObject|Index
     */
    public function getMockIndex() {
        $mockIndex = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCriteriaHelper', 'getCriteria'])
            ->getMock();
        $criteriaMock = $this->getMockCriteria();
        $mockIndex->expects($this->any())
            ->method('getCriteria')
            ->willReturn($criteriaMock);
        $criteriaHelperMock = $this->getMockCriteriaHelper();
        $mockIndex->expects($this->any())
            ->method('getCriteriaHelper')
            ->willReturn($criteriaHelperMock);

        return $mockIndex;
    }

    /**
     * Test Index::addSearchField()
     *
     * @return void
     */
    public function testAddSearchField() {
        //ARRANGE
        $mockApiInterface = $this->getMockApiInterface();

        $mockIndex = $this->getMockIndex();
        $reflectionClass = new \ReflectionClass(Index::class);
        $reflectionMethod = $reflectionClass->getMethod('addSearchField');
        $reflectionMethod->setAccessible(TRUE);

        $mockApiInterface->expects($this->once())
            ->method('writeAttribute')
            ->with('name', 'value');
        $mockApiInterface->expects($this->once())
            ->method('readAttribute')
            ->willReturn('value');
        $this->getMockCriteria()->expects($this->once())
            ->method('andWhere')
            ->with('alias.name=?1', [1 => 'value']);

        //ACT
        $reflectionMethod->invoke($mockIndex, "name", "value", $mockApiInterface, "alias");

    }

    /**
     * Test Index::addSearchField()
     *
     * @return void
     */
    public function testAddSearchField_greaterThanDate() {
        //ARRANGE
        $mockApiInterface = $this->getMockApiInterface();

        $mockIndex = $this->getMockIndex();
        $reflectionClass = new \ReflectionClass(Index::class);
        $reflectionMethod = $reflectionClass->getMethod('addSearchField');
        $reflectionMethod->setAccessible(TRUE);

        $mockApiInterface->expects($this->once())
            ->method('writeAttribute')
            ->with('name', 'value');
        $mockApiInterface->expects($this->once())
            ->method('getFieldTypes')
            ->willReturn(['name' => ApiInterface::FIELD_TYPE_DATE]);
        $mockApiInterface->expects($this->once())
            ->method('readAttribute')
            ->willReturn('value');
        $this->getMockCriteria()->expects($this->once())
            ->method('andWhere')
            ->with('alias.name>=?1', [1 => 'value']);

        //ACT
        $reflectionMethod->invoke($mockIndex, "name>", "value", $mockApiInterface, "alias");
    }

    /**
     * Test Index::addSearchField()
     *
     * @return void
     */
    public function testAddSearchField_greaterThanDateTime() {
        //ARRANGE
        $mockApiInterface = $this->getMockApiInterface();

        $mockIndex = $this->getMockIndex();
        $reflectionClass = new \ReflectionClass(Index::class);
        $reflectionMethod = $reflectionClass->getMethod('addSearchField');
        $reflectionMethod->setAccessible(TRUE);

        $mockApiInterface->expects($this->once())
            ->method('writeAttribute')
            ->with('name', 'value');
        $mockApiInterface->expects($this->once())
            ->method('getFieldTypes')
            ->willReturn(['name' => ApiInterface::FIELD_TYPE_DATE_TIME]);
        $mockApiInterface->expects($this->once())
            ->method('readAttribute')
            ->willReturn('value');
        $this->getMockCriteria()->expects($this->once())
            ->method('andWhere')
            ->with('alias.name>=?1', [1 => 'value']);

        //ACT
        $reflectionMethod->invoke($mockIndex, "name>", "value", $mockApiInterface, "alias");
    }

    /**
     * Test Index::addSearchField()
     *
     * @return void
     */
    public function testAddSearchField_lessThanDouble() {
        //ARRANGE
        $mockApiInterface = $this->getMockApiInterface();

        $mockIndex = $this->getMockIndex();
        $reflectionClass = new \ReflectionClass(Index::class);
        $reflectionMethod = $reflectionClass->getMethod('addSearchField');
        $reflectionMethod->setAccessible(TRUE);

        $mockApiInterface->expects($this->once())
            ->method('writeAttribute')
            ->with('name', 'value');
        $mockApiInterface->expects($this->once())
            ->method('getFieldTypes')
            ->willReturn(['name' => ApiInterface::FIELD_TYPE_DOUBLE]);
        $mockApiInterface->expects($this->once())
            ->method('readAttribute')
            ->willReturn('value');
        $this->getMockCriteria()->expects($this->once())
            ->method('andWhere')
            ->with('alias.name<=?1', [1 => 'value']);

        //ACT
        $reflectionMethod->invoke($mockIndex, "name<", "value", $mockApiInterface, "alias");
    }

    /**
     * Test Index::addSearchField()
     *
     * @return void
     */
    public function testAddSearchField_lessThanInt() {
        //ARRANGE
        $mockApiInterface = $this->getMockApiInterface();

        $mockIndex = $this->getMockIndex();
        $reflectionClass = new \ReflectionClass(Index::class);
        $reflectionMethod = $reflectionClass->getMethod('addSearchField');
        $reflectionMethod->setAccessible(TRUE);

        $mockApiInterface->expects($this->once())
            ->method('writeAttribute')
            ->with('name', 'value');
        $mockApiInterface->expects($this->once())
            ->method('getFieldTypes')
            ->willReturn(['name' => ApiInterface::FIELD_TYPE_INT]);
        $mockApiInterface->expects($this->once())
            ->method('readAttribute')
            ->willReturn('value');
        $this->getMockCriteria()->expects($this->once())
            ->method('andWhere')
            ->with('alias.name<=?1', [1 => 'value']);

        //ACT
        $reflectionMethod->invoke($mockIndex, "name<", "value", $mockApiInterface, "alias");
    }

    /**
     * Test Index::addSearchField()
     *
     * @return void
     */
    public function testAddSearchField_lessThanString() {
        //ARRANGE
        $mockIndex = $this->getMockIndex();
        $reflectionClass = new \ReflectionClass(Index::class);
        $reflectionMethod = $reflectionClass->getMethod('addSearchField');
        $reflectionMethod->setAccessible(TRUE);

        $mockApiInterface = $this->getMockApiInterface();
        $mockApiInterface->expects($this->once())
            ->method('getFieldTypes')
            ->willReturn(['name' => ApiInterface::FIELD_TYPE_STRING]);

        //ACT
        try {
            $reflectionMethod->invoke($mockIndex, "name<", "value", $mockApiInterface, "alias");
            $this->fail('An Exception was expected');
        } catch(Exception $e) {
            $this->assertEquals('Cannot perform <= search on any fields that are not in Date, DateTime, double, int: name has a type of string', $e->getMessage());
        }
    }

    /**
     * Test Index::addSearchField()
     *
     * @return void
     */
    public function testAddSoftDelete_oneValidBehavior() {
        //ARRANGE
        /** @var ApiInterface|\PHPUnit_Framework_MockObject_MockObject $apiInterfaceExtensionMock */
        $apiInterfaceExtensionMock = $this->getMockBuilder(ApiInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $apiInterfaceExtensionMock->expects($this->once())
            ->method('getAllBehaviorsByClassName')
            ->willReturn([new SoftDelete()]);

        $criteriaHelperMock = $this->getMockCriteriaHelper();

        $mockIndex = $this->getMockIndex();
        $mockIndex->expects($this->once())
            ->method("getCriteriaHelper")
            ->willReturn($criteriaHelperMock);

        //ACT
        $mockIndex->addSoftDelete($apiInterfaceExtensionMock, "mockApiInterface");
    }

    /**
     * Test Index::addSearchField()
     *
     * @return void
     */
    public function testAddSoftDelete_multipleValidBehaviors() {
        //ARRANGE
        /** @var ApiInterface|\PHPUnit_Framework_MockObject_MockObject $apiInterfaceExtensionMock */
        $apiInterfaceExtensionMock = $this->getMockBuilder(ApiInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $apiInterfaceExtensionMock->expects($this->once())
            ->method('getAllBehaviorsByClassName')
            ->willReturn([new SoftDelete(), new SoftDelete(), new SoftDelete()]);

        $criteriaHelperMock = $this->getMockCriteriaHelper();

        $mockIndex = $this->getMockIndex();
        $mockIndex->expects($this->exactly(3))
            ->method("getCriteriaHelper")
            ->willReturn($criteriaHelperMock);

        //ACT
        $mockIndex->addSoftDelete($apiInterfaceExtensionMock, "mockApiInterface");
    }
}
