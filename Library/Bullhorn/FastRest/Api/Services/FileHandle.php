<?php
namespace Bullhorn\FastRest\Api\Services;
class FileHandle {
	/** @type resource|false */
	private $handle;

	/**
	 * Constructor
	 * @see fopen
	 * @param string $filename
	 * @param string $mode
	 */
	public function __construct($filename, $mode) {
		$this->setHandle(fopen($filename, $mode));
	}

	/**
	 * Destructor, closes the handle
	 */
	public function __destruct() {
		$this->close();
	}

	/**
	 * Writes to the handle
	 * @param string $string
	 * @return int|false returns the number of bytes written, or FALSE on error
	 */
	public function write($string) {
		return fwrite($this->getHandle(), $string);
	}

	/**
	 * Reads from the handle
	 * @param int $length
	 * @return string
	 */
	public function read($length) {
		return fread($this->getHandle(), $length);
	}

	/**
	 * close
	 * @return void
	 */
	public function close() {
		if($this->getHandle()!==false) {
			fclose($this->getHandle());
		}
	}

	/**
	 * Getter
	 * @return resource
	 */
	private function getHandle() {
		return $this->handle;
	}

	/**
	 * Setter
	 * @param resource $handle
	 */
	private function setHandle($handle) {
		$this->handle = $handle;
	}


}