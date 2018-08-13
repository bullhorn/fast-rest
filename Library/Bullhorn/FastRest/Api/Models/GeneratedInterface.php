<?php
namespace Bullhorn\FastRest\Api\Models;

use Bullhorn\FastRest\Api\Services\Database\CriteriaHelper;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Mvc\ModelInterface;

interface GeneratedInterface extends InjectionAwareInterface, ModelInterface {
    /**
     * Gets the unreadable fields
     * @return \string[]
     */
    public function getUnReadableFields();

    /**
     * Gets a list of all fields that are automatically updated, and cannot be updated through the api
     * @return string[]
     */
    public function getAutomaticallyUpdatedFields();

    /**
     * Gets a list of all the relationships (by alias), that are required before loading the defaults
     * @return \string[]
     */
    public function getDefaultRelationships();

    /**
     * Returns the models manager related to the entity instance
     *
     * @return \Phalcon\Mvc\Model\Manager
     */
    public function getModelsManager();

    /**
     * Returns the models meta-data service related to the entity instance
     *
     * @return \Phalcon\Mvc\Model\MetaDataInterface
     */
    public function getModelsMetaData();


    /**
     * This adds a join based off of the aliases to an existing criteria, you can do nested joins, using a ., such as User.BranchSharing
     * @param CriteriaHelper $criteriaHelper The criteria we are adding the join on to
     * @param string $alias The alias of the relationship
     * @param string $currentModelAlias The current model's alias
     * @return string - The name of the model we just joined on
     */
    public function addJoin(CriteriaHelper $criteriaHelper, $alias, $currentModelAlias = null);

    /**
     * This calls the findFirst method and is used for unit testing so that it is not a static method
     * @param array|int $parameters Array of conditions or primary key.
     * @return GeneratedInterface|false
     */
    public function findFirstInstance($parameters=null);
}