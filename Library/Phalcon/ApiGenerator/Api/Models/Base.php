<?php
namespace Phalcon\ApiGenerator\Api\Models;
use Api\v1_0\Services\Behavior\AuditTrail\AuditTrail;
use Phalcon\ApiGenerator\Api\Services\Database\CriteriaHelper;
use Phalcon\ApiGenerator\Api\Services\Database\Transaction;
use Api\v1_0\Services\Module as ModuleService;
use Phalcon\Mvc\Model;
use Phalcon\ApiGenerator\Api\Services\Filter;
use Phalcon\Mvc\Model\TransactionInterface;
use Phalcon\Mvc\Model\Resultset\Simple as ResultSet;

/**
 * Interface EntityInterface
 * @package Api\v1_0\Services
 */
abstract class Base extends Model {
	/** @var Filter */
	private $filter;
	/** @var  string[] */
	private $defaultRelationships = [];
	/** @var string[] */
	private $unReadableFields = [];
	/** @var string[]  */
	private $automaticallyUpdatedFields = [];
	/** @var CustomRelationship[] */
	private $customParentRelationships = [];

	/**
	 * Constructor
	 * @return void
	 */
	public function onConstruct() {
		$this->generateFilter();
	}

	/**
	 * Getter
	 * @return CustomRelationship[]
	 */
	public function getCustomParentRelationships() {
		return $this->customParentRelationships;
	}

	/**
	 * Setter
	 * @param CustomRelationship[] $customRelationships
	 */
	private function setCustomParentRelationships(array $customRelationships) {
		$this->customParentRelationships = $customRelationships;
	}

	/**
	 * addCustomRelationship
	 *
	 * @param CustomRelationship $customRelationship
	 *
	 * @return void
	 */
	public function addCustomParentRelationship(CustomRelationship $customRelationship) {
		$customRelationships = $this->getCustomParentRelationships();
		$customRelationships[$customRelationship->getAlias()] = $customRelationship;
		$this->setCustomParentRelationships($customRelationships);
	}



	/**
	 * Gets the module service
	 * @return ModuleService
	 */
	protected function getModuleService() {
		return $this->getDi()->get('Module');
	}

	/**
	 * getChangedFields
	 * @return string[]
	 * @throws Model\Exception
	 * @throws \Exception
	 */
	public function getChangedFields() {
		try {
			return parent::getChangedFields();
		} catch(Model\Exception $e) {
			if($e->getMessage()!='The record doesn\'t have a valid data snapshot') {
				throw $e;
			}
			return $this->getModelsMetaData()->getColumnMap($this);
		}
	}

	/**
	 * Initializes the class
	 * @return void
	 */
	public function initialize() {
		$this->keepSnapshots(true);
		$this->setup(
			array(
				'exceptionOnFailedSave' => true, //Throw an exception instead of returning false from saves
				'notNullValidations'    => false //Allow empty strings instead of the wtf new \Phalcon\Db\RawValue('""');
			)
		);
		//Sets to only update the fields that are changed
		$this->useDynamicUpdate(true);
	}

	/**
	 * Sets the default audit trail for all of our models.
	 * @return void
	 */
	protected function addAuditTrailBehavior() {
		$this->addBehavior(new AuditTrail());
	}

	/**
	 * Getter
	 * @return string[]
	 */
	public function getAutomaticallyUpdatedFields() {
		return $this->automaticallyUpdatedFields;
	}

	/**
	 * Setter
	 * @param string[] $automaticallyUpdatedFields
	 */
	private function setAutomaticallyUpdatedFields(array $automaticallyUpdatedFields) {
		$this->automaticallyUpdatedFields = $automaticallyUpdatedFields;
	}

	/**
	 * Adds any new automatically updated fields, so they cannot be updated by the controller
	 *
	 * @param array $automaticallyUpdatedFields
	 *
	 * @return void
	 */
	public function addAutomaticallyUpdatedFields(array $automaticallyUpdatedFields) {
		$fields = $this->getAutomaticallyUpdatedFields();
		foreach($automaticallyUpdatedFields as $field) {
			if(!in_array($field, $fields)) {
				$fields[] = $field;
			}
		}
		$this->setAutomaticallyUpdatedFields($fields);
	}



