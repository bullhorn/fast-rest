<?php
namespace Phalcon\FastRest\DbCompare;
use Phalcon\FastRest\Base;
class Line extends Base {
	/** @var  string */
	private $name;
	/** @var  string */
	private $type;
	/** @var  bool */
	private $unsigned;
	/** @var  bool */
	private $nullable;
	/** @var  bool */
	private $autoIncrement = false;
	/** @var  string */
	private $collate;
	/** @var  string */
	private $comment;
	/** @var  string */
	private $default;

	/**
	 * Constructor
	 * @param string $rawString
	 */
	public function __construct($rawString) {
		$this->build($rawString);
	}

	/**
	 * Compares two lines, and returns any errors of non matches
	 *
	 * @param Line   $line
	 * @param string $prefix
	 *
	 * @return string[]
	 * @throws \Exception
	 */
	public function equals(Line $line, $prefix) {
		$prefix .= ': Column ('.$this->getName().'):';
		$errors = [];
		if($line->getName()!==$this->getName()) {
			throw new \Exception('Comparing Incomparable columns');
		}
		if($line->getType()!==$this->getType()) {
			$errors[] = $prefix.'Type Does Not Match:'."\n".$line->getType()."\n".$this->getType();
		}
		if($line->isUnsigned()!==$this->isUnsigned()) {
			$errors[] = $prefix.'Unsigned Does Not Match:'."\n".$line->isUnsigned()."\n".$this->isUnsigned();
		}
		if($line->isNullable()!==$this->isNullable()) {
			$errors[] = $prefix.'Nullable Does Not Match:'."\n".$line->isNullable()."\n".$this->isNullable();
		}
		if($line->isAutoIncrement()!==$this->isAutoIncrement()) {
			$errors[] = $prefix.'AutoIncrement Does Not Match:'."\n".$line->isAutoIncrement()."\n".$this->isAutoIncrement();
		}
		if($line->getCollate()!==$this->getCollate()) {
			$errors[] = $prefix.'Collate Does Not Match:'."\n".$line->getCollate()."\n".$this->getCollate();
		}
		if($line->getComment()!==$this->getComment()) {
			$errors[] = $prefix.'Comment Does Not Match:'."\n".$line->getComment()."\n".$this->getComment();
		}
		if($line->getDefault()!==$this->getDefault()) {
			$errors[] = $prefix.'Default Does Not Match:'."\n".$line->getDefault()."\n".$this->getDefault();
		}
		return $errors;
	}

	/**
	 * parses the raw string and builds the parts
	 *
	 * @param string $rawString
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function build($rawString) {
		$parseHelper = new ParseHelper();
		$rawString = trim($rawString);
		//Parse name
		if(preg_match('@`(?P<name>[^`]+)`@', $rawString, $matches)) {
			$this->setName($matches['name']);
			$rawString = substr($rawString, strlen($matches[0]));
		}

		$rawString = trim($rawString);
		$type = $parseHelper->parseSection($rawString);
		$this->setType($type);
		$rawString = substr($rawString, strlen($type));

		$rawString = trim($rawString);
		if(preg_match('@^unsigned($| )@', $rawString, $matches)) {
			$this->setUnsigned(true);
			$rawString = substr($rawString, strlen($matches[0]));
		}

		$rawString = trim($rawString);
		if(preg_match('@^COLLATE (?P<collate>[^ ]+)($| )@', $rawString, $matches)) {
			$this->setCollate($matches['collate']);
			$rawString = substr($rawString, strlen($matches[0]));
		}

		$rawString = trim($rawString);
		if(preg_match('@^(?P<nullable>NOT NULL|NULL)($| )@', $rawString, $matches)) {
			$this->setNullable($matches['nullable']=='NULL');
			$rawString = substr($rawString, strlen($matches[0]));
		}

		$rawString = trim($rawString);
		if(preg_match('@^AUTO_INCREMENT($| )@', $rawString, $matches)) {
			$this->setAutoIncrement(true);
			$rawString = substr($rawString, strlen($matches[0]));
		}

		$rawString = trim($rawString);
		if(preg_match('@^DEFAULT @', $rawString, $matches)) {
			$rawString = substr($rawString, strlen($matches[0]));
			$default = $parseHelper->parseSection($rawString);
			$this->setDefault($default);
			$rawString = substr($rawString, strlen($default));
		}
		$rawString = trim($rawString);
		if(preg_match('@^COMMENT @', $rawString, $matches)) {
			$rawString = substr($rawString, strlen($matches[0]));
			$comment = $parseHelper->parseSection($rawString);
			$this->setComment($comment);
			$rawString = substr($rawString, strlen($comment));
		}
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
	 * Getter
	 * @return boolean
	 */
	public function isUnsigned() {
		return $this->unsigned;
	}

	/**
	 * Setter
	 * @param boolean $unsigned
	 */
	public function setUnsigned($unsigned) {
		$this->unsigned = $unsigned;
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
	public function setNullable($nullable) {
		$this->nullable = $nullable;
	}

	/**
	 * Getter
	 * @return boolean
	 */
	public function isAutoIncrement() {
		return $this->autoIncrement;
	}

	/**
	 * Setter
	 * @param boolean $autoIncrement
	 */
	public function setAutoIncrement($autoIncrement) {
		$this->autoIncrement = $autoIncrement;
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getCollate() {
		return $this->collate;
	}

	/**
	 * Setter
	 * @param string $collate
	 */
	public function setCollate($collate) {
		$this->collate = $collate;
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
	public function getDefault() {
		return $this->default;
	}

	/**
	 * Setter
	 * @param string $default
	 */
	public function setDefault($default) {
		$this->default = $default;
	}


}