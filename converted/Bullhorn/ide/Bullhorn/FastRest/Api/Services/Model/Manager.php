<?php
namespace Bullhorn\FastRest\Api\Services\Model
{
    use Phalcon\Mvc\Model;
    use Phalcon\Mvc\Model\Manager as ModelManager;

    class Manager extends ModelManager 
    {
        /**
         * clearReusableForModel
         *
         * @param Model $model
         * @return void
         */
        public function clearReusableForModel($model)
        {}

    }

}

