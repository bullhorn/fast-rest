<?php
namespace Bullhorn\FastRest\Generator\Swagger;

class Property {
	/** @var  string */
	private $name;
	/** @var  string */
	private $type;
	/** @var  string */
	private $format;
	/** @var  string */
	private $description;
	/** @var bool */
	private $required = false;
	
	/**
	 * Getter
	 * @return string
	 */
	private function getName() {
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
	private function getType() {
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
	 * @return string
	 */
	private function getFormat() {
		return $this->format;
	}
	
	/**
	 * Setter
	 * @param string $format
	 */
	public function setFormat($format) {
		$this->format = $format;
	}
	
	/**
	 * Getter
	 * @return string
	 */
	private function getDescription() {
		return $this->description;
	}
	
	/**
	 * Setter
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}
	
	/**
	 * Getter
	 * @return boolean
	 */
	private function isRequired() {
		return $this->required;
	}
	
	/**
	 * Setter
	 * @param boolean $required
	 */
	public function setRequired($required) {
		$this->required = $required;
	}
	
	/**
	 * Gets the string version
	 * @return string
	 */
	public function __toString() {
		$parts = array(
			'		name="'.$this->getName().'"',
			'		type="'.$this->getType().'"',
			'		format="'.$this->getFormat().'"',
			'		description="'.$this->getDescription().'"',
			'		required='.($this->isRequired()?'true':'false')
		);
		return '	@SWG\Property (
'.implode(",\n", $parts).'
	)';
	}
}