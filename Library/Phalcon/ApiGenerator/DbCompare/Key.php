<?php
namespace Phalcon\ApiGenerator\DbCompare;
use Phalcon\ApiGenerator\Base;
class Key extends Base {
	/** @var  string */
	private $name;
	/** @var  string PRIMARY|INDEX|UNIQUE */
	private $type;
	/** @var  string[] */
	private $columns;

	/**
	 * Constructor
	 * @param string $rawString
	 */
	public function __construct($rawString) {
		$this->build($rawString);
	}

	/**
	 * Compares two keys, and returns any errors of non matches
	 *
	 * @param Key    $key
	 * @param string $prefix
	 *
	 * @return string[]
	 * @throws \Exception
	 */
	public function equals(Key $key, $prefix) {
		$prefix .= ': Index ('.$this->getName().'):';
		$errors = [];
		if($key->getName()!==$this->getName()) {
			throw new \Exception('Comparing Incomparable columns');
		}
		if($key->getType()!==$this->getType()) {
			$errors[] = $prefix.'Type Does Not Match:'."\n".$key->getType()."\n".$this->getType();
		}
		$diff = array_diff($key->getColumns(), $this->getColumns());
		if(!empty($diff)) {
			$errors[] = $prefix.'Extra Columns: '."\n".implode("\n", $diff);
		}
		$diff = array_diff($this->getColumns(), $key->getColumns());
		if(!empty($diff)) {
			$errors[] = $prefix.'Missing Columns: '."\n".implode("\n", $diff);
		}
		return $errors;
	}

	/**
	 * parses the raw string and builds the parts
	 *
	 * @param string $rawString
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function build($rawString) {
		if(preg_match('@^(?P<type>PRIMARY |UNIQUE |)KEY@', $rawString, $matches)) {
			switch($matches['type']) {
				case 'PRIMARY ':
					$this->setType('PRIMARY');
					break;
				case 'UNIQUE ':
					$this->setType('UNIQUE');
					break;
				default:
					$this->setType('INDEX');
					break;
			}
			$rawString = substr($rawString, strlen($matches[0]));
		}

		$rawString = trim($rawString);
		if(preg_match('@^`(?P<name>[^`]+)`@', $rawString, $matches)) {
			$this->setName($matches['name']);
			$rawString = substr($rawString, strlen($matches[0]));
		}
		$rawString = trim($rawString, ' ()`');
		$parts = explode('`,`', $rawString);
		$this->setColumns($parts);
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Setter
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Setter
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * Getter
	 * @return \string[]
	 */
	public function getColumns() {
		return $this->columns;
	}

	/**
	 * Setter
	 * @param \string[] $columns
	 */
	public function setColumns($columns) {
		$this->columns = $columns;
	}




}