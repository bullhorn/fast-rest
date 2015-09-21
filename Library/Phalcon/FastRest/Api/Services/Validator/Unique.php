<?php
namespace Phalcon\FastRest\Api\Services\Validator;
use Phalcon\Mvc\Model\ValidatorInterface;
use Phalcon\Mvc\Model\Validator;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as ResultSet;
use Phalcon\Mvc\EntityInterface;
class Unique extends Validator implements ValidatorInterface {

	/**
	 * Validates a method
	 *
	 * @param Model|EntityInterface $record
	 *
	 * @return bool
	 */
	public function validate(EntityInterface $record) {
		$fields = $this->getOption('fields');
		$sql = '1';
		$params = array();
		//Don't check the current object, so add a not on the primary keys
		$primaryKeys = $record->getModelsMetaData()->getPrimaryKeyAttributes($record);
		$columnMap = $record->getModelsMetaData()->getColumnMap($record);
		//Check to see if all of the values are still null
		$someValuesNotNull = false;
		foreach($primaryKeys as $i=>$primaryKey) {
			$field = $columnMap[$primaryKey]; //Get shortened version
			$value = $record->readAttribute($field);
			if(!is_null($value)) {
				$someValuesNotNull = true;
			}
		}
		if($someValuesNotNull) {
			$sql .= ' AND NOT (';
			foreach($primaryKeys as $i => $primaryKey) {
				$field = $columnMap[$primaryKey]; //Get shortened version
				$value = $record->readAttribute($field);
				if($i != 0) {
					$sql .= ' AND';
				}
				$paramKey = sizeOf($params);
				$sql .= ' '.$field.'=?'.$paramKey;
				$params[] = $value;
			}
			$sql .= ')';
		}
		//Add each field
		foreach($fields as $field) {
			$paramKey = sizeOf($params);
			$value = $record->readAttribute($field);
			$sql .= ' AND '.$field.'=?'.$paramKey;
			$params[] = $value;
		}
		/** @var ResultSet $resultSet */
		$resultSet = $record->find(
			[
				'conditions' => $sql,
				'bind'       => $params,
				'limit'      => 1
			]
		);
		//If one is found, give an
		if($resultSet->count()==0) {
			return true;
		} else {
			$this->appendMessage($this->getOption('message'));
			return false;
		}

	}
}