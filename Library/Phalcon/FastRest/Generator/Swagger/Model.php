<?php
namespace Phalcon\FastRest\Generator\Swagger;

class Model {
	/** @var  string */
	private $id;
	/** @var  Property[] */
	private $properties;

	/**
	 * Adds new property
	 *
	 * @param Property $property
	 *
	 * @return void
	 */
	public function addProperty(Property $property) {
		$properties = $this->getProperties();
		$properties[] = $property;
		$this->setProperties($properties);
	}

	/**
	 * Getter
	 * @return Property[]
	 */
	private function getProperties() {
		return $this->properties;
	}

	/**
	 * Setter
	 * @param Property[] $properties
	 */
	private function setProperties(array $properties) {
		$this->properties = $properties;
	}

	/**
	 * Getter
	 * @return string
	 */
	private function getId() {
		return $this->id;
	}

	/**
	 * Setter
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * Gets the string version
	 * @return string
	 */
	public function __toString() {
		$parts = array(
			'	id="'.$this->getId().'"',
		);
		foreach($this->getProperties() as $property) {
			$parts[] = $property->__toString();
		}
		return '@SWG\Model (
'.implode(",\n", $parts).'
)';
	}
}