<?php
namespace Bullhorn\FastRest\UnitTestHelper;

use Bullhorn\FastRest\Api\Services\DataValidation\Assert;
use Closure;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class ClassPropertyTest {
	private $IGNORED_PROPERTIES = ["_options", "di"];

	/**
	 * The class to be tested
	 * @var Closure
	 */
	private $objectFactory;

	/**
	 * The test class to run asserts on
	 * @var PHPUnit_Framework_TestCase
	 */
	private $tester;

	/**
	 * The reflection of the class to be tested
	 * @var ReflectionClass
	 */
	private $reflectionClass;

	/**
	 * Constructor ensures object is passed in
	 * @param Closure                    $objectFactory
	 * @param PHPUnit_Framework_TestCase $tester
	 * @param string|null                $actualClassName used for testing abstract classes
	 */
	public function __construct(Closure $objectFactory, PHPUnit_Framework_TestCase $tester, $actualClassName=null) {
		$this->objectFactory = $objectFactory;
		$this->tester = $tester;
		$object = $this->buildObject();
		$this->reflectionClass = new ReflectionClass(is_null($actualClassName)?$object:$actualClassName);
	}

	/**
	 * Getter for reflectionBase
	 * @return ReflectionClass
	 */
	private function getReflectionClass() {
		return $this->reflectionClass;
	}

	/**
	 * Runs the Closure function and returns the resulting object
	 * @return object
	 */
	private function buildObject() {
		$objectFactory = $this->objectFactory;
		$object = $objectFactory();
		if (!is_object($object)) {
			$this->tester->fail('Closure function does not return an object.');
		}
		return $object;
	}

	/**
	 * Returns the name of the getter method for a passed field, or fails the test if one isn't found
	 * @param String $field
	 * @return String
	 */
	private function findGetter($field) {
		$object = $this->buildObject();
		foreach(array('get','is') as $prefix) {
			$getterName = $prefix.ucfirst($field);
			if (method_exists($object, $getterName)) {
				return $getterName;
			}
		}
		$this->tester->fail("Getter for ".$field." doesn't exist.");
		return null;
	}

	/**
	 * Returns the variable type for a passed field
	 * @param String $field
	 * @return String
	 */
	private function findTypeOfField($field) {
		$class = $this->getReflectionClass();
		$property = $class->getProperty($field);
		$docComment = $property->getDocComment();
		if(preg_match('@\*\s+\@(var|type)\s+(?P<type>[^ ]+)*@', $docComment, $matches)) {
			return trim($matches['type']);
		} elseif(preg_match('@\@Column\(type="(?P<type>[^ ]+)",@', $docComment, $matches)) {
			return trim($matches['type']);
		} else {
			$this->tester->fail('Could Not Find the Doc Type for: '.$property->getName());
		}
		return null;
	}

	/**
	 * Gets the valid enum options for a passed field
	 * @param String $field
	 * @return String[]
	 */
	private function findEnumOptionsForField($field) {
		$class = $this->getReflectionClass();
		$property = $class->getProperty($field);
		$docComment = $property->getDocComment();
		if(preg_match('@\*\s+\@options\s+\[(?P<rawOptions>.+)\]*@', $docComment, $matches)) {
			$rawOptions = trim($matches['rawOptions']);
			return str_getcsv($rawOptions);
		} else {
			$this->tester->fail('Could Not Find the Doc Type for: '.$property->getName());
		}
		return null;
	}

	/**
	 * Returns an array of data to use for testing based on the passed variable's type
	 * @param String $field
	 * @throw Exception
	 * @return mixed[]
	 */
	private function findDefaultsForField($field) {
		$type = $this->findTypeOfField($field);
		return $this->findDefaultsForType($type, $field);
	}

	/**
	 * Returns an array of data to use for testing based on the passed type
	 *
	 * @param string $type
	 * @param string $field
	 *
	 * @return mixed[]
	 */
	private function findDefaultsForType($type, $field) {
		$rawType = $type;
		$type = strtolower($type);

		switch($type) {
			case 'boolean':
			case 'bool':
				return array(TRUE, FALSE, NULL);
				break;
			case 'int':
				return array(0, 1, -1);
				break;
			case 'float':
			case 'double':
				return array(0.0, 0.5, -0.5);
				break;
			case 'array':
				return array(array(0,1), array(-1,1), array());
				break;
			case 'string':
				return array('test','!@#$','');
				break;
			case 'enum':
				return $this->findEnumOptionsForField($field);
				break;
			default:
				if (preg_match('/^(?P<arrayType>.*)\[\]$/', $rawType, $matches)) {
					$values = $this->findDefaultsForType($matches['arrayType'], $field);
					$returnVar = [];
					foreach($values as $value) {
						$returnVar[] = [$value];
					}
					return $returnVar;
				} else if (class_exists($this->getFullClassName($rawType))) {
					$className = $this->getFullClassName($rawType);
					return [$this->tester->getMockBuilder($className)
						->disableOriginalConstructor()
						->getMock()];
				} else {
					$this->tester->fail('Invalid variable type: '.$rawType);
				}
				break;
		}
		return null;
	}

	/**
	 * getFullClassName
	 *
	 * @param string $shortClassName
	 *
	 * @return string
	 */
	private function getFullClassName($shortClassName) {
		if(substr($shortClassName, 0, 1)=='\\') {
			return substr($shortClassName, 1);
		}
		$class = $this->getReflectionClass();
		if($class->getNamespaceName()!='') {
			$className = $class->getNamespaceName().'\\'.$shortClassName;
			if(class_exists($className)) {
				return $className;
			}
		} elseif(class_exists($shortClassName)) {
			return $shortClassName;
		}
		//Else look at uses
		$contents = file_get_contents($class->getFileName());
		$contents = preg_replace('@(\r?\n|^)//.*@', '', $contents);
		$contents = preg_replace('@class '.$this->getReflectionClass()->getShortName().'.*@s', '', $contents);
		if(preg_match_all('@(\r?\n|^)use (?P<use>[a-zA-Z0-9_ \\\\]+);@', $contents, $matches)) {
			$uses = $matches['use'];
			foreach($uses as $use) {
				if(preg_match('@^(?P<className>[a-zA-Z0-9_ \\\\]+) as (?P<useName>[a-zA-Z0-9]+)$@', $use, $subMatches)) {
					if($subMatches['useName']==$shortClassName) {
						return $subMatches['className'];
					}
				} else {
					if(preg_match('@(?P<useName>[a-zA-Z0-9_]+)$@', $use, $subMatches)) {
						if($subMatches['useName']==$shortClassName) {
							return $use;
						}
					}
				}
			}
		}
		return $shortClassName;
	}

	/**
	 * Returns a list of all properties of the current class
	 * @return String[]
	 */
	private function findAllProperties() {
		$class = $this->getReflectionClass();
		$reflect = $class->getProperties();
		$returnVar = array();
		foreach($reflect as $property) {
			$name = $property->getName();
			if (!in_array($name, $this->IGNORED_PROPERTIES)) {
				$returnVar[] = $name;
			}
		}
		return $returnVar;
	}

	/**
	 * Converts a plural variable name into its singular version
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	private function makeFieldSingular($field) {
		if (substr($field, -1) != 's') {
			$this->tester->fail('Attempt to make field '.$field.' singular failed: last character is not "s".');
		}
		$field = substr($field, 0, -1);
		if (substr($field, -2) == 'ie') {
			$field = substr($field, 0, -2).'y';
		}
		return $field;
	}

	/**
	 * Returns the name for the adder function for a field
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	private function findAdderName($field) {
		$adderName = 'add'.ucfirst($this->makeFieldSingular($field));
		return $adderName;
	}

	/**
	 * Tests all the properties for the current class using default test values
	 * @return VOID
	 */
	public function testAllPropertiesGettersAndSetters() {
		$props = $this->findAllProperties();
		foreach ($props as $property) {
			$this->testProperty($property);
		}
	}

	/**
	 * Tests the getter and setter for a field
	 * @param string  $field
	 * @param mixed[] $values
	 * @return VOID
	 */
	public function testProperty($field, array $values=array()) {
		if (empty($values)) {
			$values = $this->findDefaultsForField($field);
		}
		$this->testSetter($field, $values);
		$this->testGetter($field, $values);
		if (substr($field, -1) == 's' && method_exists($this->buildObject(), $this->findAdderName($field))) {
			$this->testAdder($field, $values);
		}
	}

	/**
	 * Tests the getter for a field
	 * @param string  $field
	 * @param mixed[] $values
	 * @return VOID
	 */
	public function testGetter($field, array $values) {
		$object = $this->buildObject();

		//arrange
		$getterName = $this->findGetter($field);

		$reflectionProperty = $this->getReflectionClass()->getProperty($field);
		$reflectionProperty->setAccessible(TRUE);

		$getMethod = $this->getReflectionClass()->getMethod($getterName);
		$getMethod->setAccessible(TRUE);
		//act + assert
		$resultList = array();
		foreach ($values as $value) {
			$reflectionProperty->setValue($object, $value);
			$resultList[] = $getMethod->invoke($object);
		}
		$this->tester->assertSame($values, $resultList, 'Getter failed for field '.$field);
	}

	/**
	 * Tests the setter for a field
	 * @param string  $field
	 * @param mixed[] $values
	 * @return VOID
	 */
	public function testSetter($field, array $values) {
		$object = $this->buildObject();

		//arrange
		$setterName = 'set'.ucfirst($field);
		if (!method_exists($object, $setterName)) {
			$this->tester->fail("Setter for ".$field." doesn't exist.");
		}

		$setMethod = $this->getReflectionClass()->getMethod($setterName);
		$setMethod->setAccessible(TRUE);
		$reflectionProperty = $this->getReflectionClass()->getProperty($field);
		$reflectionProperty->setAccessible(TRUE);
		//act + assert
		$resultList = array();
		foreach ($values as $value) {
			$setMethod->invoke($object, $value);
			$resultList[] = $reflectionProperty->getValue($object);
		}
		$this->tester->assertSame($values, $resultList, 'Setter failed for field '.$field);
	}

	/**
	 * Tests the setter for a field
	 * @param string  $field
	 * @param mixed[] $values
	 * @return VOID
	 */
	public function testAdder($field, array $values) {
		$object = $this->buildObject();

		//arrange
		$adderName = $this->findAdderName($field);
		if (!method_exists($object, $adderName)) {
			$this->tester->fail("Adder for ".$field." doesn't exist.");
		}

		$addMethod = $this->getReflectionClass()->getMethod($adderName);
		$addMethod->setAccessible(TRUE);
		$reflectionProperty = $this->getReflectionClass()->getProperty($field);
		$reflectionProperty->setAccessible(TRUE);
		//act + assert
		$resultList = $reflectionProperty->getValue($object);
		foreach ($values as $value) {
			$addMethod->invoke($object, $this->arrayFirst($value)); //This is the adder, so only add the first element in the array of values
			$resultList[] = $this->arrayFirst($value);
		}
		$this->tester->assertSame($resultList, $reflectionProperty->getValue($object), 'Adder failed for field '.$field);
	}

	/**
	 * arrayFirst
	 * @param array $value
	 * @return mixed
	 */
	private function arrayFirst($value) {
		$value = Assert::isArray($value);
		foreach($value as $part) {
			return $part;
		}
		return null;
	}

	/**
	 * Tests the setter for a field
	 * @param string  $field
	 * @param mixed[] $values
	 * @return VOID
	 */
	public function testRemover($field, array $values) {
		$object = $this->buildObject();

		//arrange
		$removerName = 'remove'.ucfirst($this->makeFieldSingular($field));
		if (!method_exists($object, $removerName)) {
			$this->tester->fail("Remover for ".$field." doesn't exist.");
		}

		$removeMethod = $this->getReflectionClass()->getMethod($removerName);
		$removeMethod->setAccessible(TRUE);
		$reflectionProperty = $this->getReflectionClass()->getProperty($field);
		$reflectionProperty->setAccessible(TRUE);
		$reflectionProperty->setValue($object, $values);
		//act + assert
		$resultList = $values;
		$removeMethod->invoke($object);
		array_pop($resultList);
		$this->tester->assertSame($resultList, $reflectionProperty->getValue($object), 'Remover failed for field '.$field);
	}

}