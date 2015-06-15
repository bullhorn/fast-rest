<?php
namespace Phalcon\ApiGenerator\Api\Services\ControllerHelper;
use Phalcon\Http\Request;
use Phalcon\Http\Request\Exception;
class Search {
	/** @var  Search[] */
	private $children;
	/** @var  string[] */
	private $fields;
	/** @var  string|null */
	private $alias;

	/**
	 * Constructor
	 * @param \stdClass $params
	 * @param string[]  $whiteList
	 * @param string    $alias
	 */
	public function __construct(\stdClass $params, array $whiteList, $alias=null) {
		$this->buildParams($params, $whiteList);
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
	 * @param string[]  $whiteList
	 *
	 * @return void
	 * @throws Exception
	 */
	private function buildParams(\stdClass $params, array $whiteList) {
		$isRoot = is_null($this->getAlias());
		$children = array();
		$fields = array();
		foreach($params as $subAlias=>$value) {
			if(is_array($value)) {
				throw new Exception('Search Value cannot be an array', 400);
			} elseif(is_object($value) && get_class($value) == 'stdClass') {
				$children[] = new Search($value, ucfirst($subAlias));
			} else { //For current object
				if(!$isRoot || !in_array($subAlias, $whiteList)) {
					$fields[$subAlias] = $value;
				}
			}
		}
		$this->setChildren($children);
		$this->setFields($fields);
	}

	/**
	 * Getter
	 * @return Search[]
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * Setter
	 * @param Search[] $children
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