<?php
namespace Phalcon\ApiGenerator\Api\Services\Validator;
use Phalcon\Mvc\Model\ValidatorInterface;
use Phalcon\Mvc\Model\Validator;
use Phalcon\Mvc\EntityInterface;
class EmptyEmail extends Validator implements ValidatorInterface {

	/**
	 * Validates a method
	 *
	 * @param EntityInterface $record
	 *
	 * @return bool
	 */
	public function validate(EntityInterface $record) {
		$field = $this->getOption('field');
		$value = $record->readAttribute($field);
		if($value=='') {
			return true;
		} else {
			if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
				$this->appendMessage($field.' (Invalid Email): '.$value, $field);
				return false;
			} else {
				return true;
			}
		}
	}
}