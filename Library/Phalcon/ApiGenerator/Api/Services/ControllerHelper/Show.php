<?php
namespace Phalcon\ApiGenerator\Api\Services\ControllerHelper;
use Phalcon\ApiGenerator\Api\Models\ApiInterface;
use Phalcon\Http\Request;
use Phalcon\Http\Request\Exception;

class Show extends Base {
	/** @var  Request */
	private $request;
	/** @var  ApiInterface */
	private $entity;

	/**
	 * Constructor
	 * @param Request      $request
	 * @param ApiInterface $entity
	 */
	public function __construct(Request $request, ApiInterface $entity) {
		$this->setRequest($request);
		$this->setEntity($entity);
	}

	/**
	 * Generates the std object to show
	 * @return \stdClass
	 */
	public function generate() {
		$output = new \stdClass();
		$this->showRecursive($output, $this->getFields(), $this->getEntity());
		return $output;
	}

	/**
	 * showParentsAndChildren
	 *
	 * @param \stdClass    $output
	 * @param string       $alias
	 * @param ApiInterface $entity
	 * @param \stdClass    $value
	 *
	 * @return void
	 * @throws Exception
	 */
	private function showParentsAndChildren(\stdClass $output, $alias, ApiInterface $entity, \stdClass $value) {
		if(in_array($alias, $entity->getParentRelationships())) {

			/** @var ApiInterface $parentEntity */
			$parentEntity = $entity->getRelated($alias);
			if($parentEntity===false) {//AKA, a nullable vendor
				$output->{$alias} = null;
			} else {
				$output->{$alias} = new \stdClass();
				$this->showRecursive($output->{$alias}, $value, $parentEntity);
			}
		} elseif(in_array($alias, $entity->getChildrenRelationships())) {
			$output->{$alias} = array();
			$resultSet = $entity->getRelated($alias);
			foreach($resultSet as $key=>$currentEntity) {
				$subOutput = new \stdClass();
				$output->{$alias}[] = $subOutput;
				$this->showRecursive($subOutput, $value, $currentEntity);
			}
		} else {
			throw new Exception('Invalid Field, not on the current object, no child or parent found: '.$entity->getEntityName().' with '.$alias, 400);
		}
	}

	/**
	 * Adds to the output object the specified field
	 *
	 * @param \stdClass    $output
	 * @param \stdClass    $fields
	 * @param ApiInterface $entity
	 *
	 * @return void
	 * @throws Exception
	 */
	private function showRecursive(\stdClass $output, \stdClass $fields, ApiInterface $entity) {
		$this->getAcl()->canRead($entity);
		$columns = $entity->getModelsMetaData()->getColumnMap($entity);
		foreach($fields as $key=>$value) {
			if(is_object($value) && get_class($value)=='stdClass') {
				$this->showParentsAndChildren(
					$output,
					ucfirst($key),
					$entity,
					$value
				);
			} else { //For current object
				if($key=='*') { //All parts
					foreach($columns as $column) {
						$this->showField($output, $entity, $column);
					}
				} elseif(in_array($key, $columns)) {
					$this->showField($output, $entity, $key);
				} elseif(in_array($key, $entity->getChildrenRelationships()) || in_array($key, $entity->getParentRelationships())) {
					throw new Exception('Invalid Field, child or parent: '.$entity->getEntityName().' with "'.$key.'". Please add .* to the end to get all related fields', 400);
				} else {
					throw new Exception('Invalid Field, not on the current object: '.$entity->getEntityName().' with '.$key, 400);
				}
			}
		}
	}

	/**
	 * Shows an individual field
	 *
	 * @param \stdClass    $output
	 * @param ApiInterface $entity
	 * @param string       $column
	 *
	 * @return void
	 */
	private function showField(\stdClass $output, ApiInterface $entity, $column) {
		if($this->getAcl()->canReadField($entity, $column)) {
			if(in_array($column, $entity->getUnReadableFields())) {
				$value = null;
			} else {
				$value = $entity->readAttribute($column);
				//Fix for Date and DateTime
				if(is_object($value) && method_exists($value, '__toString')) {
					$value = $value->__toString();
				}
			}
			$output->{$column} = $value;
		}
	}

	/**
	 * Returns the fields
	 * @return \stdClass
	 */
	private function getFields() {
		$rawFields = $this->getRequest()->get('fields', null, '*');
		$fields = explode(',', $rawFields);
		$helper = new SplitHelper('.');
		$keyedFields = array_fill_keys($fields, null);
		return $helper->convert($keyedFields);
	}

	/**
	 * Getter
	 * @return ApiInterface
	 */
	private function getEntity() {
		return $this->entity;
	}

	/**
	 * Setter
	 * @param ApiInterface $entity
	 */
	private function setEntity(ApiInterface $entity) {
		$this->entity = $entity;
	}

	/**
	 * Getter
	 * @return Request
	 */
	private function getRequest() {
		return $this->request;
	}

	/**
	 * Setter
	 * @param Request $request
	 */
	private function setRequest(Request $request) {
		$this->request = $request;
	}

}