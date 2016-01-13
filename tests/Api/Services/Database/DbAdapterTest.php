<?php
namespace Tests\Api\Services\Database;
use Bullhorn\FastRest\Api\Services\Database\DbAdapter;
use PDOException;
use Bullhorn\FastRest\UnitTestHelper\Base as UnitTestHelperBase;

class DbAdapterTest extends UnitTestHelperBase {
	const MYSQL_HAS_GONE_AWAY_ERROR_MESSAGE = 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away';

	private $mockSqlStatement = "mock sql";
	private $mockPlaceholders = ["Test1", "Test2"];
	private $mockDataTypes = ["Test3", "Test4"];

	/**
	 * Test execute
	 * @return void
	 */
	public function testExecute_parentMethodCalled () {
		/** @var \PHPUnit_Framework_MockObject_MockObject|DbAdapter $mockDbAdapter */
		$mockDbAdapter = $this->buildMockDbAdapter();

		$mockDbAdapter->expects($this->once())
					  ->method('callParentExecute')
					  ->with($this->mockSqlStatement, $this->mockPlaceholders, $this->mockDataTypes)
					  ->willReturn(true);

		$returnVal = $mockDbAdapter->execute($this->mockSqlStatement, $this->mockPlaceholders, $this->mockDataTypes);
		$this->assertEquals(true, $returnVal);
	}

	/**
	 * Test execute with Exception "MySql has gone away"
	 * @return void
	 */
	public function testExecute_expectedExceptionIsCaught() {
		$mockPdoException = $this->buildMockPdoException();

		/** @var \PHPUnit_Framework_MockObject_MockObject|DbAdapter $mockDbAdapter */
		$mockDbAdapter = $this->buildMockDbAdapter();

		$mockDbAdapter->expects($this->once())
					  ->method('connect');

		$mockDbAdapter->expects($this->exactly(2))
					  ->method('callParentExecute')
					  ->willReturnOnConsecutiveCalls(
						  $this->throwException($mockPdoException),
						  $this->returnValue(true)
					  );

		$returnVal = $mockDbAdapter->execute($this->mockSqlStatement, $this->mockPlaceholders, $this->mockDataTypes);
		$this->assertEquals(true, $returnVal);
	}

	/**
	 * Test query
	 * @return void
	 */
	public function testQuery_parentMethodCalled () {
		/** @var \PHPUnit_Framework_MockObject_MockObject|DbAdapter $mockDbAdapter */
		$mockDbAdapter = $this->buildMockDbAdapter();

		$mockDbAdapter->expects($this->once())
					  ->method('callParentQuery')
					  ->with($this->mockSqlStatement, $this->mockPlaceholders, $this->mockDataTypes)
					  ->willReturn(true);

		$returnVal = $mockDbAdapter->query($this->mockSqlStatement, $this->mockPlaceholders, $this->mockDataTypes);
		$this->assertEquals(true, $returnVal);
	}

	/**
	 * Test query with Exception "MySql has gone away"
	 * @return void
	 */
	public function testQuery_expectedExceptionIsCaught() {
		$mockPdoException = $this->buildMockPdoException();

		/** @var \PHPUnit_Framework_MockObject_MockObject|DbAdapter $mockDbAdapter */
		$mockDbAdapter = $this->buildMockDbAdapter();

		$mockDbAdapter->expects($this->once())
					  ->method('connect');

		$mockDbAdapter->expects($this->exactly(2))
					  ->method('callParentQuery')
					  ->willReturnOnConsecutiveCalls(
						  $this->throwException($mockPdoException),
						  $this->returnValue(true)
					  );

		$returnVal = $mockDbAdapter->query($this->mockSqlStatement, $this->mockPlaceholders, $this->mockDataTypes);
		$this->assertEquals(true, $returnVal);
	}

	/**
	 * Testing expected exception by passing in a param to mock pdo exception class
	 *
	 * @expectedException PDOException
	 * @return void
	 */
	public function testQuery_expectedExceptionIsThrown() {
		$mockPdoException = $this->buildMockPdoException("Dummy");

		/** @var \PHPUnit_Framework_MockObject_MockObject|DbAdapter $mockDbAdapter */
		$mockDbAdapter = $this->buildMockDbAdapter();

		$mockDbAdapter->expects($this->exactly(1))
			->method('callParentQuery')
			->willThrowException(
				$mockPdoException
			);

		$mockDbAdapter->query($this->mockSqlStatement, $this->mockPlaceholders, $this->mockDataTypes);
	}

	/**
	 * Testing expected exception by passing in a param to mock pdo exception class
	 *
	 * @expectedException PDOException
	 * @return void
	 */
	public function testExecute_expectedExceptionIsThrown() {
		$mockPdoException = $this->buildMockPdoException("Test");

		/** @var \PHPUnit_Framework_MockObject_MockObject|DbAdapter $mockDbAdapter */
		$mockDbAdapter = $this->buildMockDbAdapter();

		$mockDbAdapter->expects($this->exactly(1))
			->method('callParentExecute')
			->willThrowException(
				$mockPdoException
			);

		$mockDbAdapter->execute($this->mockSqlStatement, $this->mockPlaceholders, $this->mockDataTypes);
	}

	/**
	 * Builds a mock DbAdapter for testing
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject|DbAdapter
	 */
	private function buildMockDbAdapter() {
		$mockDbAdapter = $this->getMockBuilder(DbAdapter::class)
							  ->disableOriginalConstructor()
							  ->setMethods(['callParentExecute', 'callParentQuery', 'connect'])
							  ->getMock();

		return $mockDbAdapter;
	}

	/**
	 * Mocking the pdo exception class via reflection directly
	 *
	 * @param String $code
	 *
	 * @return \PDOException
	 */
	private function buildMockPdoException($code = "HY000") {
		$exception = new \PDOException("MySql has Gone Away");
		$reflectionClass = new \ReflectionClass($exception);
		$reflectionProperty = $reflectionClass->getProperty("code");
		$reflectionProperty->setAccessible(true);
		$reflectionProperty->setValue($exception, $code);
		return $exception;
	}
}
