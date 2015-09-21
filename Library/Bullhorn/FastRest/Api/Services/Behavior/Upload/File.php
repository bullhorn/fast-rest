<?php
namespace Bullhorn\FastRest\Api\Services\Behavior\Upload;
use Phalcon\Http\Request\File as UploadedFile;
use Phalcon\Http\Request\Exception;
class File {
	/** @var  bool */
	private $requiredOnCreate = true;
	/** @var bool  */
	private $requiredOnUpdate = true;
	/** @var  bool */
	private $allowedOnCreate = true;
	/** @var bool  */
	private $allowedOnUpdate = true;
	/** @var  string */
	private $name;
	/** @var  \Closure */
	private $handler;
	/** @var  bool */
	private $used = false;
	/** @var string[]  */
	private $allowedExtensions = [];

	/**
	 * Getter
	 * @return boolean
	 */
	public function isAllowedOnCreate() {
		return $this->allowedOnCreate;
	}

	/**
	 * Setter
	 * @param boolean $allowedOnCreate
	 */
	public function setAllowedOnCreate($allowedOnCreate) {
		$this->allowedOnCreate = $allowedOnCreate;
	}

	/**
	 * Getter
	 * @return boolean
	 */
	public function isAllowedOnUpdate() {
		return $this->allowedOnUpdate;
	}

	/**
	 * Setter
	 * @param boolean $allowedOnUpdate
	 */
	public function setAllowedOnUpdate($allowedOnUpdate) {
		$this->allowedOnUpdate = $allowedOnUpdate;
	}

	/**
	 * Getter
	 * @return string[]
	 */
	public function getAllowedExtensions() {
		return $this->allowedExtensions;
	}

	/**
	 * Setter
	 * @param string[] $allowedExtensions
	 */
	public function setAllowedExtensions(array $allowedExtensions) {
		$this->allowedExtensions = $allowedExtensions;
	}


	/**
	 * Gets if this file was used
	 * @return boolean
	 */
	public function isUsed() {
		return $this->used;
	}

	/**
	 * Sets if this file was used to upload
	 * @param boolean $used
	 */
	private function setUsed($used) {
		$this->used = $used;
	}

	/**
	 * Validates if there are any errors
	 *
	 * @param UploadedFile $uploadedFile
	 *
	 * @return void
	 * @throws Exception
	 */
	private function validateError(UploadedFile $uploadedFile) {
		switch($uploadedFile->getError()) {
			case UPLOAD_ERR_OK:
				//Do Nothing
				break;
			case UPLOAD_ERR_INI_SIZE:
				throw new Exception('File too large, must not be greater than '.ini_get('upload_max_filesize'), 401);
				break;
			case UPLOAD_ERR_NO_FILE:
				throw new Exception('No File Uploaded', 401);
				break;
			default:
				throw new Exception($uploadedFile->getError(), 401);
				break;
		}
	}

	/**
	 * Validates the file extension
	 *
	 * @param UploadedFile $uploadedFile
	 *
	 * @return void
	 * @throws Exception
	 */
	private function validateExtension(UploadedFile $uploadedFile) {
		$pathInfo = pathinfo($uploadedFile->getName());
		if(!isset($pathInfo['extension'])) {
			throw new Exception('No Extension Found('.$uploadedFile->getName().')', 401);
		}
		$extension = strtolower($pathInfo['extension']);
		if(!in_array($extension, $this->getAllowedExtensions())) {
			throw new Exception('Invalid Type of File Uploaded, valid types: '.implode(', ', $this->getAllowedExtensions()), 401);
		}
	}

	/**
	 * Handles the file being uploaded
	 *
	 * @param UploadedFile $uploadedFile
	 *
	 * @return void
	 * @throws Exception
	 */
	public function handle(UploadedFile $uploadedFile) {
		$this->setUsed(true);
		$this->validateError($uploadedFile);
		$this->validateExtension($uploadedFile);
		$handler = $this->getHandler();
		$handler($uploadedFile);
	}

	/**
	 * Getter
	 * @return boolean
	 */
	public function isRequiredOnCreate() {
		return $this->requiredOnCreate;
	}

	/**
	 * Setter
	 * @param boolean $requiredOnCreate
	 */
	public function setRequiredOnCreate($requiredOnCreate) {
		$this->requiredOnCreate = $requiredOnCreate;
	}

	/**
	 * Getter
	 * @return boolean
	 */
	public function isRequiredOnUpdate() {
		return $this->requiredOnUpdate;
	}

	/**
	 * Setter
	 * @param boolean $requiredOnUpdate
	 */
	public function setRequiredOnUpdate($requiredOnUpdate) {
		$this->requiredOnUpdate = $requiredOnUpdate;
	}



	/**
	 * Getter
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Setter
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Getter
	 * @return \Closure
	 */
	public function getHandler() {
		return $this->handler;
	}

	/**
	 * Setter
	 * @param \Closure $handler
	 */
	public function setHandler(\Closure $handler) {
		$this->handler = $handler;
	}


}