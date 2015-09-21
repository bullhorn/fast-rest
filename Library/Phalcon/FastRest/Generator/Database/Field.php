<?php
namespace Phalcon\FastRest\Generator\Database;

class Field {
	/** @var  string */
	private $name;
	/** @var  bool */
	private $primary;
	/** @var  bool */
	private $autoIncrementing;
	/** @var  string */
	private $type;
	/** @var  string[] */
	private $enumOptions = null;
	/** @var  int */
	private $length = null;
	/** @var  bool */
	private $nullable;
	/** @var  string */
	private $description;
	/** @var  string */
	private $tableName;
	/** @var  string */
	private $swaggerType;
	/** @var string */
	private $swaggerFormat;
	/** @var  string */
	private $comment;

	/**
	 * Constructor
	 * @param \stdClass $result                         Result from SHOW COLUMNS FROM `table`
	 * @param string    $tableName
	 * @param \stdClass $informationSchemaColumnsResult
	 */
	public function __construct(\stdClass $result, $tableName, \stdClass $informationSchemaColumnsResult) {
		$this->setName($result->Field);
		$this->setPrimary($result->Key=='PRI');
		$this->setAutoIncrementing($result->Extra=='auto_increment');
		$this->setComment($informationSchemaColumnsResult->COLUMN_COMMENT);
		$fieldType = 'string';
		$swaggerType = 'string';
		$swaggerFormat = '';
		if(preg_match('@^tinyint@', $result->Type)) {
			$fieldType = 'bool';
			$swaggerType = 'boolean';
		} elseif (preg_match('@^int@', $result->Type)) {
			$fieldType = 'int';
			$swaggerType = 'integer';
			$swaggerFormat = 'int32';
		} elseif (preg_match('@^varchar\((?P<length>\d+)\)@', $result->Type, $matches)) {
			$fieldType = 'string';
			$swaggerType = 'string';
			$this->setLength($matches['length']);
		} elseif (preg_match('@^decimal@', $result->Type)) {
			$fieldType = 'double';
			$swaggerType = 'number';
			$swaggerFormat = 'double';
		} elseif (preg_match('@^float@', $result->Type)) {
			$fieldType = 'double';
			$swaggerType = 'number';
			$swaggerFormat = 'double';
		} elseif (preg_match('@^enum\(\'(?P<matches>.*)\'\)$@', $result->Type, $matches)) {
			$fieldType = 'string';
			$swaggerType = 'string';
			$this->setEnumOptions(explode('\',\'', $matches['matches']));
		} elseif (preg_match('@^(datetime|timestamp)@', $result->Type)) {
			$fieldType = 'DateTime';
			$swaggerType = 'string';
			$swaggerFormat = 'datetime';
		} elseif (preg_match('@^date@', $result->Type)) {
			$fieldType = 'Date';
			$swaggerType = 'string';
			$swaggerFormat = 'date';
		}
		$this->setType($fieldType);
		$this->setSwaggerFormat($swaggerFormat);
		$this->setSwaggerType($swaggerType);
		$this->setNullable($result->Null=='YES');
		if($this->getComment()=='') {
			$description = ucfirst($result->Field);
		} else {
			$description = $this->getComment();
		}
		$this->setDescription($description);
		$this->setTableName($tableName);
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getComment() {
		return $this->comment;
	}

	/**
	 * Setter
	 * @param string $comment
	 */
	public function setComment($comment) {
		$this->comment = $comment;
	}



	/**
	 * Getter
	 * @return string
	 */
	public function getSwaggerType() {
		return $this->swaggerType;
	}

	/**
	 * Setter
	 * @param string $swaggerType
	 */
	private function setSwaggerType($swaggerType) {
		$this->swaggerType = $swaggerType;
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getSwaggerFormat() {
		return $this->swaggerFormat;
	}

	/**
	 * Setter
	 * @param string $swaggerFormat
	 */
	private function setSwaggerFormat($swaggerFormat) {
		$this->swaggerFormat = $swaggerFormat;
	}



	/**
	 * Getter
	 * @return string
	 */
	private function getTableName() {
		return $this->tableName;
	}

	/**
	 * Setter
	 * @param string $tableName
	 */
	private function setTableName($tableName) {
		$this->tableName = $tableName;
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
	private function setDescription($description) {
		$this->description = $description;
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
	private function setLength($length) {
		$this->length = $length;
	}

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
	private function setNullable($nullable) {
		$this->nullable = $nullable;
	}



	/**
	 * Getter, null if not an enum
	 * @return string[]|null
	 */
	public function getEnumOptions() {
		return $this->enumOptions;
	}

	/**
	 * Setter
	 * @param string[] $enumOptions
	 */
	public function setEnumOptions(array $enumOptions) {
		$this->enumOptions = $enumOptions;
	}

	/**
	 * Gets the name without the table name
	 * @return string
	 */
	public function getShortName() {
		$name = $this->getName();
		//Strip out the table name
		if(substr($name, 0, strlen($this->getTableName()))==$this->getTableName() && lcfirst(substr($name, strlen($this->getTableName())))!='source') {
			$name = lcfirst(substr($name, strlen($this->getTableName())));
		}
		return $name;
	}

	/**
	 * getter
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * setter
	 * @param string $name
	 */
	private function setName($name) {
		$this->name = $name;
	}

	/**
	 * getter
	 * @return boolean
	 */
	public function isPrimary() {
		return $this->primary;
	}

	/**
	 * setter
	 * @param boolean $primary
	 */
	private function setPrimary($primary) {
		$this->primary = $primary;
	}

	/**
	 * getter
	 * @return boolean
	 */
	public function isAutoIncrementing() {
		return $this->autoIncrementing;
	}

	/**
	 * private
	 * @param boolean $autoIncrementing
	 */
	private function setAutoIncrementing($autoIncrementing) {
		$this->autoIncrementing = $autoIncrementing;
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
	private function setType($type) {
		$this->type = $type;
	}





}