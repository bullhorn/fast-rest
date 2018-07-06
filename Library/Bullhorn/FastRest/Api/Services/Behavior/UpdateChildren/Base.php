<?php
namespace Bullhorn\FastRest\Api\Services\Behavior\UpdateChildren;
use Bullhorn\FastRest\Api\Services\Behavior\DbEventEnum;
use Bullhorn\FastRest\DependencyInjection;
use Bullhorn\FastRest\DependencyInjectionHelper;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;
use Phalcon\Http\Request\Exception;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\ModelInterface;

abstract class Base extends Behavior implements InjectionAwareInterface, BehaviorInterface {
    private $savedData = [];

    /** @var  Model */
    private $entity;

    abstract public function getFieldName();

    abstract protected function process(array $data);

    public function getDi() {
        return DependencyInjectionHelper::getDi();
    }

    public function setDi(DiInterface $di) {
        DependencyInjectionHelper::setDi($di);
    }

    /**
     * getAvailableFields
     * @return string[] key is field name, value is if required
     */
    abstract protected function getAvailableFields(): array;

    public function setData(ModelInterface $model, array $data): void {
        $this->setEntity($model);
        if(is_null($this->getEntity()->getId())) {
            $this->savedData = [
                'entity' => $model,
                'data' => $data
            ];
        } else {
            $this->preProcess($data);
        }
    }

    /**
     * notify
     * @param string $type
     * @param ModelInterface|Model $model
     */
    final public function notify($type, ModelInterface $model) {
        $this->setEntity($model);
        switch($type) {
            case DbEventEnum::AFTER_CREATE:
                foreach($this->savedData as $savedData) {
                    if($savedData['entity'] === $model) {
                        $this->preProcess($savedData['data']);
                    }
                }
                break;
        }
    }

    private function preProcess(array $data) {
        foreach($data as $child) {
            $availableFields = $this->getAvailableFields();
            foreach ($child as $field => $value) {
                if (!array_key_exists($field, $availableFields)) {
                    throw new Exception('Invalid Field on (' . $this->getFieldName() . '): ' . $field, 400);
                }
                unset($availableFields[$field]);
            }
            foreach ($availableFields as $field => $required) {
                if ($required) {
                    throw new Exception('Missing Required Field on (' . $this->getFieldName() . '): ' . $field, 400);
                }
            }
        }
        $this->process($data);
    }

    /**
     * Entity
     * @return Model
     */
    protected function getEntity(): Model {
        return $this->entity;
    }

    /**
     * Entity
     * @param Model $entity
     */
    private function setEntity(Model $entity): void {
        $this->entity = $entity;
    }

}