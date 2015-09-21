<?php
namespace Bullhorn\FastRest\Generator\Swagger;

class Operation {
	/** @var  Parameter[] */
	private $parameters = [];
	/** @var  ResponseMessage[] */
	private $responseMessages = [];
	/** @var  string */
	private $method;
	/** @var  string */
	private $summary;
	/** @var  string */
	private $notes;

	/**
	 * Getter
	 * @return string
	 */
	private function getMethod() {
		return $this->method;
	}

	/**
	 * Setter
	 * @param string $method
	 */
	public function setMethod($method) {
		$this->method = $method;
	}

	/**
	 * Getter
	 * @return string
	 */
	private function getSummary() {
		return $this->summary;
	}

	/**
	 * Setter
	 * @param string $summary
	 */
	public function setSummary($summary) {
		$this->summary = $summary;
	}

	/**
	 * Getter
	 * @return string
	 */
	private function getNotes() {
		return $this->notes;
	}

	/**
	 * Setter
	 * @param string $notes
	 */
	public function setNotes($notes) {
		$this->notes = $notes;
	}


	
	/**
	 * Getter
	 * @return ResponseMessage[]
	 */
	private function getResponseMessages() {
		return $this->responseMessages;
	}
	
	/**
	 * Setter
	 * @param ResponseMessage[] $responseMessages
	 */
	private function setResponseMessages(array $responseMessages) {
		$this->responseMessages = $responseMessages;
	}
	
	/**
	 * Adds new responseMessage
	 *
	 * @param ResponseMessage $responseMessage
	 *
	 * @return void
	 */
	public function addResponseMessage(ResponseMessage $responseMessage) {
		$responseMessages = $this->getResponseMessages();
		$responseMessages[] = $responseMessage;
		$this->setResponseMessages($responseMessages);
	}
	
	/**
	 * Getter
	 * @return Parameter[]
	 */
	private function getParameters() {
		return $this->parameters;
	}

	/**
	 * Setter
	 * @param Parameter[] $parameters
	 */
	private function setParameters(array $parameters) {
		$this->parameters = $parameters;
	}

	/**
	 * Adds new parameter
	 *
	 * @param Parameter $parameter
	 *
	 * @return void
	 */
	public function addParameter(Parameter $parameter) {
		$parameters = $this->getParameters();
		$parameters[] = $parameter;
		$this->setParameters($parameters);
	}

	/**
	 * Gets the string version
	 * @return string
	 */
	public function __toString() {
		$parts = array(
			'		method="'.$this->getMethod().'"',
			'		summary="'.$this->getSummary().'"',
			'		notes="'.$this->getNotes().'"'
		);
		foreach($this->getResponseMessages() as $responseMessage) {
			$parts[] = $responseMessage->__toString();
		}
		foreach($this->getParameters() as $parameter) {
			$parts[] = $parameter->__toString();
		}
		return '	@SWG\Operation (
'.implode(",\n", $parts).'
	)';
	}
}