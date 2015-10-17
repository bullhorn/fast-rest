<?php
namespace Bullhorn\FastRest\UnitTestHelper;
use Phalcon\Db\AdapterInterface;
use Phalcon\Db\ColumnInterface;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\ReferenceInterface;

class MockDbAdapter implements AdapterInterface {
	/** @var  MockTable[] */
	private $tables = array();
	/** @var  string */
	public $phalconHelperNamespace = '';
	/** @var  string */
	public $modelSubNamespace = '';
	/** @type  array */
	private $descriptor;

	/**
	 * Constructor
	 * @param array $descriptor
	 */
	public function __construct(array $descriptor) {
		$this->descriptor = $descriptor;
	}

	/**
	 * Getter
	 * @return string
	 */
	private function getModelSubNamespace() {
		return $this->modelSubNamespace;
	}

	/**
	 * Setter
	 * @param string $modelSubNamespace
	 */
	public function setModelSubNamespace($modelSubNamespace) {
		$this->modelSubNamespace = $modelSubNamespace;
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getPhalconHelperNamespace() {
		return $this->phalconHelperNamespace;
	}

	/**
	 * Setter
	 * @param string $phalconHelperNamespace
	 */
	public function setPhalconHelperNamespace($phalconHelperNamespace) {
		$this->phalconHelperNamespace = $phalconHelperNamespace;
	}

	/**
	 * Getter
	 * @return MockTable[]
	 */
	public function getTables() {
		return $this->tables;
	}

	/**
	 * Setter
	 * @param MockTable[] $tables
	 */
	public function setTables(array $tables) {
		$this->tables = $tables;
	}

	/**
	 * Gets the table name
	 *
	 * @param string $tableName
	 *
	 * @return MockTable
	 * @throws \Exception
	 */
	public function getTable($tableName) {
		foreach($this->getTables() as $table) {
			if($table->getName()==$tableName) {
				return $table;
			}
		}
		$className = $this->getPhalconHelperNamespace().'\\Database\\Tables\\'.$this->getModelSubNamespace().'\\'.ucfirst($tableName).'Test';
		if(class_exists($className)) {
			$table = new $className();
			$this->addTable($table);
			return $table;
		}
		return false;
	}

	/**
	 * Adds a table
	 *
	 * @param MockTable $table
	 *
	 * @return void
	 */
	public function addTable(MockTable $table) {
		$tables = $this->getTables();
		$found = false;
		foreach($tables as $key=>$subTable) {
			if($subTable->getName()==$table->getName()) {
				$tables[$key] = $table;
				$found = true;
				break;
			}
		}
		if(!$found) {
			$tables[] = $table;
		}
		$this->setTables($tables);
	}

	public function fetchOne($sqlQuery, $fetchMode = null, $placeholders = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //fetchOne() method.
	}

	public function fetchAll($sqlQuery, $fetchMode = null, $placeholders = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //fetchAll() method.
	}

	public function insert($table, array $values, $fields = null, $dataTypes = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //insert() method.
	}

	public function update($table, $fields, $values, $whereCondition = null, $dataTypes = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //update() method.
	}

	public function delete($table, $whereCondition = null, $placeholders = null, $dataTypes = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //delete() method.
	}

	public function getColumnList($columnList) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //getColumnList() method.
	}

