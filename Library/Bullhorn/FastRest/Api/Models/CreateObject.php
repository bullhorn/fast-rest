<?php
namespace Bullhorn\FastRest\Api\Models;
use Bullhorn\FastRest\Base as ApiBase;
use Phalcon\Http\Request\Exception;

class CreateObject extends ApiBase {
	/** @var \stdClass $postParams */
	private $postParams;
	/** @var  ControllerModelInterface */
	private $entity;
	/** @var  int */
	private $statusCode = 201;
	/** @var  Exception[] */
	private $errors = [];

	/**
	 * CreateObject constructor.
	 * @param \stdClass $postParams
	 */
	public function __construct(\stdClass $postParams) {
		$this->setPostParams($postParams);
	}

	/**
	 * Getter
	 * @return \stdClass
	 */
	public function getPostParams() {
		return $this->postParams;
	}

	/**
	 * Setter
	 * @param \stdClass $postParams
	 */
	public function setPostParams($postParams) {
		$this->postParams = $postParams;
	}



	/**
	 * Getter
	 * @return ControllerModelInterface
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 * Setter
	 * @param ControllerModelInterface $entity
	 */
	public function setEntity($entity) {
		$this->entity = $entity;
	}

	/**
	 * Getter
	 * @return int
	 */
	public function getStatusCode() {
		return $this->statusCode;
	}

	/**
	 * Setter
	 * @param int $statusCode
	 */
	public function setStatusCode($statusCode) {
		$this->statusCode = $statusCode;
	}

	/**
	 * Getter
	 * @return \Phalcon\Http\Request\Exception[]
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Setter
	 * @param \Phalcon\Http\Request\Exception[] $errors
	 */
	public function setErrors($errors) {
		$this->errors = $errors;
	}


}