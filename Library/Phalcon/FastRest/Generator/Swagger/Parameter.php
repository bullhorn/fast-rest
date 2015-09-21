<?php
namespace Phalcon\FastRest\Generator\Swagger;

class Parameter {
	/** @var string */
	private $name;
	/** @var string */
	private $description;
	/** @var string */
	private $paramType;
	/** @var string */
	private $type;
	/** @var  bool */
	private $allowMultiple = false;
	/** @var  bool */
	private $required = false;

	/**
	 * Getter
	 * @return boolean
	 */
	public function isRequired() {
		return $this->required;
	}

	/**
	 * Setter
	 * @param boolean $required
	 *
	 * @return $this
	 */
	public function setRequired($required) {
		$this->required = $required;
		return $this;
	}

	/**
	 * Getter
	 * @return boolean
	 */
	private function isAllowMultiple() {
		return $this->allowMultiple;
	}

	/**
	 * Setter
	 * @param boolean $allowMultiple
	 */
	public function setAllowMultiple($allowMultiple) {
		$this->allowMultiple = $allowMultiple;
	}



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
	 * @return string
	 */
	private function getParamType() {
		return $this->paramType;
	}

	/**
	 * Setter
	 * @param string $paramType
	 */
	public function setParamType($paramType) {
		$this->paramType = $paramType;
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
	 * toString
	 * @return string
	 */
	public function __toString() {
		$parts = array(
			'			name="'.$this->getName().'"',
			'			description="'.str_replace("\n", '<br />', htmlspecialchars(trim($this->getDescription()))).'"',
			'			paramType="'.$this->getParamType().'"',
			'			type="'.$this->getType().'"',
			'			required='.($this->isRequired()?'true':'false'),
			'			allowMultiple='.($this->isAllowMultiple()?'true':'false')
		);
		return '		@SWG\Parameter (
'.implode(",\n", $parts).'
		)';
	}
}