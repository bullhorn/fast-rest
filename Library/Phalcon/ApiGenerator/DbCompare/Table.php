<?php
namespace Phalcon\ApiGenerator\DbCompare;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\ApiGenerator\Base;
class Table extends Base {
	/** @var  DbAdapter */
	private $dbAdapter;
	/** @var  string */
	private $name;
	/** @var  Line[] */
	private $lines;
	/** @var  Key[] */
	private $keys;
	/** @var  Constraint[] */
	private $constraints;
	/** @var  string */
	private $engine;
	/** @var  string */
	private $defaultCharset;
	/** @var  string */
	private $collate;

	/**
	 * Constructor
	 * @param DbAdapter $dbAdapter
	 * @param string    $table
	 */
	public function __construct(DbAdapter $dbAdapter, $table) {
		$this->setDbAdapter($dbAdapter);
		$this->setName($table);
		$this->buildTable();
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getEngine() {
		return $this->engine;
	}

	/**
	 * Setter
	 * @param string $engine
	 */
	public function setEngine($engine) {
		$this->engine = $engine;
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getDefaultCharset() {
		return $this->defaultCharset;
	}

	/**
	 * Setter
	 * @param string $defaultCharset
	 */
	public function setDefaultCharset($defaultCharset) {
		$this->defaultCharset = $defaultCharset;
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getCollate() {
		return $this->collate;
	}

	/**
	 * Setter
	 * @param string $collate
	 */
	public function setCollate($collate) {
		$this->collate = $collate;
	}



	/**
	 * Setter
	 * @return Key[]
	 */
	public function getKeys() {
		return $this->keys;
	}

	/**
	 * Setter
	 * @param Key[] $keys
	 */
	public function setKeys($keys) {
		$this->keys = $keys;
	}

	/**
	 * Getter
	 * @return Line[]
	 */
	public function getLines() {
		return $this->lines;
	}

	/**
	 * Setter
	 * @param Line[] $lines
	 */
	public function setLines(array $lines) {
		$this->lines = $lines;
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
	 * @return Constraint[]
	 */
	public function getConstraints() {
		return $this->constraints;
	}

	/**
	 * Setter
	 * @param Constraint[] $constraints
	 */
	public function setConstraints(array $constraints) {
		$this->constraints = $constraints;
	}



	/**
	 * Compares tables
	 *
	 * @param Table $table
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function equals(Table $table) {
		$prefix = $this->getName().': ';
		$errors = [];
		if($table->getName()!==$this->getName()) {
			throw new \Exception('Comparing Incomparable Tables');
		}
		if($table->getEngine()!==$this->getEngine()) {
			$errors[] = $prefix.'Engine Does Not Match:'."\n".$table->getEngine()."\n".$this->getEngine();
		}
		if($table->getDefaultCharset()!==$this->getDefaultCharset()) {
			$errors[] = $prefix.'Default Charset Does Not Match:'."\n".$table->getDefaultCharset()."\n".$this->getDefaultCharset();
		}
		if($table->getCollate()!==$this->getCollate()) {
			$errors[] = $prefix.'Collate Does Not Match:'."\n".$table->getCollate()."\n".$this->getCollate();
		}
		$errors = array_merge($errors, $this->equalsLines($table, $prefix));
		$errors = array_merge($errors, $this->equalsKeys($table, $prefix));
		$errors = array_merge($errors, $this->equalsConstraints($table, $prefix));
		return $errors;
	}

	/**
	 * Compares lines
	 *
	 * @param Table  $table
	 * @param string $prefix
	 *
	 * @return string[]
	 * @throws \Exception
	 */
	private function equalsLines(Table $table, $prefix) {
		$thisLines = $this->getLines();
		$tableLines = $table->getLines();
		$errors = [];

		foreach($thisLines as $line) {
			if(array_key_exists($line->getName(), $tableLines)) {
				$errors = array_merge($errors, $line->equals($tableLines[$line->getName()], $prefix));
			}
		}

		$thisLineNames = array_keys($thisLines);
		$tableLineNames = array_keys($tableLines);
		$diff = array_diff($tableLineNames, $thisLineNames);
		if(!empty($diff)) {
			$errors[] = $prefix.'Extra Columns: '."\n".implode("\n", $diff);
		}
		$diff = array_diff($thisLineNames, $tableLineNames);
		if(!empty($diff)) {
			$errors[] = $prefix.'Missing Columns: '."\n".implode("\n", $diff);
		}
		return $errors;
	}

	/**
	 * Compares keys
	 *
	 * @param Table  $table
	 * @param string $prefix
	 *
	 * @return string[]
	 * @throws \Exception
	 */
	private function equalsKeys(Table $table, $prefix) {
		$thisKeys = $this->getKeys();
		$tableKeys = $table->getKeys();
		$errors = [];

		foreach($thisKeys as $key) {
			if(array_key_exists($key->getName(), $tableKeys)) {
				$errors = array_merge($errors, $key->equals($tableKeys[$key->getName()], $prefix));
			}
		}

		$thisKeyNames = array_keys($thisKeys);
		$tableKeyNames = array_keys($tableKeys);
		$diff = array_diff($tableKeyNames, $thisKeyNames);
		if(!empty($diff)) {
			$errors[] = $prefix.'Extra Keys: '."\n".implode("\n", $diff);
		}
		$diff = array_diff($thisKeyNames, $tableKeyNames);
		if(!empty($diff)) {
			$errors[] = $prefix.'Missing Keys: '."\n".implode("\n", $diff);
		}
		return $errors;
	}

	/**
	 * Compares constraints
	 *
	 * @param Table  $table
	 * @param string $prefix
	 *
	 * @return string[]
	 * @throws \Exception
	 */
	private function equalsConstraints(Table $table, $prefix) {
		$thisConstraints = $this->getConstraints();
		$tableConstraints = $table->getConstraints();
		$errors = [];

		foreach($thisConstraints as $constraint) {
			if(array_key_exists($constraint->getName(), $tableConstraints)) {
				$errors = array_merge($errors, $constraint->equals($tableConstraints[$constraint->getName()], $prefix));
			}
		}

		$thisConstraintNames = array_keys($thisConstraints);
		$tableConstraintNames = array_keys($tableConstraints);
		$diff = array_diff($tableConstraintNames, $thisConstraintNames);
		if(!empty($diff)) {
			$errors[] = $prefix.'Extra Constraints: '."\n".implode("\n", $diff);
		}
		$diff = array_diff($thisConstraintNames, $tableConstraintNames);
		if(!empty($diff)) {
			$errors[] = $prefix.'Missing Constraints: '."\n".implode("\n", $diff);
		}
		return $errors;
	}

	/**
	 * Getter
	 * @return DbAdapter
	 */
	public function getDbAdapter() {
		return $this->dbAdapter;
	}

	/**
	 * Setter
	 * @param DbAdapter $dbAdapter
	 */
	public function setDbAdapter(DbAdapter $dbAdapter) {
		$this->dbAdapter = $dbAdapter;
	}

	/**
	 * Builds a table
	 * @return void
	 */
	private function buildTable() {
		$result = $this->getDbAdapter()->query('SHOW CREATE TABLE `'.$this->getName().'`')->fetchAll();
		$rawResult = $result[0]['Create Table'];
		$rawResult = $this->buildLines($rawResult);
		$rawResult = trim($rawResult);
		if(preg_match('@^ENGINE=(?P<engine>[^ ]+) (AUTO_INCREMENT=\d+ )?DEFAULT CHARSET=(?P<defaultCharset>[^ ]+) COLLATE=(?P<collate>[^ ]+)$@', $rawResult, $matches)) {
			$this->setEngine($matches['engine']);
			$this->setDefaultCharset($matches['defaultCharset']);
			$this->setCollate($matches['collate']);
		}
	}

	/**
	 * buildLines
	 *
	 * @param string $rawResult
	 *
	 * @return array
	 */
	private function buildLines($rawResult) {
		if(preg_match('@^CREATE TABLE `[^`]+` \(@', $rawResult, $matches)) {
			$rawResult = substr($rawResult, strlen($matches[0]));
		}
		$parseHelper = new ParseHelper();
		$lines = array();
		while(strlen($rawResult)>0) {
			$section = $parseHelper->parseSection($rawResult, ',');
			$lines[] = $section;
			$rawResult = substr($rawResult, strlen($section) + 1);
			if($parseHelper->isTooManyClosingParenthesis()) {
				break;
			}
		}
		$lineObjects = [];
		$keyObjects = [];
		$constraintObjects = [];
		foreach($lines as $line) {
			$line = trim($line);
			if($line[0]=='`') {
				$lineObject = new Line($line);
				$lineObjects[$lineObject->getName()] = $lineObject;
			} elseif(preg_match('@^(PRIMARY |UNIQUE |)KEY @', $line)) {
				$keyObject = new Key($line);
				$keyObjects[$keyObject->getName()] = $keyObject;
			} elseif(preg_match('@^CONSTRAINT @', $line)) {
				$constraintObject = new Constraint($line);
				$constraintObjects[$constraintObject->getName()] = $constraintObject;
			}
		}
		$this->setLines($lineObjects);
		$this->setKeys($keyObjects);
		$this->setConstraints($constraintObjects);
		return $rawResult;
	}

}