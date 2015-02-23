<?php
namespace Phalcon\ApiGenerator\Generator\Object;

class Variable {
	/** @var  string */
	private $name;
	/** @var  string */
	private $description;
	/** @var  string */
	private $type;
	/** @var  string */
	private $access = 'private';
	/** @var bool */
	private $primary;
	/** @var bool */
	private $autoIncrementing;
	/** @var int */
	private $length;
	/** @var  bool */
	private $nullable;

	/**
	 * Getter
	 * @return boolean
	 */
	public function isNullable() {
		return $this->nullable;
	}

	/**
	 * Setter
	 * @param boolean $nullable
	 */
	public function setNullable($nullable) {
		$this->nullable = $nullable;
	}


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
	public function isAutoIncrementing() {
		return $this->autoIncrementing;
	}

	/**
	 * Setter
	 * @param boolean $autoIncrementing
	 */
	public function setAutoIncrementing($autoIncrementing) {
		$this->autoIncrementing = $autoIncrementing;
	}

	/**
	 * Getter
	 * @return int
	 */
	public function getLength() {
		return $this->length;
	}

	/**
	 * Setter
	 * @param int $length
	 */
	public function setLength($length) {
		$this->length = $length;
	}


	/**
	 * Getter
	 * @return string
	 */
	public function getAccess() {
		return $this->access;
	}

	/**
	 * Setter
	 * @param string $access
	 */
	public function setAccess($access) {
		$this->access = $access;
	}

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
	 * @return string
	 */
	public function getDescription() {
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
	public function getType() {
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
	 * Gets the string version of this method
	 * @return string
	 */
	public function toString() {
		$description = $this->getDescription();
		if($description=='') {
			$description = $this->getName();
		}
		$buffer = '	/**
	 * '.$description."\n";
		if($this->isPrimary()) {
			$buffer .= '	 * @Primary'."\n";
		}
		if($this->isAutoIncrementing()) {
			$buffer .= '	 * @Identity'."\n";
		}
		$buffer .= '	 * @Column(type="'.$this->getType().'"'
			.(!is_null($this->getLength())?', length='.$this->getLength():'')
			.', nullable='.($this->isNullable()?'true':'false').')'."\n";
		$buffer .= '	 */
	'.$this->getAccess().' $'.$this->getName().';
';
		return $buffer;
	}


}