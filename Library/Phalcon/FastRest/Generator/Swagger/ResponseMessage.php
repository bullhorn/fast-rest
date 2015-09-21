<?php
namespace Phalcon\FastRest\Generator\Swagger;

class ResponseMessage {
	/** @var int */
	private $code;
	/** @var  string */
	private $message;
	/** @var  string */
	private $responseModel;

	/**
	 * Getter
	 * @return int
	 */
	private function getCode() {
		return $this->code;
	}

	/**
	 * Setter
	 * @param int $code
	 */
	public function setCode($code) {
		$this->code = $code;
	}

	/**
	 * Getter
	 * @return string
	 */
	private function getMessage() {
		return $this->message;
	}

	/**
	 * Setter
	 * @param string $message
	 */
	public function setMessage($message) {
		$this->message = $message;
	}

	/**
	 * Getter
	 * @return string
	 */
	private function getResponseModel() {
		return $this->responseModel;
	}

	/**
	 * Setter
	 * @param string $responseModel
	 */
	public function setResponseModel($responseModel) {
		$this->responseModel = $responseModel;
	}



	/**
	 * toString
	 * @return string
	 */
	public function __toString() {
		$parts = array(
			'			code="'.$this->getCode().'"',
			'			message="'.$this->getMessage().'"'
		);
		if(!is_null($this->getResponseModel())) {
			$parts[] = '			responseModel="'.$this->getResponseModel().'"';
		}
		return '		@SWG\ResponseMessage (
'.implode(",\n", $parts).'
		)';
	}
}