<?php
namespace YourService\Controllers\Api;
use Bullhorn\FastRest\Api\Controllers\Base as ApiGeneratorBase;
use Phalcon\Http\Request\Exception;
use Phalcon\DI;
/**
 * Class ControllerBase
 * @package Api\v1_0\Controllers
 */
abstract class ControllerBase extends ApiGeneratorBase {

	/**
	 * Gets a list of parameters that are always allowed in the query, aka any tokens or authentication information
	 * @return string[]
	 */
	protected function getQueryWhiteList() {
		return [];
	}

	/**
	 * Validates that they have a valid login
	 * @return void
	 * @throws Exception
	 */
	protected function validateLogin() {
		return; //No Validation
	}

}
