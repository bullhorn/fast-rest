<?php
namespace Phalcon\ApiGenerator\Generator\Swagger;

class Resource {
	/** @var  string */
	private $apiVersion;
	/** @var  string */
	private $resourcePath;
	/** @var  string */
	private $basePath;
	/** @var  string */
	private $description;

	/**
	 * Getter
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Setter
	 * @param string $description
	 *
	 * @return $this
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}
	
	/**
	 * Getter
	 * @return string
	 */
	private function getApiVersion() {
		return $this->apiVersion;
	}
	
	/**
	 * Setter
	 * @param string $apiVersion
	 *
	 * @return $this
	 */
	public function setApiVersion($apiVersion) {
		$this->apiVersion = $apiVersion;
		return $this;
	}
	
	/**
	 * Getter
	 * @return string
	 */
	private function getResourcePath() {
		return $this->resourcePath;
	}
	
	/**
	 * Setter
	 * @param string $resourcePath
	 *
	 * @return $this
	 */
	public function setResourcePath($resourcePath) {
		$this->resourcePath = $resourcePath;
		return $this;
	}
	
	/**
	 * Getter
	 * @return string
	 */
	private function getBasePath() {
		return $this->basePath;
	}
	
	/**
	 * Setter
	 * @param string $basePath
	 *
	 * @return $this
	 */
	public function setBasePath($basePath) {
		$this->basePath = $basePath;
		return $this;
	}

	/**
	 * toString
	 * @return string
	 */
	public function __toString() {
		$parts = array(
			'	apiVersion="'.$this->getApiVersion().'"',
			'	resourcePath="'.$this->getResourcePath().'"',
			'	basePath="'.$this->getBasePath().'"',
			'	description="'.$this->getDescription().'"',
		);
		return '@SWG\Resource (
'.implode(",\n", $parts).'
)';
	}
}