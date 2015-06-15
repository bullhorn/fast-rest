<?php
namespace Phalcon\ApiGenerator\Api\Services\ControllerHelper;
use Phalcon\ApiGenerator\Api\Services\Database\Transaction;
use Phalcon\Http\Request;
use Phalcon\ApiGenerator\Api\Models\ApiInterface;
use Phalcon\Http\Request\Exception;
use Phalcon\Mvc\Model\Transaction\Failed as TransactionException;
use \Phalcon\Mvc\Model\TransactionInterface;
use Phalcon\ApiGenerator\Api\Services\Behavior\Upload\Base as UploadBase;

/**
 * Class Save
 */
class Save extends Base {
	/** @var  Request */
	private $request;
	/** @var  ApiInterface */
	private $entity;
	/** @var  bool */
	private $creating;
	const EVENT_DATA_PROPAGATION_CREATE = 'event_data_propagation_create';
	const EVENT_DATA_PROPAGATION_UPDATE = 'event_data_propagation_update';
	const EVENT_DATA_FINAL_CLEANUP = 'event_data_final_cleanup';

	/**
	 * Constructor
	 * @param Request      $request
	 * @param ApiInterface $entity
	 * @param bool         $isCreating
	 */
	public function __construct(Request $request, ApiInterface $entity, $isCreating) {
		$this->setRequest($request);
		$this->setEntity($entity);
		$this->setCreating($isCreating);
	}

	/**
	 * Getter
	 * @return boolean
	 */
	private function isCreating() {
		return $this->creating;
	}

