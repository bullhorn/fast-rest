<?php
namespace Bullhorn\FastRest\Api\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Mvc\Model\MetaDataInterface;
use Phalcon\Mvc\Model\Relation;
use Bullhorn\FastRest\Api\Services\Database\CriteriaHelper;
use Bullhorn\FastRest\Api\Services\Behavior\UpdateChildren\Base as ChildrenUpdater;

interface ApiInterface extends GeneratedInterface {
    const FIELD_TYPE_BOOL = 'bool';
    const FIELD_TYPE_DATE = 'Date';
    const FIELD_TYPE_DATE_TIME = 'DateTime';
    const FIELD_TYPE_DOUBLE = 'double';
    const FIELD_TYPE_INT = 'int';
    const FIELD_TYPE_STRING = 'string';

    /**
     * Gets a list of all the custom relationships, the key should be the alias
     * @return CustomRelationship[]
     */
    public function getCustomParentRelationships();

    /**
     * ChildrenUpdaters
     * @return ChildrenUpdater[]
     */
    public function getChildrenUpdaters(): array;

    /**
     * Returns the primary id.
     * @return int
     */
    public function getId();

    /**
     * Getter
     * @return string[]
     */
    public function getAutomaticallyUpdatedFields();

    /**
     * Gets the unreadable fields, these will always display as null
     * @return \string[]
     */
    public function getUnReadableFields();

    /**
     * This adds a join based off of the aliases to an existing criteria, you can do nested joins, using a ., such as User.BranchSharing
     * @param CriteriaHelper $criteriaHelper The criteria we are adding the join on to
     * @param string $alias The alias of the relationship
     * @param string $currentModelAlias The current model's alias
     * @return string - The name of the model we just joined on
     */
    public function addJoin(CriteriaHelper $criteriaHelper, $alias, $currentModelAlias = null);

    /**
     * Returns a list of all parent relationships, these will all return a Base Model instance
     * @return string[]
     */
    public function getParentRelationships();

    /**
     * Returns a list of all children relationships, these will all return a ResultSet
     * @return string[]
     */
    public function getChildrenRelationships();

    /**
     * Returns the models manager related to the entity instance
     *
     * @return ManagerInterface
     */
    public function getModelsManager();

    /**
     * Returns the models meta-data service related to the entity instance
     *
     * @return MetaDataInterface
     */
    public function getModelsMetaData();

    /**
     * Finds a relationship from a given alias
     *
     * @param string $alias
     *
     * @return Relation
     */
    public function getRelationship($alias);

    /**
     * Gets the Relationship Aliases required to load the defaults
     * @return string[]
     */
    public function getDefaultRelationships();

    /**
     * Sets a related entity
     *
     * @param string $alias
     * @param Model $entity
     *
     * @return void
     */
    public function setRelated($alias, Model $entity);

    /**
     * This should set any defaults to the current object
     * @return void
     */
    public function loadDefaults();

    /**
     * Returns a list of changed values
     *
     * @return array
     */
    public function getChangedFields();

    /**
     * Gets the entity name
     * @return string
     */
    public function getEntityName();

    /**
     * Allows us to arbitrarily add read only properties to api entities.
     * @return String[]
     */
    public function getExtraProperties();

    /**
     * Returns all behaviors associated with the class
     *
     * @param string $className
     *
     * @return Model\BehaviorInterface[]
     */
    public function getAllBehaviorsByClassName($className);

    /**
     * Stubbed method
     *
     * @param mixed $attribute
     * @param mixed $value
     *
     * @return void
     */
    public function writeAttribute($attribute, $value);

    /**
     * Stubbed method
     *
     * @param mixed $attribute
     *
     * @return void
     */
    public function readAttribute($attribute);

    /**
     * Gets an array of field types, self::FIELD_TYPE_
     *
     * @return string[]
     */
    public function getFieldTypes();
}