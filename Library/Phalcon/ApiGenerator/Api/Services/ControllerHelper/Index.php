<?php
namespace Phalcon\ApiGenerator\Api\Services\ControllerHelper;
use Phalcon\ApiGenerator\Api\Services\Database\CriteriaHelper;
use Phalcon\Mvc\Model\Resultset\Simple as ResultSet;
use Phalcon\Http\Request;
use Phalcon\ApiGenerator\Api\Models\ApiInterface;
use Phalcon\ApiGenerator\Api\Models\ControllerModelInterface;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Request\Exception;
use Phalcon\Mvc\Url;

class Index extends Base {
	/** @var  Request */
	private $request;
	/** @var  ControllerModelInterface */
	private $entityFactory;
	/** @var  Criteria */
	private $criteria;
	/** @var  int */
	private $count;
	/** @var  ResultSet */
	private $resultSet;
	/** @var  CriteriaHelper */
	private $criteriaHelper;
	/** @var string[] Fields that are always allowed  */
	private $whiteList = ['fields','start','count','sort'];

	/**
	 * Constructor
	 * @param Request                  $request
	 * @param ControllerModelInterface $entityFactory
	 * @param string[]                 $whiteList     The list of fields that are always allowed
	 */
	public function __construct(Request $request, ControllerModelInterface $entityFactory, array $whiteList = []) {
		$this->addToWhiteList($whiteList);
		$this->setRequest($request);
		$this->setEntityFactory($entityFactory);
		$this->setCriteria($this->getEntityFactory()->query());
		$this->setCriteriaHelper(new CriteriaHelper($this->getCriteria()));
		$entityFactory->buildListCriteria($this->getCriteriaHelper());
		$this->buildSearchCriteria();
		$this->buildSortParameter();
		$this->buildGroupBy();
		/** @var ResultSet $countResultSet */
		$countResultSet = $this->getCriteriaHelper()->execute();
		$this->setCount($countResultSet->count());
		$this->buildLimit();
		$this->setResultSet($this->getCriteriaHelper()->execute());
	}

	/**
	 * Gets the whitelisted list of fields always allowed
	 * @return \string[]
	 */
	private function getWhiteList() {
		return $this->whiteList;
	}

	/**
	 * Sets the whitelisted list of fields always allowed
	 * @param \string[] $whiteList
	 * @throws \Exception
	 */
	private function setWhiteList(array $whiteList) {
		$this->whiteList = $whiteList;
	}

	/**
	 * Add Fields to the white list so they are ignored
	 *
	 * @param array $whiteList
	 *
	 * @return void
	 */
	private function addToWhiteList(array $whiteList) {
		$current = $this->getWhiteList();
		$current = array_merge($current, $whiteList);
		$this->setWhiteList($current);
	}

	/**
	 * Adds the group by to make sure that only one result is returned for each main entity
	 * @return void
	 */
	private function buildGroupBy() {
		$this->getCriteriaHelper()->setGroupBys([get_class($this->getEntityFactory()).'.id']);
	}

	/**
	 * Getter
	 * @return CriteriaHelper
	 */
	private function getCriteriaHelper() {
		return $this->criteriaHelper;
	}

	/**
	 * Setter
	 * @param CriteriaHelper $criteriaHelper
	 */
	private function setCriteriaHelper($criteriaHelper) {
		$this->criteriaHelper = $criteriaHelper;
	}

