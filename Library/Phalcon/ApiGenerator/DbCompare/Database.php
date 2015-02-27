<?php
namespace Phalcon\ApiGenerator\DbCompare;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\ApiGenerator\Base;
class Database extends Base {
	/** @var  DbAdapter */
	private $dbAdapter;
	/** @var  Table[] */
	private $tables;

	/**
	 * Constructor
	 * @param DbAdapter $dbAdapter
	 * @param string[]  $ignoreTables
	 */
	public function __construct(DbAdapter $dbAdapter, $ignoreTables = []) {
		$this->setDbAdapter($dbAdapter);
		$this->buildDatabase($ignoreTables);
	}
	
	/**
	 * Getter
	 * @return Table[]
	 */
	public function getTables() {
		return $this->tables;
	}
	
	/**
	 * Setter
	 * @param Table[] $tables
	 */
	public function setTables(array $tables) {
		$this->tables = $tables;
	}




	/**
	 * Compares database
	 *
	 * @param Database $database
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function equals(Database $database) {
		$prefix = '';
		$thisTables = $this->getTables();
		$databaseTables = $database->getTables();
		$errors = [];
		
		foreach($thisTables as $database) {
			if(array_key_exists($database->getName(), $databaseTables)) {
				$errors = array_merge($errors, $database->equals($databaseTables[$database->getName()], $prefix));
			}
		}
		
		$thisTableNames = array_keys($thisTables);
		$databaseTableNames = array_keys($databaseTables);
		$diff = array_diff($databaseTableNames, $thisTableNames);
		if(!empty($diff)) {
			$errors[] = $prefix.'Extra Tables: '."\n".implode("\n", $diff);
		}
		$diff = array_diff($thisTableNames, $databaseTableNames);
		if(!empty($diff)) {
			$errors[] = $prefix.'Missing Tables: '."\n".implode("\n", $diff);
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
	 * @param string[] $ignoreTables
	 * @return void
	 */
	private function buildDatabase($ignoreTables) {
		$tables = $this->getDbAdapter()->query('SHOW FULL TABLES WHERE Table_Type!="VIEW"')->fetchAll();
		array_walk(
			$tables,
			function(&$item) {
				$item = $item[0];
			}
		);
		$tables = array_diff($tables, $ignoreTables);
		$tableObjects = [];
		foreach($tables as $table) {
			$tableObject = new Table($this->getDbAdapter(), $table);
			$tableObjects[$tableObject->getName()] = $tableObject;
		}
		$this->setTables($tableObjects);
	}
}