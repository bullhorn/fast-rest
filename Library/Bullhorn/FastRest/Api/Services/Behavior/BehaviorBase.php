<?php
namespace Bullhorn\FastRest\Api\Services\Behavior;
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

    /**
     * Provides a way of doing additional manipulation after creating.
     * @return void
     */
    protected function afterCreate() {
        return;
    }

    /**
     * Provides a way of doing additional manipulation after deletion.
     * @return void
     */
    protected function afterDelete() {
        return;
    }

    /**
     * Provides a way of doing additional manipulation after creating/updating.
     * @return void
     */
    protected function afterSave() {
        return;
    }

    /**
     * Provides a way of doing additional manipulation after updating.
     * @return void
     */
    protected function afterUpdate() {
        return;
    }

    /**
     * Validates if the entity can be deleted
     * @return void
     */
    protected function beforeDelete() {
        return;
    }

    /**
     * Ran before validation
     * @return void
     */
    protected function beforeValidation() {
        return;
    }

    /**
     * Ran before validation on creation
     * @return void
     */
    protected function beforeValidationOnCreate() {
        return;
    }

    /**
     * Ran before validation on update
     * @return void
     */
    protected function beforeValidationOnUpdate() {
        return;
    }

    /**
     * Validates if the entity can be read
     * @return bool
     */
    protected function canRead() {
        return true;
    }

    /**
     * Validates if the entity can be written to
     * @return bool
     */
    protected function canWrite() {
        return true;
    }

    /**
     * Does any data manipulation that is needed before saving but after validation
     * @return void
     */
    protected function dataPropagationCreate() {
        return;
    }

    /**
     * Does any data manipulation that is needed before deleting the row from the database but after validation
     * @param
     * @return void
     */
    protected function dataPropagationDelete() {
        return;
    }

    /**
     * Does any data manipulation that is needed before updating the database row but after validation
     * @param
     * @return void
     */
    protected function dataPropagationUpdate() {
        return;
    }

    /**
     * One time cleanup after save/update/delete are done. Helps avoid some recursion nightmares.
     * @param
     * @return void
     */
    protected function finalCleanup() {
        return;
    }

    /**
     * Validates if the entity can be updated or inserted
     * @return void
     */
    protected function validation() {
        return;
    }

    /**
     * This is used to handle custom events
     * @param string $eventType
     * @return void
     */
    protected function notifyOther($eventType) {
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
     * Receives notifications from the Models Manager
     * @param string             $eventType
     * @param MvcInterface $entity
     * @return bool
     * @throws ValidationException
     */
    final public function notify($eventType, MvcInterface $entity) {
        $instance = new static();
        $instance->setEntity($entity);
        switch($eventType) {
            case 'beforeDelete':
                $instance->beforeDelete();
                break;
            case 'afterSave':
                $instance->clearReusableObjects();
                $instance->afterSave();
                break;
            case 'afterUpdate':
                $instance->afterUpdate();
                break;
            case 'afterCreate':
                $instance->afterCreate();
                break;
            case 'afterDelete':
                $instance->clearReusableObjects();
                $instance->afterDelete();
                break;
            case 'validation':
                $instance->validation();
                break;
            case 'beforeValidationOnCreate':
                $instance->beforeValidation();
                $instance->beforeValidationOnCreate();
                break;
            case 'beforeValidationOnUpdate':
                $instance->beforeValidation();
                $instance->beforeValidationOnUpdate();
                break;
            case AclEvents::EVENT_READ:
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
                $instance->finalCleanup();
                break;
            case SaveService::EVENT_DATA_PROPAGATION_CREATE:
                $instance->dataPropagationCreate();
                break;
            case SaveService::EVENT_DATA_PROPAGATION_UPDATE:
                $instance->dataPropagationUpdate();
                break;
            case DeleteService::EVENT_DATA_PROPAGATION_DELETE:
                $instance->dataPropagationDelete();
                break;
            default:
                $instance->notifyOther($eventType);
                break;
        }
        if($entity->validationHasFailed()==true) {
            $exception = new ValidationException();
            $exception->setEntity($entity);
            throw $exception;
        } else {
            return;
        }
    }

}