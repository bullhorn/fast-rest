<?php
namespace Bullhorn\FastRest\Api\Services\Database;
use Phalcon\Mvc\Model\Criteria as Criteria;
use Phalcon\Mvc\Model\Resultset\Simple as ResultSet;
use Phalcon\Mvc\Model\Row;
class CriteriaHelper {
	/** @var  Criteria */
	private $criteria;
	/** @var  string[] */
	private $groupBys = [];
	/** @var int */
	private $localParamCount = 0;

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
	 * Increment the next local param count
	 * @return int
	 */
	private function incrementLocalParamCount() {
		$count = $this->getLocalParamCount();
		$count++;
		$this->setLocalParamCount($count);
		return $count;
	}

	/**
	 * Getter
	 * @return int
	 */
	private function getLocalParamCount() {
		return $this->localParamCount;
	}

	/**
	 * Setter
	 * @param int $localParamCount
	 */
	private function setLocalParamCount($localParamCount) {
		$this->localParamCount = $localParamCount;
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
	 * Appends a condition to the current conditions using an AND operator
	 *
	 * @param string $conditions
	 * @param array  $bindParams
	 * @param array  $bindTypes
	 *
	 * @return $this
	 */
	public function andWhere($conditions, array $bindParams=null, array $bindTypes=null) {
		if(!is_null($bindParams)) {
			$tmpBindParams = array();
			foreach($bindParams as $key=>$value) {
				if(is_int($key)) {
					$param = 'criteriaHelper'.$this->incrementLocalParamCount();
					$tmpBindParams[$param] = $value;
					$conditions = str_replace('?'.$key, ':'.$param.':', $conditions);
				} else {
					$tmpBindParams[$key] = $value;
				}
			}
			$bindParams = $tmpBindParams;
		}
		$this->getCriteria()->andWhere($conditions, $bindParams, $bindTypes);
		return $this;
	}

	/**
	 * Executes a find using the parameters built with the criteria
	 * @return ResultSet|Row[]
	 */
	public function execute() {
		$modelName = $this->getCriteria()->getModelName();
		$params = $this->getCriteria()->getParams();
		if(!empty($this->getGroupBys())) {
			$params['group'] = $this->getGroupBys();
		}
		return $modelName::find($params);
	}


	/**
	 * Converts an array into
	 *
	 * @param array $list              if this is an array of objects that have the getId method, it uses those ids instead
	 * @param array &$params
	 * @param int   $currentParamCount
	 *
	 * @return string sql
	 */
	public function listToIn(array $list, &$params, $currentParamCount) {
		$currentParamCount += sizeOf($params);
		$sql = '';
		$first = true;
		foreach($list as $key=>$value) {
			if(is_object($value) && method_exists($value, 'getId')) {
				$value = $value->getId();
			}
			if($first) {
				$first = false;
			} else {
				$sql .= ',';
			}
			$sql .= '?'.$currentParamCount;
			$params[$currentParamCount] = $value;
			$currentParamCount++;
		}
		return $sql;
	}

	/**
	 * Adds a new parameter
	 *
	 * @param string $value
	 * @param array  &$params
	 * @param int    $currentParamCount
	 *
	 * @return string
	 */
	public function addParam($value, &$params, $currentParamCount) {
		$count = $currentParamCount+sizeOf($params);
		$params[] = $value;
		return '?'.$count;
	}

}