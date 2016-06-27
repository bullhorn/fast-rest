<?php
namespace Tests\Api\Controllers;

use Bullhorn\FastRest\Api\Controllers\Base;
use Bullhorn\FastRest\Api\Models\ApiInterface;
use Bullhorn\FastRest\Api\Models\ControllerModelInterface;
use Bullhorn\FastRest\Api\Services\ControllerHelper\Save;
use Bullhorn\FastRest\Api\Services\Output\OutputInterface;
use Bullhorn\FastRest\UnitTestHelper\Base as UnitTestHelperBase;
use Phalcon\Cache\Frontend\Output;
use Phalcon\Http\Request;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\ModelInterface;
use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use Phalcon\Http\Request\Exception;
use Phalcon\Mvc\ViewInterface;
use ReflectionClass;
use Bullhorn\FastRest\Api\Services\DataTransform\Base as DataTransformer;

class BaseTest extends UnitTestHelperBase {
    /**
     * getBase
     * @param string[] $extraMethods
     * @return Base|PHPUnit_Framework_MockObject_MockObject
     */
    private function getBase($extraMethods = []) {
        return $this->getMockBuilder(Base::class)
            ->setMethods(array_merge(['generateEntity', 'getQueryWhiteList', 'validateLogin'], $extraMethods))
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * testAfterExecuteRoute_invalidOutput
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMethod The Output must implement: Bullhorn\FastRest\Api\Services\Output\OutputInterface
     */
    public function testAfterExecuteRoute_invalidOutput() {
        //Arrange
        $this->getDi()->set(OutputInterface::DI_NAME, new stdClass());
        /** @type PHPUnit_Framework_MockObject_MockObject|Base $base */
        $base = $this->getBase();
        //Act
        $base->afterExecuteRoute();
    }

    /**
     * testAfterExecuteRoute_invalidOutput
     * @return void
     */
    public function testAfterExecuteRoute_valid() {
        //Arrange
        $statusCode = 123;
        $errorMessage = 'errorMessage';
        $errorStatusCode = 124;
        $errors = [
            new Exception($errorMessage, $errorStatusCode)
        ];
        $object = new stdClass();
        $object->statusCode = $statusCode;
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();

        $response->expects($this->once())
            ->method('setStatusCode')
            ->with($errorStatusCode, 'Check Document Body For More Details');

        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
        $view->expects($this->once())
            ->method('disable');

        $output = $this->getMockBuilder(OutputInterface::class)->getMock();
        $output->expects($this->once())
            ->method('output')
            ->with($object, $response);
        $this->getDi()->set(OutputInterface::DI_NAME, $output);
        /** @type PHPUnit_Framework_MockObject_MockObject|Base $base */
        $base = $this->getBase(['getOutputObject', 'getErrors', 'getStatusCode']);

        $base->expects($this->once())
            ->method('getOutputObject')
            ->willReturn($object);

        $base->expects($this->once())
            ->method('getErrors')
            ->willReturn($errors);

        $base->expects($this->once())
            ->method('getStatusCode')
            ->willReturn($statusCode);

        $base->response = $response;
        $base->view = $view;
        //Act
        $base->afterExecuteRoute();
    }

    /**
     * testSaveEntity
     * @return void
     */
    public function testSaveEntity() {
        //Arrange
        $postParams = new stdClass();
        $transformedPostParams = new stdClass();
        $transformedPostParams->abc = 'alpha';
        $entity = $this->getMockBuilder(ControllerModelInterface::class)->getMock();
        $isCreating = true;
        $base = $this->getMockBuilder(Base::class)
            ->setMethods(['generateEntity', 'getQueryWhiteList', 'validateLogin', 'getDataTransformer'])
            ->getMock();
        $request = $this->getMockBuilder(Request::class)->getMock();
        $base->request = $request;

        $transformer = $this->getMockBuilder(DataTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $transformer->expects($this->once())
            ->method('transform')
            ->with($entity);

        $transformer->expects($this->once())
            ->method('getParams')
            ->willReturn($transformedPostParams);

        $base->expects($this->once())
            ->method('getDataTransformer')
            ->with($postParams)
            ->willReturn($transformer);

        $expected = true;

        $this->getDi()->set(
            Save::class,
            function(Request $passedRequest, ApiInterface $passedEntity, $passedIsCreating) use ($request, $transformedPostParams, $entity, $isCreating, $expected) {
                $this->assertSame($request, $passedRequest);
                $this->assertSame($entity, $passedEntity);
                $this->assertSame($isCreating, $passedIsCreating);
                $save = $this->getMockBuilder(Save::class)
                    ->disableOriginalConstructor()
                    ->getMock();

                $save->expects($this->once())
                    ->method('process')
                    ->with($transformedPostParams)
                    ->willReturn($expected);
                return $save;
            }
        );

        //Act
        $reflectionClass = new ReflectionClass(Base::class);
        $reflectionMethod = $reflectionClass->getMethod('saveEntity');
        $reflectionMethod->setAccessible(true);
        $actual = $reflectionMethod->invoke($base, $postParams, $entity, $isCreating);

        //Assert
        $this->assertSame($expected, $actual);
    }

}