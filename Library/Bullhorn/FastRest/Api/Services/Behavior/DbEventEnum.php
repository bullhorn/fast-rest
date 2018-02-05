<?php
namespace Bullhorn\FastRest\Api\Services\Behavior;
use Bullhorn\FastRest\Api\Services\SplEnum;

class DbEventEnum extends SplEnum {
    const AFTER_CREATE = 'afterCreate';
    const AFTER_DELETE = 'afterDelete';
    const AFTER_UPDATE = 'afterUpdate';
    const AFTER_SAVE = 'afterSave';
    const AFTER_VALIDATION = 'afterValidation';
    const AFTER_VALIDATION_ON_CREATE = 'afterValidationOnCreate';
    const AFTER_VALIDATION_ON_UPDATE = 'afterValidationOnUpdate';
    const BEFORE_VALIDATION = 'beforeValidation';
    const BEFORE_CREATE = 'beforeCreate';
    const BEFORE_DELETE = 'beforeDelete';
    const BEFORE_SAVE = 'beforeSave';
    const BEFORE_UPDATE = 'beforeUpdate';
    const BEFORE_VALIDATION_ON_CREATE = 'beforeValidationOnCreate';
    const BEFORE_VALIDATION_ON_UPDATE = 'beforeValidationOnUpdate';
    const ON_VALIDATION_FAILS = 'onValidationFails';
    const VALIDATION = 'validation';
}