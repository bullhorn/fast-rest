<?php
namespace Bullhorn\FastRest\Api\Services\ControllerHelper;
use Phalcon\Http\Request;
use Bullhorn\FastRest\Api\Services\Filter;
use Phalcon\Http\Request\Exception;

/**
 * Class Save
 */
class Params extends Base {

	/** @var  Request */
	private $request;
	/** @var  \stdClass[] */
	private $params;
	/** @var  Filter */
	private $filter;

	/**
	 * Constructor
	 * @param Request $request
	 */
	public function __construct(Request $request) {
		$this->setRequest($request);
		$this->loadParams();
		$this->setFilter(new Filter());
	}

	/**
	 * Getter
	 * @return Filter
	 */
	private function getFilter() {
		return $this->filter;
	}

	/**
	 * Setter
	 * @param Filter $filter
	 */
	private function setFilter(Filter $filter) {
		$this->filter = $filter;
	}



	/**
	 * setParam
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function setParam($name, $value) {
		$params = $this->getParams();
		$params->$name = $value;
		$this->setParams($params);
	}

	/**
	 * getParam
	 *
	 * @param string       $name
	 * @param string|array $filters
	 *
	 * @return mixed
	 * @throws ParamNotFoundException
	 */
	public function getParam($name, $filters=null) {
		$params = $this->getParams();
		if(property_exists($params, $name)) {
			$value = $params->$name;
			if(!is_null($filters)) {
				$value = $this->getFilter()->sanitize($value, $filters);
			}
			return $value;
		} else {
			throw new ParamNotFoundException('Param Not Found: '.$name);
		}
	}

	/**
	 * Getter
	 * @return \stdClass|\stdClass[]
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * Setter
	 * @param \stdClass|\stdClass[] $params
	 */
	private function setParams($params) {
		$this->params = $params;
	}


	/**
	 * Getter
	 * @return Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Setter
	 * @param Request $request
	 */
	private function setRequest(Request $request) {
		$this->request = $request;
	}

	private function dispatchJsonError() {
		$error = 'JSON Decode Error';
		switch(json_last_error()) {
			case JSON_ERROR_DEPTH:
				$error .= ' - Maximum stack depth exceeded';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$error .= ' - Underflow or the modes mismatch';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$error .= ' - Unexpected control character found';
				break;
			case JSON_ERROR_SYNTAX:
				$error .= ' - Syntax error, malformed JSON';
				break;
			case JSON_ERROR_UTF8:
				$error .= ' - Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
			default:
				$error .= ' - Unknown error';
				break;
		}
		throw new Exception($error, 400);
	}

	/**
	 * Gets the parameters, sorted from parent to child
	 * @return \stdClass
	 * @throws Exception
	 */
	private function loadParams() {
		if($this->getRequest()->getRawBody()==''
			&& empty($this->getRequest()->getPost())
			&& empty($this->getRequest()->getPut())
			&& empty($this->getRequest()->getUploadedFiles())
		) {
			throw new Exception('No Data Passed', 400);
		}
		$bulkCreation = false;
		if(!is_null($this->getRequest()->getJsonRawBody())) {
			$params = $this->getRequest()->getJsonRawBody();
			$delimiter = '.';
			if(is_array($params)) {
				$bulkCreation = true;
			}
		} else {
			if($this->getRequest()->isPost()) {
				$params = $this->getRequest()->getPost();
			} else {
				$params = $this->getRequest()->getPut();
			}
			$delimiter = '_';
		}
		if(sizeOf($params)==0 && json_last_error()!=JSON_ERROR_NONE) {
			$this->dispatchJsonError();
		}
		if($bulkCreation) { //Bulk Creation
			$outputParams = [];
			foreach($params as $param) {
				$helper = new SplitHelper($delimiter);
				$outputParams[] = $helper->convert((array)$param);
			}
			$this->setParams($outputParams);
		} else {
			$helper = new SplitHelper($delimiter);
			$this->setParams($helper->convert((array)$params));
		}
	}

}