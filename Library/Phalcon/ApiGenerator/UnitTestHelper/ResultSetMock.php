<?php
namespace Phalcon\ApiGenerator\UnitTestHelper;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\ModelInterface;

class ResultSetMock implements ResultsetInterface, \Iterator {
	/** @type  ModelInterface[] */
	private $models;
	private $position = 0;

	/**
	 * Constructor
	 * @param ModelInterface[] $models
	 */
	public function __construct(array $models) {
		$this->setModels($models);
	}

	/**
	 * Getter
	 * @return ModelInterface[]
	 */
	private function getModels() {
		return $this->models;
	}

	/**
	 * Setter
	 * @param ModelInterface[] $models
	 */
	private function setModels(array $models) {
		$this->models = $models;
	}


	public function getType() {
		// TODO: Implement getType() method.
	}

	/**
	 * getFirst
	 * @return \Phalcon\Mvc\Model|false
	 */
	public function getFirst() {
		// TODO: Implement getFirst() method.
	}

	public function getLast() {
		// TODO: Implement getLast() method.
	}

	public function setIsFresh($isFresh) {
		// TODO: Implement setIsFresh() method.
	}

	public function isFresh() {
		// TODO: Implement isFresh() method.
	}

	public function getCache() {
		// TODO: Implement getCache() method.
	}

	public function toArray() {
		// TODO: Implement toArray() method.
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current() {
		$models = $this->getModels();
		return $models[$this->position];
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		$this->position++;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid() {
		return array_key_exists($this->position, $this->getModels());
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		$this->position = 0;
	}


}