	/**
	 * Gets the unreadable fields
	 * @return \string[]
	 */
	public function getUnReadableFields() {
		return $this->unReadableFields;
	}

	/**
	 * Sets the unreadable fields
	 * @param \string[] $unReadableFields
	 */
	public function setUnReadableFields(array $unReadableFields) {
		$this->unReadableFields = $unReadableFields;
	}



	/**
	 * Checks if this class exists in the database
	 * @return bool
	 */
	public function inDatabase() {
		return parent::_exists($this->getModelsMetaData(), $this->selectReadConnection());
	}

	/**
	 * Getter
	 * @return \string[]
	 */
	public function getDefaultRelationships() {
		return $this->defaultRelationships;
	}

	/**
	 * Sets the relationship aliases required to load up the defaults
	 * ['employeeType', 'user'] for the employee
	 * @param \string[] $defaultRelationships
	 */
	protected function setDefaultRelationships(array $defaultRelationships) {
		$this->defaultRelationships = $defaultRelationships;
	}




	/**
	 * Generates the filter object
	 * @return void
	 */
	private function generateFilter() {
		$filter = new Filter();
		$this->setFilter($filter);
	}

	/**
	 * Override readAttribute to allow for having custom setMethods
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function readAttribute($name) {
		$method = 'get'.ucfirst($name);
		if(method_exists($this, $method)) {
			return $this->$method();
		} else {
			return parent::readAttribute($name);
		}
	}

	/**
	 * Override writeAttribute to allow for having custom setMethods
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return void
	 */
	public function writeAttribute($name, $value) {
		$method = 'set'.ucfirst($name);
		if(method_exists($this, $method)) {
			$this->$method($value);
		} else {
			parent::writeAttribute($name, $value);
		}
	}

	/**
	 * Getter
	 * @return Filter
	 */
	protected function getFilter() {
		return $this->filter;
	}

	/**
	 * Setter
	 * @param Filter $filter
	 */
	private function setFilter(Filter $filter) {
		$this->filter = $filter;
	}

	/**
	 * This should set any defaults to the current object
	 * @return void
	 */
	abstract public function loadDefaults();

	/**
	 * Gets the entity name
	 * @return string
	 */
	public function getEntityName() {
		$function = new \ReflectionClass($this);
		return $function->getShortName();
	}

	/**
	 * Overwrite skipAttributes to support columnMap
	 *
	 * @param string[] $columns
	 *
	 * @return void
	 */
	public function skipAttributes(array $columns) {
		parent::skipAttributes($this->convertColumnsToRawFields($columns));
	}

	/**
	 * Overwrite skipAttributes to support columnMap. Does not save any passed in values for the specified columns to the database.
	 *
	 * @param string[] $columns
	 *
	 * @return void
	 */
	public function skipAttributesOnUpdate(array $columns) {
		parent::skipAttributesOnUpdate($this->convertColumnsToRawFields($columns));
	}

	/**
	 * Overwrite skipAttributes to support columnMap
	 *
	 * @param string[] $columns
	 *
	 * @return void
	 */
	public function skipAttributesOnCreate(array $columns) {
		parent::skipAttributesOnCreate($this->convertColumnsToRawFields($columns));
	}

	/**
	 * Converts the columns into their original raw fields
	 *
	 * @param string[] $columns
	 *
	 * @return \string[]
	 */
	private function convertColumnsToRawFields(array $columns) {
		$columnMaps = $this->getModelsMetaData()->getColumnMap($this);
		foreach($columns as $key=>$value) {
			if(!array_key_exists($value, $columnMaps)) {
				//Look up in the changed values
				$index = array_search($value, $columnMaps);
				if($index===false) {
					throw new \InvalidArgumentException('Invalid Column, could not find: '.$value);
				} else {
					$columns[$key] = $index;
				}
			}
		}
		return $columns;
	}

