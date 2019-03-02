<?php
namespace Bullhorn\FastRest\Api\Models;

use Bullhorn\FastRest\Api\Services\Database\CriteriaHelper;
use Bullhorn\FastRest\Api\Services\Database\Transaction;
use Phalcon\Db\Column;
use Phalcon\Mvc\Model;
use Bullhorn\FastRest\Api\Services\Filter;
use Phalcon\Mvc\Model\TransactionInterface;
use Phalcon\Mvc\Model\Resultset\Simple as ResultSet;
use Bullhorn\FastRest\Api\Services\Behavior\UpdateChildren\Base as ChildrenUpdater;

/**
 * Interface EntityInterface
 */
abstract class Base extends Model {
    /** @var Filter */
    private $filter;
    /** @var  string[] */
    private $defaultRelationships = [];
    /** @var string[] */
    private $unReadableFields = [];
    /** @var string[] */
    private $automaticallyUpdatedFields = [];
    /** @var CustomRelationship[] */
    private $customParentRelationships = [];
    /** @var ChildrenUpdater[] */
    private static $childrenUpdaters = [];
    /** @var Base[]|ApiInterface[] */
    private $apiParentEntities = [];

    /**
     * Constructor
     * @return void
     */
    public function onConstruct() {
        $this->generateFilter();
    }

    /**
     * resetField
     * @param string $name
     * @return void
     */
    public function resetField($name) {
        $this->{$name} = null;
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

    public function addChildrenUpdater(ChildrenUpdater $childrenUpdater): void {
        $this->addBehavior($childrenUpdater);
        $childrenUpdaters = $this->getChildrenUpdaters();
        $childrenUpdaters[$childrenUpdater->getFieldName()] = $childrenUpdater;
        $this->setChildrenUpdaters($childrenUpdaters);
    }

    /**
     * ChildrenUpdaters
     * @return ChildrenUpdater[]
     */
    public function getChildrenUpdaters(): array {
        $key = get_class($this);
        if(!array_key_exists($key, self::$childrenUpdaters)) {
            return [];
        }
        return self::$childrenUpdaters[$key];
    }

    /**
     * ChildrenUpdaters
     * @param ChildrenUpdater[] $childrenUpdaters
     * @return Base
     */
    private function setChildrenUpdaters(array $childrenUpdaters): Base {
        self::$childrenUpdaters[get_class($this)] = $childrenUpdaters;
        return $this;
    }


    /**
     * getChangedFields
     * @return string[]
     * @throws Model\Exception
     * @throws \Exception
     */
    public function getChangedFields() {
        $snapshotData = $this->getSnapshotData();
        if(!is_array($snapshotData)) {
            $fields = array_values($this->getModelsMetaData()->getColumnMap($this));
        } else {
            $snapshotData = $this->getSnapshotData();
            $databaseTypes = $this->getDatabaseTypes();
            foreach($snapshotData as $key=>$value) {
                if(array_key_exists($key, $databaseTypes)) {
                    $databaseType = $databaseTypes[$key];
                    switch($databaseType) {
                        case Column::TYPE_BOOLEAN:
                            $value = (bool)$value;
                            break;
                        case Column::TYPE_INTEGER:
                            $value = (int)$value;
                            break;
                        case Column::TYPE_FLOAT:
                        case Column::TYPE_DOUBLE:
                            $value = (float)$value;
                            break;
                    }
                    $snapshotData[$key] = $value;
                }
            }
            $fields = [];
            foreach($this->getModelsMetaData()->getColumnMap($this) as $shortName) {
                if(!array_key_exists($shortName, $snapshotData)) {
                    $fields[] = $shortName;
                    continue;
                }
                $snapData = $snapshotData[$shortName];
                $fieldValue = $this->readAttribute($shortName);
                if($snapData !== $fieldValue) {
                    $fields[] = $shortName;
                    continue;
                }
            }
        }
        return array_diff($fields, ['updatedAt', 'createdAt']);
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
                'notNullValidations' => false //Allow empty strings instead of the wtf new \Phalcon\Db\RawValue('""');
            )
        );
        //Sets to only update the fields that are changed
        $this->useDynamicUpdate(true);
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
     * getDatabaseTypes
     * @return array
     */
    abstract public function getDatabaseTypes();

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
        $method = 'get' . ucfirst($name);
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
        $method = 'set' . ucfirst($name);
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
        foreach($columns as $key => $value) {
            if(!array_key_exists($value, $columnMaps)) {
                //Look up in the changed values
                $index = array_search($value, $columnMaps);
                if($index === false) {
                    throw new \InvalidArgumentException('Invalid Column, could not find: ' . $value);
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
     * @param Model $entity
     *
     * @return void
     */
    public function setRelated($alias, Model $entity) {
        $foundRelation = $this->getRelationship($alias);
        if($foundRelation instanceof CustomRelationship) {
            call_user_func(array($this, 'set' . ucfirst($alias)), $entity);
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
     * @param null $arguments
     *
     * @return ResultSet
     */
    public function getRelated($alias, $arguments = null) {
        $relationship = $this->getRelationship($alias);
        if(($relationship === false || $relationship instanceof CustomRelationship) && method_exists($this, "get" . ucfirst($alias))) {
            return call_user_func(array($this, "get" . ucfirst($alias)));
        }
        return parent::getRelated($alias, $arguments);
    }

    /**
     * Used to be able to make a call inside of a transaction
     * @return \Phalcon\Db\AdapterInterface
     */
    public function selectReadConnection() {
        $transaction = new Transaction($this->getReadConnectionService());
        if($transaction->isInTransaction()) {
            return $transaction->getTransaction()->getConnection();
        } else {
            return $this->getReadConnection();
        }
    }

    /**
     * Converts an array into
     *
     * @param array $list if this is an array of objects that have the getId method, it uses those ids instead
     * @param array &$params
     * @param int $currentParamCount
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
     * @param array &$params
     * @param int $currentParamCount
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

    public function getLazyProperties(): array {
        return [];
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
        foreach($behaviors as $behavior) {
            if(get_class($behavior) == $className || is_subclass_of($behavior, $className)) {
                $returnVar[] = $behavior;
            }
        }

        return $returnVar;
    }

    private $columnMapFixed;

    /**
     * @param array $map
     * @return array
     */
    protected function columnMapMissingColumnsFix(array $map) {
        if(is_null($this->columnMapFixed)) {
            /** @var Column[] $columns */
            $columns = $this->getReadConnection()->describeColumns($this->getSource());
            foreach($columns as $dbColumn) {
                $name = $dbColumn->getName();
                if(!array_key_exists($name, $map)) {
                    $map[$name] = $name;
                }
            }
            $this->columnMapFixed = $map;
        }
        return $map;
    }

    /**
     * ApiParentEntities
     * @return Base[]|ApiInterface[]
     */
    public function getApiParentEntities(): array {
        return $this->apiParentEntities;
    }

    /**
     * ApiParentEntities
     * @param Base[]|ApiInterface[] $apiParentEntities
     * @return Base
     */
    public function setApiParentEntities(array $apiParentEntities) {
        $this->apiParentEntities = $apiParentEntities;
        return $this;
    }

}