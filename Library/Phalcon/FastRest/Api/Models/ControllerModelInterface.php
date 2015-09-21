<?php
namespace Phalcon\FastRest\Api\Models;
use Phalcon\FastRest\Api\Services\Database\CriteriaHelper;
interface ControllerModelInterface extends ApiInterface {
	/**
	 * Add to criteria any specific restrictions
	 *
	 * @param CriteriaHelper $criteriaHelper
	 *
	 * @return void
	 */
	public function buildListCriteria(CriteriaHelper $criteriaHelper);
}