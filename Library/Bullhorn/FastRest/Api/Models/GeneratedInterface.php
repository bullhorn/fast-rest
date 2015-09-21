<?php
namespace Bullhorn\FastRest\Api\Models;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Mvc\ModelInterface;
interface GeneratedInterface extends InjectionAwareInterface, ModelInterface {

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


}