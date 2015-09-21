<?php
namespace Phalcon\FastRest\Api\Services\ControllerHelper;
use Phalcon\Http\Request;
use Phalcon\Http\Request\Exception;
class Field {
	/** @var  Field[] */
	private $children;
	/** @var  string[] */
	private $fields;
	/** @var  string|null */
	private $alias;

	/**
	 * Constructor
	 * @param \stdClass $params
	 * @param string    $alias
	 */
	public function __construct(\stdClass $params, $alias=null) {
		$this->buildParams($params);
		$this->setAlias($alias);
	}

	/**
	 * Getter
	 * @return null|string
	 */
	public function getAlias() {
		return $this->alias;
	}

	/**
	 * Setter
	 * @param null|string $alias
	 */
	private function setAlias($alias) {
		$this->alias = $alias;
	}

	/**
	 * buildParams
	 *
	 * @param \stdClass $params
	 *
	 * @return void
	 * @throws Exception
	 */
	private function buildParams(\stdClass $params) {
		$children = array();
		$fields = array();
		foreach($params as $subAlias=>$value) {
			if(is_array($value)) {
				throw new Exception('Field Value cannot be an array', 400);
			} elseif(is_object($value) && get_class($value) == 'stdClass') {
				$children[] = new Field($value, ucfirst(trim($subAlias)));
			} else { //For current object
				$fields[] = $subAlias;
			}
		}
		$this->setChildren($children);
		$this->setFields($fields);
	}

	/**
	 * Getter
	 * @return Field[]
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * Setter
	 * @param Field[] $children
	 */
	public function setChildren(array $children) {
		$this->children = $children;
	}

	/**
	 * Getter
	 * @return \string[]
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * Setter
	 * @param \string[] $fields
	 */
	public function setFields($fields) {
		$this->fields = $fields;
	}




}