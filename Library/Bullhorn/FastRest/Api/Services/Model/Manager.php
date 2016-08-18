<?php
namespace Bullhorn\FastRest\Api\Services\Model;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Manager as ModelManager;

class Manager extends ModelManager {

    /**
     * clearReusableForModel
     * @param Model $model
     * @return void
     */
    public function clearReusableForModel(Model $model) {
        $values = $this->_reusable;
        if(is_null($values)) {
            return;
        }
        $className = get_class($model);
        foreach($values as $key=>$value) {
            if(strpos($key, $className)===0) {
                unset($values[$key]);
            }
        }
        $this->_reusable = $values;
    }
}