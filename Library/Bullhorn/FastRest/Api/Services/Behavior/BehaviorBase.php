<?php
namespace Bullhorn\FastRest\Api\Services\Behavior;
use Bullhorn\FastRest\Api\Models\GeneratedInterface;
use Bullhorn\FastRest\Api\Services\Acl\Events as AclEvents;
use Bullhorn\FastRest\Api\Services\ControllerHelper\Delete as DeleteService;
use Bullhorn\FastRest\Api\Services\ControllerHelper\Save as SaveService;
use Bullhorn\FastRest\DependencyInjection;
use Phalcon\DI\InjectionAwareInterface;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface as MvcInterface;
use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\Model\Message;
use Bullhorn\FastRest\Api\Services\Model\Manager as ModelsManager;
use Phalcon\Mvc\Model\ValidatorInterface;

abstract class BehaviorBase extends Behavior implements BehaviorInterface, InjectionAwareInterface {
    use DependencyInjection;

    /** @var  Model */
    private $entity;

    /** @var bool  */
    private $unitTestingChildren = false;
    /** @var bool  */
    private $unitTestParentCalled = false;
    /** @var  Snapshot */
    private $snapshot;

    /**
     * isUnitTestingChildren
     * @return bool
     */
    protected function isUnitTestingChildren() {
        return $this->unitTestingChildren;
    }

    /**
     * setUnitTestingChildren
     * @param bool $unitTestingChildren
     * @return BehaviorBase
     */
    public function setUnitTestingChildren($unitTestingChildren) {
        $this->unitTestingChildren = $unitTestingChildren;
        $this->setUnitTestParentCalled(false);
        return $this;
    }

    /**
     * isUnitTestParentCalled
     * @return bool
     */
    public function isUnitTestParentCalled() {
        return $this->unitTestParentCalled;
    }

    /**
     * setUnitTestParentCalled
     * @param bool $unitTestParentCalled
     * @return BehaviorBase
     */
    protected function setUnitTestParentCalled($unitTestParentCalled) {
        $this->unitTestParentCalled = $unitTestParentCalled;
        return $this;
    }

    /**
     * Provides a way of doing additional manipulation after creating.
     * @return void
     */
    protected function afterCreate() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * Provides a way of doing additional manipulation after deletion.
     * @return void
     */
    protected function afterDelete() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * Provides a way of doing additional manipulation after creating/updating.
     * @return void
     */
    protected function afterSave() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * Provides a way of doing additional manipulation after updating.
     * @return void
     */
    protected function afterUpdate() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * Validates if the entity can be deleted
     * @return void
     */
    protected function beforeDelete() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * Ran before validation
     * @return void
     */
    protected function beforeValidation() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * Ran before validation on creation
     * @return void
     */
    protected function beforeValidationOnCreate() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * Ran before validation on update
     * @return void
     */
    protected function beforeValidationOnUpdate() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * Validates if the entity can be read
     * @return bool
     */
    protected function canRead() {
        $this->setUnitTestParentCalled(true);
        return true;
    }

    /**
     * Validates if the entity can be written to
     * @return bool
     */
    protected function canWrite() {
        $this->setUnitTestParentCalled(true);
        return true;
    }

