<?php
namespace Bullhorn\FastRest\UnitTestHelper;

use NickLewis\PhalconDbMock\Models\Table;
use NickLewis\PhalconDbMock\Services\DbAdapter;
use Phalcon\Db\ColumnInterface;

class MockDbAdapter extends DbAdapter {
    /** @var  string */
    public $phalconHelperNamespace = '';
    /** @var  string */
    public $modelSubNamespace = '';

    /**
     * checkAddTable
     * @param string $tableName
     * @return void
     * @throws \NickLewis\PhalconDbMock\Models\DbException
     */
    private function checkAddTable($tableName) {
        $className = $this->getPhalconHelperNamespace() . '\\Database\\Tables\\' . $this->getModelSubNamespace() . '\\' . ucfirst($tableName) . 'Test';
        if(class_exists($className)) {
            /** @var MockTable $mockTable */
            $mockTable = new $className();

            $table = new Table($this->getDatabase(), $mockTable->getName());
            foreach($mockTable->getColumns() as $column) {
                $table->addColumn($column);
            }
            $this->getDatabase()->addTable($table);
        }
    }

    /**
     * tableExists
     * @param string      $tableName
     * @param string|null $schemaName
     * @return bool
     */
    public function tableExists($tableName, $schemaName = null) {
        if(!parent::tableExists($tableName, $schemaName)) {
            $this->checkAddTable($tableName);
        }
        return parent::tableExists($tableName, $schemaName);
    }

    /**
     * describeColumns
     * @param string      $table
     * @param string|null $schema
     * @return ColumnInterface
     */
    public function describeColumns($table, $schema = null) {
        $this->tableExists($table, $schema);
        return parent::describeColumns($table, $schema);
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

}