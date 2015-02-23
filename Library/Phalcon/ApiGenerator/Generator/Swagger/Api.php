<?php
namespace Phalcon\ApiGenerator\Generator\Swagger;

class Api {
	/** @var  string */
	private $path;
	/** @var  Operation[] */
	private $operations;

	/**
	 * Getter
	 * @return string
	 */
	private function getPath() {
		return $this->path;
	}

	/**
	 * Setter
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}


	
	/**
	 * Getter
	 * @return Operation[]
	 */
	private function getOperations() {
		return $this->operations;
	}

	/**
	 * Setter
	 * @param Operation[] $operations
	 */
	private function setOperations(array $operations) {
		$this->operations = $operations;
	}

	/**
	 * Adds new operation
	 *
	 * @param Operation $operation
	 *
	 * @return void
	 */
	public function addOperation(Operation $operation) {
		$operations = $this->getOperations();
		$operations[] = $operation;
		$this->setOperations($operations);
	}

	/**
	 * Gets the string version
	 * @return string
	 */
	public function __toString() {
		$parts = array(
			'	path="'.$this->getPath().'"',
		);
		foreach($this->getOperations() as $operation) {
			$parts[] = $operation->__toString();
		}
		return '@SWG\Api (
'.implode(",\n", $parts).'
)';
	}
}