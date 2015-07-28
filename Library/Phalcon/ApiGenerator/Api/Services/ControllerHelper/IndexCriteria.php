<?php
namespace Phalcon\ApiGenerator\Api\Services\ControllerHelper;
use Phalcon\Http\Request;
class IndexCriteria {
	/** @var  Request */
	private $request;
	/** @var  Sort[] */
	private $sorts;
	/** @var  Search */
	private $search;
	/** @var  int */
	private $offset;
	/** @var  int */
	private $limit;
	/** @var  string[] */
	private $whiteList = ['fields','start','count','sort', 'token', 'vanityName', 'bhRestToken', 'authenticationKey'];

	/**
	 * Constructor
	 * @param Request  $request
	 * @param string[] $whiteList
	 */
	public function __construct(Request $request, array $whiteList = []) {
		$this->addToWhiteList($whiteList);
		$this->setRequest($request);
		$this->buildSorts();
		$this->buildSearch();
		$this->buildOffset();
		$this->buildLimit();
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
	 * Getter
	 * @return \string[]
	 */
	private function getWhiteList() {
		return $this->whiteList;
	}

	/**
	 * Setter
	 * @param \string[] $whiteList
	 */
	private function setWhiteList($whiteList) {
		$this->whiteList = $whiteList;
	}



	/**
	 * buildLimit
	 * @return void
	 */
	private function buildLimit() {
		$limit = $this->getRequest()->getQuery('count', 'int', 50);
		if($limit<1) {
			$limit = 1;
		}
		if($limit>500) {
			$limit = 500;
		}
		$this->setLimit($limit);
	}

	/**
	 * Getter
	 * @return int
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * Setter
	 * @param int $limit
	 */
	private function setLimit($limit) {
		$this->limit = $limit;
	}



	/**
	 * buildOffset
	 * @return void
	 */
	private function buildOffset() {
		$start = $this->getRequest()->getQuery('start', 'int', 0); //If you don't pass in, 0, min 0
		if($start<0) {
			$start = 0;
		}
		$this->setOffset($start);
	}

	/**
	 * Getter
	 * @return int
	 */
	public function getOffset() {
		return $this->offset;
	}

	/**
	 * Setter
	 * @param int $offset
	 */
	private function setOffset($offset) {
		$this->offset = $offset;
	}



	private function buildSearch() {
		$helper = new SplitHelper('_');
		$params = $helper->convert($this->getRequest()->getQuery());
		$this->setSearch(new Search($params, $this->getWhiteList()));
	}

	/**
	 * Getter
	 * @return Search
	 */
	public function getSearch() {
		return $this->search;
	}

	/**
	 * Setter
	 * @param Search $search
	 */
	public function setSearch(Search $search) {
		$this->search = $search;
	}



	private function buildSorts() {
		$sorts = array();
		if($this->getRequest()->getQuery('sort')!='') {
			$sortParameters = explode(",", $this->getRequest()->getQuery('sort'));

			foreach($sortParameters as $index => $parameter) {

				$parameter = trim($parameter);
				if(substr($parameter, 0, 1) == "-") {
					$parameter = substr($parameter, 1);
					$isAsc = false;
				} else {
					$isAsc = true;
				}
				$parts = explode('.', $parameter);

				$sort = new Sort();
				$sort->setAsc($isAsc);
				$sort->setFields($parts);
				$sorts[] = $sort;
			}
		}
		$this->setSorts($sorts);
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
	 * Getter
	 * @return Sort[]
	 */
	public function getSorts() {
		return $this->sorts;
	}

	/**
	 * Setter
	 * @param Sort[] $sorts
	 */
	private function setSorts(array $sorts) {
		$this->sorts = $sorts;
	}


}