    /**
     * Does any data manipulation that is needed before saving but after validation
     * @return void
     */
    protected function dataPropagationCreate() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * Does any data manipulation that is needed before deleting the row from the database but after validation
     * @param
     * @return void
     */
    protected function dataPropagationDelete() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * Does any data manipulation that is needed before updating the database row but after validation
     * @param
     * @return void
     */
    protected function dataPropagationUpdate() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * One time cleanup after save/update/delete are done. Helps avoid some recursion nightmares.
     * @param
     * @return void
     */
    protected function finalCleanup() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * Validates if the entity can be updated or inserted
     * @return void
     */
    protected function validation() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * This is used to handle custom events
     * @param string $eventType
     * @return void
     */
    protected function notifyOther($eventType) {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * setEntity
     * @param MvcInterface $entity
     * @return void
     */
    public function setEntity(MvcInterface $entity) {
        $this->entity = $entity;
    }

    /**
     * getEntity
     * @return Model
     */
    public function getEntity() {
        return $this->entity;
    }

    /**
     * validate
     * @param ValidatorInterface $validator
     * @return void
     */
    public function validate(ValidatorInterface $validator) {
        $validator->validate($this->getEntity());
        $messages = $validator->getMessages();
        if(is_array($messages)) {
            foreach($messages as $message) {
                $this->getEntity()->appendMessage($message);
            }
        }
    }

    /**
     * clearReusableObjects
     * @return void
     */
    private function clearReusableObjects() {
        /** @var ModelsManager $modelsManager */
        $modelsManager = $this->getEntity()->getModelsManager();
        $modelsManager->clearReusableForModel($this->getEntity());
    }

    /**
     * getUpdatedFields
     * @return array
     */
    public function getChangedFields() {
        return $this->getSnapshot()->getChangedFields();
    }

    /**
     * getOldSnapshotData
     * @return array
     */
    public function getSnapshotData() {
        return $this->getSnapshot()->getSnapshotData();
    }

    /**
     * beforeSave
     * @return void
     */
    protected function beforeSave() {
        $this->setUnitTestParentCalled(true);
        return;
    }

    /**
     * Receives notifications from the Models Manager
     * @param string                          $eventType
     * @param MvcInterface|GeneratedInterface $entity
     * @return void
     * @throws ValidationException
     */
    final public function notify($eventType, MvcInterface $entity) {
        $instance = new static();
        $instance->setEntity($entity);
        $instance->setSnapshot(new Snapshot($instance->getEntity()));
        switch($eventType) {
            case 'beforeSave':
                $instance->getSnapshot()->setIsAfterSave(false);
                $instance->beforeSave();
                break;
            case 'beforeDelete':
                $instance->getSnapshot()->setIsAfterSave(false);
                $instance->beforeDelete();
                break;
            case 'afterSave':
                $instance->getSnapshot()->setIsAfterSave(true);
                $instance->clearReusableObjects();
                $instance->afterSave();
                break;
            case 'afterUpdate':
                $instance->getSnapshot()->setIsAfterSave(true);
                $instance->afterUpdate();
                break;
            case 'afterCreate':
                $instance->getSnapshot()->setIsAfterSave(true);
                $instance->afterCreate();
                break;
            case 'afterDelete':
                $instance->getSnapshot()->setIsAfterSave(true);
                $instance->clearReusableObjects();
                $instance->afterDelete();
                break;
            case 'validation':
                $instance->getSnapshot()->setIsAfterSave(false);
                $instance->validation();
                break;
            case 'beforeValidationOnCreate':
                $instance->getSnapshot()->setIsAfterSave(false);
                $instance->beforeValidation();
                $instance->beforeValidationOnCreate();
                break;
            case 'beforeValidationOnUpdate':
                $instance->getSnapshot()->setIsAfterSave(false);
                $instance->beforeValidation();
                $instance->beforeValidationOnUpdate();
                break;
            case AclEvents::EVENT_READ:
                $instance->getSnapshot()->setIsAfterSave(false);
                $canRead = $instance->canRead();
                if($canRead===false) {
                    if(empty($entity->getMessages())) {
                        $entity->appendMessage(new Message("You do not have read permission to: ".$entity->getSource()));
                    }
                }else if($canRead!==true) {
                    throw new \InvalidArgumentException("Can read must return a boolean.");
                }
                break;
            case AclEvents::EVENT_WRITE:
                $instance->getSnapshot()->setIsAfterSave(false);
                $canWrite = $instance->canWrite();
                if($canWrite===false) {
                    if(empty($entity->getMessages())) {
                        $entity->appendMessage(new Message("You do not have write permission to: ".$entity->getSource()));
                    }
                }else if($canWrite!==true) {
                    throw new \InvalidArgumentException("Can write must return a boolean.");
                }
                break;
            case SaveService::EVENT_DATA_FINAL_CLEANUP:
                $instance->getSnapshot()->setIsAfterSave(true);
                $instance->finalCleanup();
                break;
            case SaveService::EVENT_DATA_PROPAGATION_CREATE:
                $instance->getSnapshot()->setIsAfterSave(false);
                $instance->dataPropagationCreate();
                break;
            case SaveService::EVENT_DATA_PROPAGATION_UPDATE:
                $instance->getSnapshot()->setIsAfterSave(false);
                $instance->dataPropagationUpdate();
                break;
            case DeleteService::EVENT_DATA_PROPAGATION_DELETE:
                $instance->getSnapshot()->setIsAfterSave(false);
                $instance->dataPropagationDelete();
                break;
        }
        $instance->getSnapshot()->setIsAfterSave(true);
        $instance->notifyOther($eventType);
        if($entity->validationHasFailed()==true) {
            $exception = new ValidationException();
            $exception->setEntity($entity);
            throw $exception;
        } else {
            return;
        }
    }

    /**
     * getSnapshot
     * @return Snapshot
     */
    private function getSnapshot() {
        return $this->snapshot;
    }

    /**
     * setSnapshot
     * @param Snapshot $snapshot
     * @return BehaviorBase
     */
    private function setSnapshot(Snapshot $snapshot) {
        $this->snapshot = $snapshot;
        return $this;
    }

}