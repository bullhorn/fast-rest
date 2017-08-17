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

class Snapshot implements InjectionAwareInterface {
    use DependencyInjection;

    /** @var  Model */
    private $entity;
    /** @var bool  */
    private $isAfterSave = false;

    /**
     * Snapshot constructor.
     * @param Model $entity
     */
    public function __construct(Model $entity) {
        $this->setEntity($entity);
    }

    /**
     * setEntity
     * @param MvcInterface $entity
     * @return void
     */
    private function setEntity(MvcInterface $entity) {
        $this->entity = $entity;
    }

    /**
     * getEntity
     * @return Model
     */
    private function getEntity() {
        return $this->entity;
    }

    /**
     * getUpdatedFields
     * @return array
     */
    public function getChangedFields() {
        if($this->isAfterSave() && method_exists($this->getEntity(), 'getUpdatedFields')) {
            return $this->getEntity()->getUpdatedFields();
        } else { //Legacy
            return $this->getEntity()->getChangedFields();
        }
    }

    /**
     * getOldSnapshotData
     * @return array
     */
    public function getSnapshotData() {
        if($this->isAfterSave() && method_exists($this->getEntity(), 'getOldSnapshotData')) {
            return $this->getEntity()->getOldSnapshotData();
        } else { //Legacy
            return $this->getEntity()->getSnapshotData();
        }
    }

    /**
     * isAfterSave
     * @return bool
     */
    private function isAfterSave() {
        return $this->isAfterSave;
    }

    /**
     * setIsAfterSave
     * @param bool $isAfterSave
     * @return BehaviorBase
     */
    public function setIsAfterSave($isAfterSave) {
        $this->isAfterSave = $isAfterSave;
        return $this;
    }

}