	/**
	 * Finds a relationship
	 *
	 * @param string $alias
	 *
	 * @return Model\Relation|false
	 */
	public function getRelationship($alias) {
		if(array_key_exists($alias, $this->getCustomParentRelationships())) {
			return $this->getCustomParentRelationships()[$alias];
		} else {
			return $this->getModelsManager()->getRelationByAlias(get_class($this), $alias);
		}
	}

	/**
	 * Sets a related entity
	 *
	 * @param string $alias
	 * @param Model  $entity
	 *
	 * @return void
	 */
	public function setRelated($alias, Model $entity) {
		$foundRelation = $this->getRelationship($alias);
		if($foundRelation instanceof CustomRelationship) {
			call_user_func(array($this, 'set'.ucfirst($alias)), $entity);
		} else {
			$attribute = $foundRelation->getFields();
			$value = $foundRelation->getReferencedFields();
			if(is_array($attribute)) {
				$count = sizeOf($attribute);
				for($i = 0; $i < $count; $i++) {
					$this->writeAttribute($attribute[$i], $entity->readAttribute($value[$i]));
				}
			} else {
				$this->writeAttribute($attribute, $entity->readAttribute($value));
			}
		}
	}

	/**
	 * Gets related entities.
	 *
	 * @param String $alias
	 * @param null   $arguments
	 *
	 * @return ResultSet
	 */
	public function getRelated($alias, $arguments = null) {
		$relationship = $this->getRelationship($alias);
		if(($relationship === false || $relationship instanceof CustomRelationship) && method_exists($this, "get".ucfirst($alias))) {
			return call_user_func(array($this, "get".ucfirst($alias)));
		}
		return parent::getRelated($alias, $arguments);
	}

	/**
	 * Used to be able to make a call inside of a transaction
	 * @return \Phalcon\Db\AdapterInterface
	 */
	public function selectReadConnection() {
		if($this->getDi()->has(Transaction::DI_NAME)) {
			/** @var TransactionInterface $transaction */
			$transaction = $this->getDi()->get(Transaction::DI_NAME);
			return $transaction->getConnection();
		} else {
			return $this->getReadConnection();
		}
	}

	/**
	 * Converts an array into
	 *
	 * @param array $list              if this is an array of objects that have the getId method, it uses those ids instead
	 * @param array &$params
	 * @param int   $currentParamCount
	 *
	 * @return string sql
	 */
	public function listToIn(array $list, &$params, $currentParamCount) {
		$criteria = $this->query();
		$criteriaHelper = new CriteriaHelper($criteria);
		return $criteriaHelper->listToIn($list, $params, $currentParamCount);
	}

	/**
	 * Adds a new parameter
	 *
	 * @param string $value
	 * @param array  &$params
	 * @param int    $currentParamCount
	 *
	 * @return string
	 */
	public function addParam($value, &$params, $currentParamCount) {
		$criteria = $this->query();
		$criteriaHelper = new CriteriaHelper($criteria);
		return $criteriaHelper->addParam($value, $params, $currentParamCount);
	}

	/**
	 * Allows us to arbitrarily add read only properties to api entities.
	 * @return String[]
	 */
	public function getExtraProperties() {
		return array();
	}

	/**
	 * Returns all behaviors associated with the class
	 *
	 * @param string $className
	 *
	 * @return Model\BehaviorInterface[]
	 */
	public function getAllBehaviorsByClassName($className) {
		$modelsManager = $this->getModelsManager();

		$reflectionClass = new \ReflectionClass($modelsManager);
		$reflectionBehavior = $reflectionClass->getProperty('_behaviors');
		$reflectionBehavior->setAccessible(true);
		/** @var array $allBehaviors */
		$allBehaviors = $reflectionBehavior->getValue($modelsManager);
		/** @var Model\BehaviorInterface[] $behaviors */
		$behaviors = $allBehaviors[strtolower(get_class($this))];

		/** @var Model\BehaviorInterface[] $returnVar */
		$returnVar = [];
		foreach ($behaviors as $behavior) {
			if(get_class($behavior)==$className || is_subclass_of($behavior, $className)) {
				$returnVar[] = $behavior;
			}
		}

		return $returnVar;
	}
}