	/**
	 * Getter
	 * @return Criteria
	 */
	private function getCriteria() {
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
	 * Getter
	 * @return ControllerModelInterface
	 */
	private function getEntityFactory() {
		return $this->entityFactory;
	}

	/**
	 * Setter
	 * @param ControllerModelInterface $entityFactory
	 */
	private function setEntityFactory(ControllerModelInterface $entityFactory) {
		$this->entityFactory = $entityFactory;
	}



	/**
	 * Getter
	 * @return Request
	 */
	private function getRequest() {
		return $this->request;
	}

	/**
	 * Setter
	 * @param Request $request
	 */
	private function setRequest(Request $request) {
		$this->request = $request;
	}

	/**
	 * Builds the search criteria
	 * @return void
	 */
	private function buildSearchCriteria() {
		$helper = new SplitHelper('_');
		$params = $helper->convert($this->getRequest()->getQuery());
		$this->buildSearchFieldsRecursive($params, $this->getEntityFactory());
	}

	/**
	 * Builds the search fields
	 *
	 * @param \stdClass    $params
	 * @param ApiInterface $entity
	 * @param string       $alias
	 *
	 * @return void
	 * @throws Exception
	 */
	private function buildSearchFieldsRecursive(\stdClass $params, ApiInterface $entity, $alias = null) {
		$isRoot = false;
		if(is_null($alias)) {
			$isRoot = true;
			$alias = get_class($entity);
		}
		foreach($params as $key=>$value) {
			if(is_array($value)) {
				throw new Exception('Search Value cannot be an array', 400);
			} elseif(is_object($value) && get_class($value)=='stdClass') {
				$subAlias = ucfirst($key);
				if(in_array($subAlias, $entity->getParentRelationships())) {
					$subEntity = $this->addJoin($entity, $subAlias, false);
					$this->buildSearchFieldsRecursive($value, $subEntity, $subAlias);
				} elseif(in_array($subAlias, $entity->getChildrenRelationships())) {
					throw new Exception('Cannot search on children: '.($isRoot?'':$alias.'.').$subAlias, 400);
				} else {
					throw new Exception('Could not find the parent: '.($isRoot?'':$alias.'.').$subAlias, 400);
				}
			} else { //For current object
				if(in_array($key, $entity->getModelsMetaData()->getColumnMap($entity))) {
					$this->addSearchField($key, $value, $entity, $alias);
				} else {
					if(!$isRoot || !in_array($key, $this->getWhiteList())) {
						throw new Exception('Could not find the field: '.($isRoot?'':$alias.'.').$key, 400);
					}
				}
			}
		}
	}

	/**
	 * Adds a specific field
	 *
	 * @param string       $name
	 * @param string       $value
	 * @param ApiInterface $entity
	 * @param string       $alias
	 *
	 * @return void
	 */
	private function addSearchField($name, $value, ApiInterface $entity, $alias) {
		$sql = $alias.'.'.$name;
		$entity->writeAttribute($name, $value);
		$params = array();
		$paramCount = $this->getCriteriaHelper()->getParamId();
		$sql .= '=?'.$paramCount;
		$params[$paramCount] = $entity->readAttribute($name).''; //Make sure to convert to string, for Date and DateTime
		$this->getCriteria()->andWhere($sql, $params);
	}

	/**
	 * Adds a join
	 *
	 * @param ApiInterface $entity
	 * @param string       $alias
	 * @param bool         $forceFind
	 *
	 * @return ApiInterface|false False if sub part was not found
	 * @throws Exception
	 */
	private function addJoin(ApiInterface $entity, $alias, $forceFind) {
		if(in_array($alias, $entity->getParentRelationships())) {
			$referencedModel = $entity->addJoin($this->getCriteriaHelper(), $alias);
			return new $referencedModel();
		} else {
			if($forceFind) {
				throw new Exception('Could Not Find Part in current entity: '.$alias, 400);
			}
			return false;
		}
	}


	/**
	 * Gets sort parameters from the URL.
	 *
	 * @return void
	 */
	protected function buildSortParameter() {
		if($this->getRequest()->getQuery('sort')!='') {
			$sortParameters = explode(",", $this->getRequest()->getQuery('sort'));

			$orderBy = '';
			foreach($sortParameters as $index => $parameter) {
				$parameter = trim($parameter);
				if(substr($parameter, 0, 1) == "-") {
					$parameter = substr($parameter, 1);
					$isAsc = false;
				} else {
					$isAsc = true;
				}
				$parts = explode('.', $parameter);
				$this->buildSortRecursive($this->getEntityFactory(), $parts, $isAsc, $orderBy);
			}
			$this->getCriteria()->orderBy($orderBy);
		}
	}

	/**
	 * Builds the order by
	 *
	 * @param ApiInterface $entity
	 * @param string[]     $parts
	 * @param bool         $isAsc
	 * @param string       &$orderBy
	 * @param string       $alias
	 *
	 * @return void
	 * @throws Exception
	 */
	private function buildSortRecursive(ApiInterface $entity, $parts, $isAsc, &$orderBy, $alias=null) {
		if(is_null($alias)) {
			$alias = get_class($entity);
		}
		$part = array_shift($parts);
		if(sizeOf($parts)==0) { //Is Last part
			if(in_array($part, $entity->getModelsMetaData()->getColumnMap($entity))) {
				if($orderBy!='') {
					$orderBy .= ',';
				}
				$orderBy .= $alias.'.'.$part.' '.($isAsc?'ASC':'DESC');
			} else {
				throw new Exception('Could Not Find sort part: '.$part, 400);
			}
		} else {
			$subAlias = ucfirst($part);
			$subEntity = $this->addJoin($entity, $subAlias, true); //Throws an exception if not found
			$this->buildSortRecursive($subEntity, $parts, $isAsc, $orderBy, $subAlias);
		}
	}

	/**
	 * Builds the limit query
	 * @return void
	 */
	private function buildLimit() {
		$this->getCriteria()->limit($this->getLimit(), $this->getOffset());
	}

	/**
	 * Gets the current offset
	 * @return int
	 */
	private function getOffset() {
		$start = $this->getRequest()->getQuery('start', 'int', 0); //If you don't pass in, 0, min 0
		if($start<0) {
			$start = 0;
		}
		return $start;
	}

	/**
	 * Getter
	 * @return ResultSet
	 */
	public function getResultSet() {
		return $this->resultSet;
	}

	/**
	 * Setter
	 * @param ResultSet $resultSet
	 */
	private function setResultSet($resultSet) {
		$this->resultSet = $resultSet;
	}

	/**
	 * Getter
	 * @return int
	 */
	private function getCount() {
		return $this->count;
	}

	/**
	 * Gets the current limit
	 * @return int
	 */
	private function getLimit() {
		$limit = $this->getRequest()->getQuery('count', 'int', 50);
		if($limit<1) {
			$limit = 1;
		}
		if($limit>500) {
			$limit = 500;
		}
		return $limit;
	}

	/**
	 * Gets the url
	 *
	 * @param string[] $queryParams
	 *
	 * @return string
	 */
	private function generateUrl($queryParams) {
		$params = $this->getRequest()->getQuery();
		//Remove htaccess mod rewrite query
		if(array_key_exists('_url', $params)) {
			unset($params['_url']);
		}
		//Override with the new parameters
		foreach($queryParams as $name=>$value) {
			$params[$name] = $value;
		}
		$url = new Url();
		return  'https://'.$this->getRequest()->getHttpHost().substr($url->get($this->getRequest()->getQuery('_url'), $params), 1);
	}

	/**
	 * Generates the links for the link header
	 * @return string
	 */
	public function generateLinks() {
		return '<'.$this->generateUrl($this->getNextPageQueryCriteria()).'>; rel="next",'
		.'<'.$this->generateUrl($this->getLastPageQueryCriteria()).'>; rel="last",'
		.'<'.$this->generateUrl($this->getFirstPageQueryCriteria()).'>; rel="first",'
		.'<'.$this->generateUrl($this->getPreviousPageQueryCriteria()).'>; rel="prev"';
	}

	/**
	 * Gets the query params for the first page
	 * @return string[]
	 */
	private function getFirstPageQueryCriteria() {
		return array(
			'start' => 0,
			'count' => $this->getLimit()
		);
	}

	/**
	 * Gets the query params for the first page
	 * @return string[]
	 */
	private function getPreviousPageQueryCriteria() {
		$start = $this->getOffset()-$this->getLimit();
		if($start<0) {
			$start = 0;
		}
		return array(
			'start' => $start,
			'count' => $this->getLimit()
		);
	}

	/**
	 * Gets the query params for the first page
	 * @return string[]
	 */
	private function getNextPageQueryCriteria() {
		$start = $this->getOffset()+$this->getLimit();
		return array(
			'start' => $start,
			'count' => $this->getLimit()
		);
	}

	/**
	 * Gets the query params for the first page
	 * @return string[]
	 */
	private function getLastPageQueryCriteria() {
		$start = $this->getCount()-$this->getLimit();
		if($start<0) {
			$start = 0;
		}
		return array(
			'start' => $start,
			'count' => $this->getLimit()
		);
	}

	/**
	 * Setter
	 * @param int $count
	 */
	private function setCount($count) {
		$this->count = $count;
	}


}