	/**
	 * Setter
	 * @param boolean $creating
	 */
	private function setCreating($creating) {
		$this->creating = $creating;
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

	/**
	 * Saves all possible post variables
	 * @param Params $params
	 * @return bool if anything was changed
	 * @throws Exception
	 * @throws \Exception
	 */
	public function process(Params $params) {
		$transactionManager = new Transaction();
		$transactionManager->begin();
		$transaction = $transactionManager->getTransaction();
		$isChanged = false;
		try {
			$isChanged = $this->saveFieldsRecursive($params->getParams(), $this->getEntity(), $this->isCreating(), $transaction);
			$transactionManager->commit();
		} catch (TransactionException $e) {
			$transactionManager->rollback();
		}
		return $isChanged;
	}

	/**
	 * Looks up which fields are automatically updated
	 *
	 * @param ApiInterface $entity
	 * @param bool         $isCreating
	 *
	 * @return array
	 */
	private function lookUpAutomaticFields(ApiInterface $entity, $isCreating) {
		if($isCreating) {
			$automaticAttributes = $entity->getModelsMetaData()->getAutomaticCreateAttributes($entity);
		} else {
			$automaticAttributes = $entity->getModelsMetaData()->getAutomaticUpdateAttributes($entity);
		}
		$columnMap = $entity->getModelsMetaData()->getColumnMap($entity);
		$automaticFields = array_fill_keys($entity->getAutomaticallyUpdatedFields(), null);
		foreach($automaticAttributes as $fullName=>$null) {
			$automaticFields[$columnMap[$fullName]] = null;
		}
		return $automaticFields;
	}

	/**
	 * Filters the params down to just the instance fields
	 *
	 * @param \stdClass    $params
	 * @param ApiInterface $entity
	 * @param bool         $isCreating
	 *
	 * @return string[]
	 * @throws Exception
	 */
	private function filterFields(\stdClass $params, ApiInterface $entity, $isCreating) {
		$automaticFields = $this->lookUpAutomaticFields($entity, $isCreating);
		$customParentRelationshipFields = [];
		foreach($entity->getCustomParentRelationships() as $customParentRelationshipField) {
			$customParentRelationshipFields[] = $customParentRelationshipField->getFields();
		}
		$fields = array();
		foreach($params as $key=>$value) {
			if(!(is_object($value) && get_class($value)=='stdClass')) {
				if(in_array($key, $entity->getModelsMetaData()->getColumnMap($entity)) || in_array($key, $customParentRelationshipFields)) {
					if(array_key_exists($key, $automaticFields) && $entity->readAttribute($key)!=$value) {
						throw new Exception('The field of: '.$key.' cannot be manually updated', 409);
					}
					$fields[$key] = $value;
				} else {
					throw new Exception('Could not find the field: '.$key, 400);
				}
			}
		}
		return $fields;
	}

	/**
	 * Filters the params down to just the instance parents
	 *
	 * @param \stdClass    $params
	 * @param ApiInterface $entity
	 *
	 * @return \stdClass[]
	 * @throws Exception
	 */
	private function filterParents(\stdClass $params, ApiInterface $entity) {
		$parents = array();
		foreach($params as $key=>$value) {
			if(is_object($value) && get_class($value)=='stdClass') {
				$alias = ucfirst($key);
				if(in_array($alias, $entity->getParentRelationships())) {
					$parents[$alias] = $value;
				} else {
					throw new Exception('Could not find the parent field: '.$key, 400);
				}
			}
		}
		return $parents;
	}

	/**
	 * Writes the actual fields
	 *
	 * @param string[]     $fields
	 * @param ApiInterface $entity
	 *
	 * @return void
	 */
	private function writeFields($fields, ApiInterface $entity) {
		foreach($fields as $name=>$value) {
			if($this->getAcl()->canWriteField($entity, $name)) {
				$entity->writeAttribute($name, $value);
			}
		}
	}

	/**
	 * Writes the custom parent fields
	 *
	 * @param string[]     $fields
	 * @param ApiInterface $entity
	 *
	 * @return void
	 */
	private function writeCustomParents($fields, ApiInterface $entity) {
		foreach($entity->getCustomParentRelationships() as $customRelationship) {
			$name = $customRelationship->getReferencedFields();
			if(array_key_exists($name, $fields)) {
				$entity->writeAttribute($name, $fields[$name]);
			}
		}
	}

	/**
	 * Saves all fields
	 *
	 * @param \stdClass            $params
	 * @param ApiInterface         $entity
	 * @param bool                 $isCreating
	 * @param TransactionInterface $transaction
	 *
	 * @return bool if anything was changed
	 * @throws Exception
	 */
	private function saveFieldsRecursive(\stdClass $params, ApiInterface $entity, $isCreating, TransactionInterface $transaction) {
		if(!$isCreating) {
			$this->getAcl()->canWrite($entity);
		}

		$isChanged = false;
		$entity->setTransaction($transaction);
		$parents = $this->filterParents($params, $entity);
		$fields = $this->filterFields($params, $entity, $isCreating);


		$this->writeFields($fields, $entity);
		$this->writeCustomParents($fields, $entity);
		//Then Update the parents
		/** @var ApiInterface[] $parentEntities */
		$parentEntities = array();
		foreach($parents as $relationship=>$subParams) {
			/** @var ApiInterface $parentEntity */
			$parentEntity = $entity->getRelated($relationship);
			if($parentEntity===false) {
				$relation = $entity->getRelationship($relationship);
				$referencedModel = $relation->getReferencedModel();
				$parentEntity = new $referencedModel();
				$subIsCreating = true;
			} else {
				$subIsCreating = false;
			}
			$parentEntities[$relationship] =  $parentEntity;
			$parentIsChanged = $this->saveFieldsRecursive($subParams, $parentEntity, $subIsCreating, $transaction);
			if($parentIsChanged) {
				$isChanged = true;
			}
		}
		//Then Check if we want to load from defaults (if creating the specific object)
		if($isCreating) {
			$requiredRelationships = $entity->getDefaultRelationships();
			foreach($requiredRelationships as $alias) {
				//If the entity isn't currently set, set it from the parents
				if($entity->getRelated($alias)===false) {
					if(!array_key_exists($alias, $parentEntities)) {
						$message = 'The Required Parent of \''.$alias.'\' was not found.';
						$relationship = $entity->getRelationship($alias);
						$message .= ' Please either create a new \''.$alias.'\' or set the field \''.$relationship->getFields().'\'';
						throw new Exception($message, 400);
					}
					$entity->setRelated($alias, $parentEntities[$alias]);
				}
			}
			$entity->loadDefaults();
		}
		//Then, if loaded from defaults update the fields and other related parents again, to make sure the defaults didn't overwrite them
		foreach($parentEntities as $relationship=>$parent) {
			$entity->setRelated($relationship, $parent);
		}
		$this->writeFields($fields, $entity);

		if($isCreating) {
			$entity->fireEventCancel(UploadBase::EVENT_UPLOAD_FILE_CREATE);
			$entity->fireEvent(self::EVENT_DATA_PROPAGATION_CREATE);
		} else {
			$entity->fireEventCancel(UploadBase::EVENT_UPLOAD_FILE_UPDATE);
			$entity->fireEvent(self::EVENT_DATA_PROPAGATION_UPDATE);
		}
		if($isCreating || sizeOf($entity->getChangedFields())>0) {
			$isChanged = true;
			$entity->save();
		}
		if($isCreating) {
			//Add the acl in after the save so that the validation can be performed, in case you are looking at a parent, and the parent value is set incorrectly.  This will rollback the transaction anyways
			$this->getAcl()->canWrite($entity);
		}
		$entity->fireEvent(self::EVENT_DATA_FINAL_CLEANUP);
		return $isChanged;
	}
}