	public function limit($sqlQuery, $number) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //limit() method.
	}

	public function tableExists($tableName, $schemaName = null) {
		return $this->getTable($tableName)!==false;
	}

	public function viewExists($viewName, $schemaName = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //viewExists() method.
	}

	public function forUpdate($sqlQuery) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //forUpdate() method.
	}

	public function sharedLock($sqlQuery) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //sharedLock() method.
	}

	public function createTable($tableName, $schemaName, array $definition) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //createTable() method.
	}

	public function dropTable($tableName, $schemaName = null, $ifExists = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //dropTable() method.
	}

	public function createView($viewName, array $definition, $schemaName = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //createView() method.
	}

	public function dropView($viewName, $schemaName = null, $ifExists = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //dropView() method.
	}

	/**
	 * {REPLACE_ME!}
	 *
	 * @param                             $tableName
	 * @param                             $schemaName
	 * @param \Phalcon\Db\ColumnInterface $column
	 *
	 * @return mixed
	 */
	public function addColumn($tableName, $schemaName, ColumnInterface $column) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //addColumn() method.
	}

	/**
	 * {REPLACE_ME!}
	 *
	 * @param                             $tableName
	 * @param                             $schemaName
	 * @param \Phalcon\Db\ColumnInterface $column
	 *
	 * @return mixed
	 */
	public function modifyColumn($tableName, $schemaName, ColumnInterface $column, ColumnInterface $currentColumn = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //modifyColumn() method.
	}

	public function dropColumn($tableName, $schemaName, $columnName) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //dropColumn() method.
	}

	/**
	 * {REPLACE_ME!}
	 *
	 * @param                            $tableName
	 * @param                            $schemaName
	 * @param \Phalcon\Db\IndexInterface $index
	 *
	 * @return mixed
	 */
	public function addIndex($tableName, $schemaName, IndexInterface $index) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //addIndex() method.
	}

	public function dropIndex($tableName, $schemaName, $indexName) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //dropIndex() method.
	}

	/**
	 * {REPLACE_ME!}
	 *
	 * @param                            $tableName
	 * @param                            $schemaName
	 * @param \Phalcon\Db\IndexInterface $index
	 *
	 * @return mixed
	 */
	public function addPrimaryKey($tableName, $schemaName, IndexInterface $index) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //addPrimaryKey() method.
	}

	public function dropPrimaryKey($tableName, $schemaName) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //dropPrimaryKey() method.
	}

	/**
	 * {REPLACE_ME!}
	 *
	 * @param                                $tableName
	 * @param                                $schemaName
	 * @param \Phalcon\Db\ReferenceInterface $reference
	 *
	 * @return mixed
	 */
	public function addForeignKey($tableName, $schemaName, ReferenceInterface $reference) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //addForeignKey() method.
	}

	public function dropForeignKey($tableName, $schemaName, $referenceName) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //dropForeignKey() method.
	}

	/**
	 * {REPLACE_ME!}
	 *
	 * @param \Phalcon\Db\ColumnInterface $column
	 *
	 * @return mixed
	 */
	public function getColumnDefinition(ColumnInterface $column) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //getColumnDefinition() method.
	}

	public function listTables($schemaName = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //listTables() method.
	}

	public function listViews($schemaName = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //listViews() method.
	}

	public function getDescriptor() {
		return $this->descriptor;
	}

	public function getConnectionId() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //getConnectionId() method.
	}

	public function getSQLStatement() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //getSQLStatement() method.
	}

	public function getRealSQLStatement() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //getRealSQLStatement() method.
	}

	public function getSQLVariables() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //getSQLVariables() method.
	}

	public function getSQLBindTypes() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //getSQLBindTypes() method.
	}

	public function getType() {
		return 'mocking';
	}

	public function getDialectType() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //getDialectType() method.
	}

	public function getDialect() {
		return '';
	}

	public function connect($descriptor = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //connect() method.
	}

	public function query($sqlStatement, $placeholders = null, $dataTypes = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //query() method.
	}

	public function execute($sqlStatement, $placeholders = null, $dataTypes = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //execute() method.
	}

	public function affectedRows() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //affectedRows() method.
	}

	public function close() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //close() method.
	}

	public function escapeIdentifier($identifier) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //escapeIdentifier() method.
	}

	public function escapeString($str) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //escapeString() method.
	}

	public function lastInsertId($sequenceName = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //lastInsertId() method.
	}

	public function begin($nesting = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //begin() method.
	}

	public function rollback($nesting = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //rollback() method.
	}

	public function commit($nesting = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //commit() method.
	}

	public function isUnderTransaction() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //isUnderTransaction() method.
	}

	public function getInternalHandler() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //getInternalHandler() method.
	}

	public function describeIndexes($table, $schema = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //describeIndexes() method.
	}

	public function describeReferences($table, $schema = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //describeReferences() method.
	}

	public function tableOptions($tableName, $schemaName = null) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //tableOptions() method.
	}

	public function useExplicitIdValue() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //useExplicitIdValue() method.
	}

	public function getDefaultIdValue() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //getDefaultIdValue() method.
	}

	public function supportSequences() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //supportSequences() method.
	}

	public function createSavepoint($name) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //createSavepoint() method.
	}

	public function releaseSavepoint($name) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //releaseSavepoint() method.
	}

	public function rollbackSavepoint($name) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //rollbackSavepoint() method.
	}

	public function setNestedTransactionsWithSavepoints($nestedTransactionsWithSavepoints) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //setNestedTransactionsWithSavepoints() method.
	}

	public function isNestedTransactionsWithSavepoints() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //isNestedTransactionsWithSavepoints() method.
	}

	public function getNestedTransactionSavepointName() {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //getNestedTransactionSavepointName() method.
	}

	public function describeColumns($table, $schema = null) {
		$MockTable = $this->getTable($table);
		if($MockTable===false) {
			throw new \Exception('Table Not Defined: '.$table);
		}
		return $MockTable->getColumns();
	}

	public function convertBoundParams($sqlStatement, $params) {
		throw new \Exception('MockDbAdapter: '.__FUNCTION__); //convertBoundParams() method.
	}
}