<?php
namespace Bullhorn\FastRest\UnitTestHelper;

use Phalcon\Db\Column;

abstract class MockTable {
    /** @var  string */
    private $name;
    /** @var  Column[] */
    private $columns = array();

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
     * @return \Phalcon\Db\Column[]
     */
    public function getColumns() {
        return $this->columns;
    }

    /**
     * Setter
     * @param \Phalcon\Db\Column[] $columns
     */
    public function setColumns(array $columns) {
        $this->columns = $columns;
    }

}