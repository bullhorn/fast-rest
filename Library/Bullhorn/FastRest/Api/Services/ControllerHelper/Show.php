<?php
namespace Bullhorn\FastRest\Api\Services\ControllerHelper;

use Bullhorn\FastRest\Api\Models\ApiInterface;
use Phalcon\Http\Request\Exception;

class Show extends Base {
    /** @var  ApiInterface */
    private $entity;

    /**
     * Constructor
     * @param ApiInterface $entity
     */
    public function __construct(ApiInterface $entity) {
        $this->setEntity($entity);
    }

    /**
     * Generates the std object to show
     * @param Field $field
     * @return \stdClass
     */
    public function generate(Field $field) {
        $output = new \stdClass();
        $this->showRecursive($output, $field, $this->getEntity());
        return $output;
    }

    /**
     * showParentsAndChildren
     *
     * @param \stdClass $output
     * @param ApiInterface $entity
     * @param Field $field
     *
     * @return void
     * @throws Exception
     */
    private function showParentsAndChildren(\stdClass $output, ApiInterface $entity, Field $field) {
        if(in_array($field->getAlias(), $entity->getParentRelationships())) {

            /** @var ApiInterface $parentEntity */
            $parentEntity = $entity->getRelated($field->getAlias());
            if($parentEntity === false) {//AKA, a nullable vendor
                $output->{$field->getAlias()} = null;
            } else {
                $parentEntities = $entity->getApiParentEntities() ?? [];
                $parentEntities[$entity->getSource()] = $entity;
                $parentEntity->setApiParentEntities($parentEntities);
                $output->{$field->getAlias()} = new \stdClass();
                $this->showRecursive($output->{$field->getAlias()}, $field, $parentEntity);
            }
        } elseif(in_array($field->getAlias(), $entity->getChildrenRelationships())) {
            $output->{$field->getAlias()} = array();
            $resultSet = $entity->getRelated($field->getAlias());
            foreach($resultSet as $key => $currentEntity) {
                $parentEntities = $entity->getApiParentEntities() ?? [];
                $parentEntities[$entity->getSource()] = $entity;
                $currentEntity->setApiParentEntities($parentEntities);

                $subOutput = new \stdClass();
                $output->{$field->getAlias()}[] = $subOutput;
                $this->showRecursive($subOutput, $field, $currentEntity);
            }
        } else {
            throw new Exception('Invalid Field, not on the current object, no child or parent found: ' . $entity->getEntityName() . ' with ' . $field->getAlias(), 400);
        }
    }

    /**
     * Adds to the output object the specified field
     *
     * @param \stdClass $output
     * @param Field $field
     * @param ApiInterface $entity
     *
     * @return void
     * @throws Exception
     */
    private function showRecursive(\stdClass $output, Field $field, ApiInterface $entity) {
        $this->getAcl()->canRead($entity);
        $columns = $entity->getModelsMetaData()->getColumnMap($entity);
        $columns = array_merge($columns, $entity->getExtraProperties());
        foreach($field->getChildren() as $child) {
            $this->showParentsAndChildren(
                $output,
                $entity,
                $child
            );
        }
        foreach($field->getFields() as $subField) {
            if($subField == '*') { //All parts
                $lazyProperties = $entity->getLazyProperties();
                foreach(array_diff($columns, $lazyProperties) as $column) {
                    $this->showField($output, $entity, $column);
                }
            } elseif(in_array($subField, $columns)) {
                $this->showField($output, $entity, $subField);
            } elseif(in_array($subField, $entity->getChildrenRelationships()) || in_array($subField, $entity->getParentRelationships())) {
                throw new Exception('Invalid Field, child or parent: ' . $entity->getEntityName() . ' with "' . $subField . '". Please add .* to the end to get all related fields', 400);
            } else {
                throw new Exception('Invalid Field, not on the current object: ' . $entity->getEntityName() . ' with ' . $subField, 400);
            }
        }
    }

    /**
     * Shows an individual field
     *
     * @param \stdClass $output
     * @param ApiInterface $entity
     * @param string $column
     *
     * @return void
     */
    private function showField(\stdClass $output, ApiInterface $entity, $column) {
        if($this->getAcl()->canReadField($entity, $column)) {
            if(in_array($column, $entity->getUnReadableFields())) {
                $value = null;
            } else {
                $value = $entity->readAttribute($column);
                //Fix for Date and DateTime
                if(is_object($value) && method_exists($value, '__toString')) {
                    $value = $value->__toString();
                }
            }
            $output->{$column} = $value;
        }
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

}