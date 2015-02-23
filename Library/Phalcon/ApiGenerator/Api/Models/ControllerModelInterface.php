<?php
namespace Phalcon\ApiGenerator\Api\Models;
use Phalcon\ApiGenerator\Api\Services\Database\CriteriaHelper;
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