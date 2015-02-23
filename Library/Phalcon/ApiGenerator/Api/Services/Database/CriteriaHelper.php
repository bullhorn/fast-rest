<?php
namespace Phalcon\ApiGenerator\Api\Services\Database;
use Phalcon\Mvc\Model\Criteria as Criteria;
use Phalcon\Mvc\Model\Resultset\Simple as ResultSet;
class CriteriaHelper {
	/** @var  Criteria */
	private $criteria;
	/** @var  string[] */
	private $groupBys = [];

	/**
	 * Constructor
	 * @param Criteria $criteria
	 */
	public function __construct(Criteria $criteria) {
		$this->setCriteria($criteria);
	}

	/**
	 * Get group bys
	 * @return \string[]
	 */
	public function getGroupBys() {
		return $this->groupBys;
	}

	/**
	 * Sets the group bys
	 * @param \string[] $groupBys
	 */
	public function setGroupBys(array $groupBys) {
		$this->groupBys = $groupBys;
	}



	/**
	 * Getter
	 * @return Criteria
	 */
	public function getCriteria() {
		return $this->criteria;
	}

	/**
	 * Setter
	 * @param Criteria $criteria
	 */
	private function setCriteria(Criteria $criteria) {
		$this->criteria = $criteria;
	}


	/**
	 * This gets the current joins
	 * @return string[]
	 */
	public function getJoins() {
		$params = $this->getCriteria()->getParams();
		if(array_key_exists('joins', $params)) {
			return $params['joins'];
		} else {
			return array();
		}
	}

	/**
	 * Gets the next param id that can be used to make sure it is unique
	 * @return int
	 */
	public function getParamId() {
		$params = $this->getCriteria()->getParams();
		if(array_key_exists('bind', $params)) {
			return sizeOf($params['bind']);
		} else {
			return 0;
		}
	}

	/**
	 * Executes a find using the parameters built with the criteria
	 * @return ResultSet
	 */
	public function execute() {
		$modelName = $this->getCriteria()->getModelName();
		$params = $this->getCriteria()->getParams();
		if(!empty($this->getGroupBys())) {
			$params['group'] = $this->getGroupBys();
		}
		return $modelName::find($params);
	}
}