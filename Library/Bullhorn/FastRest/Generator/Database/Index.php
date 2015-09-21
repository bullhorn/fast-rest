<?php
namespace Bullhorn\FastRest\Generator\Database;

class Index {
	/** @var  bool */
	private $unique;
	/** @var  bool */
	private $primary;
	/** @var string[] */
	private $columns = array();

	/**
	 * Getter
	 * @return boolean
	 */
	public function isPrimary() {
		return $this->primary;
	}

	/**
	 * Setter
	 * @param boolean $primary
	 */
	public function setPrimary($primary) {
		$this->primary = $primary;
	}


	/**
	 * Getter
	 * @return boolean
	 */
	public function isUnique() {
		return $this->unique;
	}

	/**
	 * Setter
	 * @param boolean $unique
	 */
	public function setUnique($unique) {
		$this->unique = $unique;
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
	private function setColumns($columns) {
		$this->columns = $columns;
	}

	/**
	 * Adds a new column
	 *
	 * @param string $column
	 *
	 * @return void
	 */
	public function addColumn($column) {
		$columns = $this->getColumns();
		$columns[] = $column;
		$this->setColumns($columns);
	}

}