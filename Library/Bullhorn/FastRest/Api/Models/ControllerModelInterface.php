<?php
namespace Bullhorn\FastRest\Api\Models;

use Bullhorn\FastRest\Api\Services\Database\CriteriaHelper;

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