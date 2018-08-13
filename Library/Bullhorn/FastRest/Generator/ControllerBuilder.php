<?php
namespace Bullhorn\FastRest\Generator;

use Bullhorn\FastRest\Api\Models\GeneratedInterface as Base;
use Phalcon\Mvc\Model\BehaviorInterface;
use Bullhorn\FastRest\Api\Services\Behavior\Upload\File as UploadableFile;
use Bullhorn\FastRest\Api\Services\Behavior\Upload\Base as UploadBehavior;

class ControllerBuilder {
    /** @var  Configuration */
    private $configuration;
    /** @var  Base */
    private $model;
    /** @var  Object\Index */
    private $object;
    /** @var  ModelBuilder */
    private $modelBuilder;
    /** @var  PluralHelper */
    private $pluralHelper;
    /** @var  Base[] */
    private $relatedModels;
    /** @var  ModelBuilder[] */
    private $relatedBuilders;

    /**
     * Constructor
     * @param Configuration $configuration
     * @param Base $model
     */
    public function __construct(Configuration $configuration, Base $model) {
        $this->setConfiguration($configuration);
        $this->setModel($model);
        $this->setPluralHelper(new PluralHelper());
        $this->setObject(new Object\Index($this->getConfiguration()));
        $this->setModelBuilder(new ModelBuilder($this->getConfiguration(), $model->getSource()));
        $this->buildRelatedModels();
        $this->build();
    }

    /**
     * Getter
     * @return Configuration
     */
    private function getConfiguration() {
        return $this->configuration;
    }

    /**
     * Setter
     * @param Configuration $configuration
     */
    private function setConfiguration(Configuration $configuration) {
        $this->configuration = $configuration;
    }

    /**
     * Getter
     * @return ModelBuilder[]
     */
    private function getRelatedBuilders() {
        return $this->relatedBuilders;
    }

    /**
     * getRelatedBuilder
     *
     * @param string $alias
     *
     * @return ModelBuilder|null
     */
    private function getRelatedBuilder($alias) {
        foreach($this->getRelatedBuilders() as $key => $builder) {
            if($alias == $key) {
                return $builder;
            }
        }
        return null;
    }

    /**
     * Setter
     * @param ModelBuilder[] $relatedBuilders
     */
    private function setRelatedBuilders(array $relatedBuilders) {
        $this->relatedBuilders = $relatedBuilders;
    }


    /**
     * Getter
     * @return Base[]
     */
    private function getRelatedModels() {
        return $this->relatedModels;
    }

    /**
     * Setter
     * @param Base[] $relatedModels
     */
    private function setRelatedModels(array $relatedModels) {
        $this->relatedModels = $relatedModels;
    }

    /**
     * Builds the related required models, such as user to employee
     * @return void
     */
    private function buildRelatedModels() {
        /** @var Base[] $relatedModels */
        $relatedModels = array();
        /** @var ModelBuilder[] $relatedBuilders */
        $relatedBuilders = array();
        $automaticallyUpdatedFields = $this->getModel()->getAutomaticallyUpdatedFields();
        foreach($this->getModel()->getDefaultRelationships() as $alias) {
            $relation = $this->getModel()->getModelsManager()->getRelationByAlias(get_class($this->getModel()), $alias);
            if($relation !== false) {
                if(in_array($relation->getFields(), $automaticallyUpdatedFields)) {
                    $referencedModel = $relation->getReferencedModel();
                    /** @var Base $relatedModel */
                    $relatedModel = new $referencedModel();
                    $relatedModels[$alias] = $relatedModel;
                    $relatedBuilders[$alias] = new ModelBuilder($this->getConfiguration(), $relatedModel->getSource());
                }
            }
        }
        $this->setRelatedModels($relatedModels);
        $this->setRelatedBuilders($relatedBuilders);
    }

    /**
     * Getter
     * @return PluralHelper
     */
    private function getPluralHelper() {
        return $this->pluralHelper;
    }

    /**
     * Setter
     * @param PluralHelper $pluralHelper
     */
    private function setPluralHelper(PluralHelper $pluralHelper) {
        $this->pluralHelper = $pluralHelper;
    }


    /**
     * Getter
     * @return ModelBuilder
     */
    private function getModelBuilder() {
        return $this->modelBuilder;
    }

    /**
     * Setter
     * @param ModelBuilder $builder
     */
    private function setModelBuilder(ModelBuilder $builder) {
        $this->modelBuilder = $builder;
    }


    /**
     * Getter
     * @return Object\Index
     */
    public function getObject() {
        return $this->object;
    }

    /**
     * Setter
     * @param Object\Index $object
     */
    private function setObject(Object\Index $object) {
        $this->object = $object;
    }


    /**
     * Getter
     * @return Base
     */
    private function getModel() {
        return $this->model;
    }

    /**
     * Setter
     * @param Base $model
     */
    private function setModel($model) {
        $this->model = $model;
    }

    /**
     * getClassName
     * @return string
     */
    private function getClassName() {
        return ucfirst($this->getPluralHelper()->pluralize($this->getModel()->getSource()));
    }

    /**
     * buildProperties
     *
     * @param Swagger\Model $model
     * @param string $alias
     *
     * @return void
     */
    private function buildProperties(Swagger\Model $model, $alias = null) {
        if(is_null($alias)) {
            $builder = $this->getModelBuilder();
        } else {
            $builder = $this->getRelatedBuilder($alias);
        }
        $requiredFields = array();
        foreach($builder->getIndexes() as $index) {
            if($index->isPrimary() || $index->isPrimary()) {
                foreach($index->getColumns() as $column) {
                    $requiredFields[$column] = true;
                }
            }
        }
        $unReadableField = $this->getModel()->getUnReadableFields();
        foreach($builder->getFields() as $field) {
            if(in_array($field->getShortName(), $unReadableField)) {
                continue;
            }
            $property = new Swagger\Property();
            $property->setName((!is_null($alias) ? $alias . '.' : '') . $field->getShortName());
            $property->setType($field->getSwaggerType());
            $property->setFormat($field->getSwaggerFormat());
            $property->setDescription($field->getComment());
            $property->setRequired(array_key_exists($field->getName(), $requiredFields));
            $model->addProperty($property);
        }
    }

    /**
     * Builds the class documentation
     * @return void
     */
    private function buildDocumentation() {
        $model = new Swagger\Model();
        $model->setId($this->getClassName());
        $this->buildProperties($model);
        foreach($this->getRelatedModels() as $alias => $relatedModel) {
            $this->buildProperties($model, $alias);
        }
        $resource = new Swagger\Resource();
        $resource->setApiVersion('1.0')->setBasePath('/v1.0')->setResourcePath($this->getClassName());
        $resource->setDescription('');
        $documentation = $model . "\n" . $resource;
        $this->getObject()->setDocumentation($documentation);
    }

    /**
     * Builds the factory
     * @return void
     */
    private function buildFactory() {
        $this->getObject()->addUse(get_class($this->getModel()).' as Model');
        $method = new Object\Method();
        $method->setDescription('When called, this should return a new entity');
        $method->setReturnType('Model');
        $method->setAccess('public');
        $method->setName('generateEntity');
        $method->setContent('return $this->getDI()->get(Model::class);');
        $this->getObject()->addMethod($method);
    }

    /**
     * Builds the index action
     * @return void
     */
    private function buildIndexAction() {
        $api = new Swagger\Api();
        $api->setPath('/' . $this->getClassName());
        $operation = new Swagger\Operation();
        $operation->setMethod('GET');
        $operation->setSummary('Lists all ' . $this->getClassName() . '.');
        $operation->setNotes(
            'Only returns the first 50 by default. Using the actual API you can search by parent fields and any field on the model as opposed to the brief list provided below.'
        );
        $responseMessage = new Swagger\ResponseMessage();
        $responseMessage->setCode(200);
        $responseMessage->setMessage('List of ' . $this->getClassName() . '.');
        $responseMessage->setResponseModel($this->getClassName());
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->invalidFieldSpecifiedError();
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->notAuthorizedResponse();
        $operation->addResponseMessage($responseMessage);

        $operation->addParameter($this->buildStartParameter());
        $operation->addParameter($this->buildCountParameter());
        $operation->addParameter($this->buildSortParameter());
        $operation->addParameter($this->buildFieldsParameter());
        $searchParameters = $this->buildSearchParameters();
        foreach($searchParameters as $searchParameter) {
            $operation->addParameter($searchParameter);
        }
        $api->addOperation($operation);

        $method = new Object\Method();
        $method->setDescription($api->__toString());
        $method->setReturnType('void');
        $method->setAccess('public');
        $method->setName('indexAction');
        $method->setContent('parent::indexAction();');
        $this->getObject()->addMethod($method);
    }

    /**
     * Builds the post action
     * @return void
     */
    private function buildCreateAction() {
        $api = new Swagger\Api();
        $api->setPath('/' . $this->getClassName());
        $operation = new Swagger\Operation();
        $operation->setMethod('POST');
        $operation->setSummary('Create ' . $this->singularModelA_Or_An() . '.');
        $operation->setNotes('Creates ' . $this->singularModelA_Or_An() . '.');

        $responseMessage = new Swagger\ResponseMessage();
        $responseMessage->setCode(201);
        $responseMessage->setMessage(ucfirst($this->getModel()->getSource()) . ' was created.');
        $responseMessage->setResponseModel($this->getClassName());
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->invalidFieldSpecifiedError();
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->notAuthorizedResponse();
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->invalidValuePassed();
        $operation->addResponseMessage($responseMessage);

        $operation->addParameter($this->buildFieldsParameter());

        $parameter = new Swagger\Parameter();
        $parameter->setName('body');
        $parameter->setDescription($this->getPutPostBodyDescription(true));
        $parameter->setParamType('body');
        $parameter->setType($this->getClassName());
        $operation->addParameter($parameter);
        $api->addOperation($operation);

        $method = new Object\Method();
        $method->setDescription($api->__toString());
        $method->setReturnType('void');
        $method->setAccess('public');
        $method->setName('createAction');
        $method->setContent('parent::createAction();');
        $this->getObject()->addMethod($method);
    }

    /**
     * getPutPostBodyDescriptionReadyOnlyFields
     *
     * @param bool $isCreating
     *
     * @return string
     */
    private function getPutPostBodyDescriptionReadyOnlyFields($isCreating) {
        $description = '';
        $models = [[null, $this->getModel()]];
        foreach($this->getRelatedModels() as $alias => $relatedModel) {
            $models[] = [$alias, $relatedModel];
        }
        $automaticFields = array();
        foreach($models as $modelData) {
            /** @var Base $model */
            list($alias, $model) = $modelData;
            if($isCreating) {
                $automaticAttributes = $model->getModelsMetaData()->getAutomaticCreateAttributes($model);
            } else {
                $automaticAttributes = $model->getModelsMetaData()->getAutomaticUpdateAttributes($model);
            }
            $columnMap = $model->getModelsMetaData()->getColumnMap($model);
            foreach($model->getAutomaticallyUpdatedFields() as $field) {
                $automaticFields[(!is_null($alias) ? $alias . '.' : '') . $field] = null;
            }
            foreach($automaticAttributes as $fullName => $null) {
                $automaticFields[(!is_null($alias) ? $alias . '.' : '') . $columnMap[$fullName]] = null;
            }
        }
        if(!empty($automaticFields)) {
            $description .= "\n" . 'The following fields are read only: ' . implode(', ', array_keys($automaticFields)) . '.';
        }
        return $description;
    }

    /**
     * getPutPostBodyDescriptionFiles
     *
     * @param bool $isCreating
     *
     * @return string
     */
    private function getPutPostBodyDescriptionFiles($isCreating) {
        $description = '';
        $reflectionClass = new \ReflectionClass($this->getModel()->getModelsManager());
        $reflectionBehavior = $reflectionClass->getProperty('_behaviors');
        $reflectionBehavior->setAccessible(true);
        /** @var array $allBehaviors */
        $allBehaviors = $reflectionBehavior->getValue($this->getModel()->getModelsManager());
        /** @var BehaviorInterface[] $behaviors */
        $behaviors = $allBehaviors[strtolower(get_class($this->getModel()))];
        /** @var UploadableFile[] $optionalFiles */
        $optionalFiles = [];
        /** @var UploadableFile[] $requiredFiles */
        $requiredFiles = [];
        foreach($behaviors as $behavior) {
            if(is_subclass_of($behavior, 'Bullhorn\FastRest\Api\Services\Behavior\Upload\Base')) {
                /** @var UploadBehavior $behavior */
                foreach($behavior->getFiles() as $file) {
                    if($isCreating ? $file->isAllowedOnCreate() : $file->isAllowedOnUpdate()) {
                        if($isCreating ? $file->isRequiredOnCreate() : $file->isRequiredOnUpdate()) {
                            $requiredFiles[] = $file;
                        } else {
                            $optionalFiles[] = $file;
                        }
                    }
                }
            }
        }
        if(!empty($requiredFiles)) {
            $description .= "\n" . 'The following files are required:';
            foreach($requiredFiles as $file) {
                $description .= "\n" . '"' . $file->getName() . '" with the extensions: ' . implode(', ', $file->getAllowedExtensions());
            }
            $description .= "\n";
        }
        if(!empty($optionalFiles)) {
            $description .= "\n" . 'The following files are optional:';
            foreach($optionalFiles as $file) {
                $description .= "\n" . '"' . $file->getName() . '" with the extensions: ' . implode(', ', $file->getAllowedExtensions());
            }
            $description .= "\n";
        }
        return $description;
    }

    /**
     * getPutPostBodyDescription
     *
     * @param bool $isCreating
     *
     * @return string
     */
    private function getPutPostBodyDescription($isCreating) {
        $description = ucfirst($this->getModel()->getSource()) . ' Data.';
        if($isCreating) {
            $description .= "\n" . 'Only pass in the fields you wish to update.';
        }
        $description .= $this->getPutPostBodyDescriptionReadyOnlyFields($isCreating);
        $description .= $this->getPutPostBodyDescriptionFiles($isCreating);
        return $description;
    }

    /**
     * Builds the update action
     * @return void
     */
    private function buildUpdateAction() {
        $api = new Swagger\Api();
        $api->setPath('/' . $this->getClassName() . '/{id}');
        $operation = new Swagger\Operation();
        $operation->setMethod('PUT');
        $operation->setSummary('Update ' . $this->singularModelA_Or_An() . '.');
        $operation->setNotes('Update ' . $this->singularModelA_Or_An() . '.');

        $operation->addParameter($this->buildIdParameter());
        $operation->addParameter($this->buildFieldsParameter());

        $parameter = new Swagger\Parameter();
        $parameter->setName('body');
        $parameter->setDescription($this->getPutPostBodyDescription(false));
        $parameter->setParamType('body');
        $parameter->setType($this->getClassName());
        $operation->addParameter($parameter);

        $responseMessage = new Swagger\ResponseMessage();
        $responseMessage->setCode(200);
        $responseMessage->setMessage(ucfirst($this->getModel()->getSource()) . ' was updated.');
        $responseMessage->setResponseModel($this->getClassName());
        $operation->addResponseMessage($responseMessage);

        $responseMessage = new Swagger\ResponseMessage();
        $responseMessage->setCode(304);
        $responseMessage->setMessage(ucfirst($this->getModel()->getSource()) . ' was not changed.');
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->invalidFieldSpecifiedError();
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->notAuthorizedResponse();
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->notFoundResponse();
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->invalidValuePassed();
        $operation->addResponseMessage($responseMessage);

        $api->addOperation($operation);
        $method = new Object\Method();
        $method->setDescription($api->__toString());
        $method->setReturnType('void');
        $method->setAccess('public');
        $method->setName('updateAction');
        $method->setContent('parent::updateAction();');
        $this->getObject()->addMethod($method);
    }

    /**
     * Builds the show action
     * @return void
     */
    private function buildShowAction() {
        $api = new Swagger\Api();
        $api->setPath('/' . $this->getClassName() . '/{id}');
        $operation = new Swagger\Operation();
        $operation->setMethod('GET');
        $operation->setSummary('Find ' . ucfirst($this->getModel()->getSource()) . ' by id.');
        $operation->setNotes('Returns ' . $this->singularModelA_Or_An() . ' based on ID.');

        $responseMessage = new Swagger\ResponseMessage();
        $responseMessage->setCode(200);
        $responseMessage->setMessage(ucfirst($this->getModel()->getSource()));
        $responseMessage->setResponseModel($this->getClassName());
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->invalidFieldSpecifiedError();
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->notAuthorizedResponse();
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->notFoundResponse();
        $operation->addResponseMessage($responseMessage);

        $operation->addParameter($this->buildIdParameter());
        $operation->addParameter($this->buildFieldsParameter());
        $api->addOperation($operation);

        $method = new Object\Method();
        $method->setDescription($api->__toString());
        $method->setReturnType('void');
        $method->setAccess('public');
        $method->setName('showAction');
        $method->setContent('parent::showAction();');
        $this->getObject()->addMethod($method);
    }

    /**
     * Builds the delete action
     * @return void
     */
    private function buildDeleteAction() {
        $api = new Swagger\Api();
        $api->setPath('/' . $this->getClassName() . '/{id}');
        $operation = new Swagger\Operation();
        $operation->setMethod('DELETE');
        $operation->setSummary('Deletes ' . $this->singularModelA_Or_An() . '.');
        $operation->setNotes('');

        $responseMessage = new Swagger\ResponseMessage();
        $responseMessage->setCode(204);
        $responseMessage->setMessage(ucfirst($this->getModel()->getSource()) . " deleted.");
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->notAuthorizedResponse();
        $operation->addResponseMessage($responseMessage);

        $responseMessage = $this->notFoundResponse();
        $operation->addResponseMessage($responseMessage);

        $parents = $this->getDeletionPreventingParents();
        if(!empty($parents)) {
            $responseMessage = new Swagger\ResponseMessage();
            $responseMessage->setCode(409);
            $responseMessage->setMessage(
                ucfirst($this->getModel()->getSource()) . " is connected by foreign key to one of the following entities: " . implode(', ', $parents)
            );
            $operation->addResponseMessage($responseMessage);
        }

        $operation->addParameter($this->buildIdParameter());
        $api->addOperation($operation);

        $method = new Object\Method();
        $method->setDescription($api->__toString());
        $method->setReturnType('void');
        $method->setAccess('public');
        $method->setName('deleteAction');
        $method->setContent('parent::deleteAction();');
        $this->getObject()->addMethod($method);
    }

    /**
     * Builds and returns the parameter of fields
     * @return Swagger\Parameter
     */
    private function buildFieldsParameter() {
        list($parentEntities, $childrenEntities) = $this->getParentAndChildrenEntities();

        $parameter = new Swagger\Parameter();
        $parameter->setName('fields');
        $parameter->setDescription(
            'Can be fields on this entity, parent entities, or children entities. Specify entities other than this one with type.field. Eligible entities are: '
            . implode(', ', array_merge($parentEntities, $childrenEntities)) . '. Optional.'
        );
        $parameter->setParamType('query');
        $parameter->setType('string');
        $parameter->setAllowMultiple(true);
        return $parameter;
    }

    /**
     * Builds the id parameter
     * @return Swagger\Parameter
     */
    private function buildIdParameter() {
        $parameter = new Swagger\Parameter();
        $parameter->setName('id');
        $parameter->setDescription('Which ' . ucfirst($this->getModel()->getSource()) . ' to target.');
        $parameter->setParamType('path');
        $parameter->setType('integer');
        $parameter->setRequired(true);

        return $parameter;
    }

    /**
     * Builds the start parameter
     * @return Swagger\Parameter
     */
    private function buildStartParameter() {
        $parameter = new Swagger\Parameter();
        $parameter->setName('start');
        $parameter->setDescription('Which record to start retrieving results from in the database. Defaults to 0 which is the first record. Optional.');
        $parameter->setParamType('query');
        $parameter->setType('integer');

        return $parameter;
    }

    /**
     * Builds the count parameter
     * @return Swagger\Parameter
     */
    private function buildCountParameter() {
        $parameter = new Swagger\Parameter();
        $parameter->setName('count');
        $parameter->setDescription('How many records to retrieve from the start point. Optional.');
        $parameter->setParamType('query');
        $parameter->setType('integer');

        return $parameter;
    }

    /**
     * Builds the sort parameter
     * @return Swagger\Parameter
     */
    private function buildSortParameter() {
        $parameter = new Swagger\Parameter();
        $parameter->setName('sort');
        $parameter->setDescription(
            'Any field on the model can be a sort field. The sort can contain multiple fields, comma separated.
			It is also possible to get a reverse sort by adding a - in front of the field . Optional.'
        );
        $parameter->setParamType('query');
        $parameter->setType('string');

        return $parameter;
    }

    /**
     * Builds a list of search parameters
     * @return Swagger\Parameter[]
     */
    private function buildSearchParameters() {
        $fields = $this->getModelBuilder()->getFields();
        $numberOfFields = sizeof($fields);
        if($numberOfFields > 5) {
            $numberOfFields = 5;
        }
        $parameters = array();
        for($i = 0; $i < $numberOfFields; $i++) {
            $parameter = new Swagger\Parameter();
            $parameter->setName($fields[$i]->getShortName());
            $parameter->setDescription($fields[$i]->getComment());
            $parameter->setParamType('query');
            $parameter->setType($fields[$i]->getSwaggerType());
            $parameters[] = $parameter;
        }

        return $parameters;
    }

    /**
     * Builds the class
     * @return void
     */
    private function build() {
        $this->getObject()->addUse('Bullhorn\FastRest\Api\Models\ApiInterface as ModelInterface');
        $this->getObject()->setName(ucfirst(strtolower($this->getClassName())) . 'Controller');
        $this->getObject()->setNamespace($this->getConfiguration()->getRootNamespace() . '\Controllers');
        $this->getObject()->setExtends('ControllerBase');
        $this->buildFactory();
        $this->buildIndexAction();
        $this->buildShowAction();
        $this->buildCreateAction();
        $this->buildUpdateAction();
        $this->buildDeleteAction();
        $this->buildDocumentation();
    }

    /**
     * Outputs
     * @return void
     */
    public function output() {
        header('Content-Type: text/plain');
        echo $this->__toString();
    }

    /**
     * __toString
     * @return string
     */
    public function __toString() {
        return $this->getObject()->toString();
    }

    /**
     * All gets can return this error message.
     *
     * @return Swagger\ResponseMessage
     */
    private function invalidFieldSpecifiedError() {
        $responseMessage = new Swagger\ResponseMessage();
        $responseMessage->setCode(400);
        $responseMessage->setMessage('Invalid field specified.');

        return $responseMessage;
    }

    /**
     * The user is not authorized to use this resource.
     * @return Swagger\ResponseMessage
     */
    private function notAuthorizedResponse() {
        $responseMessage = new Swagger\ResponseMessage();
        $responseMessage->setCode(401);
        $responseMessage->setMessage('Not authorized to access this resource.');

        return $responseMessage;
    }

    /**
     * Unable to find the requested resource.
     * @return Swagger\ResponseMessage
     */
    private function notFoundResponse() {
        $responseMessage = new Swagger\ResponseMessage();
        $responseMessage->setCode(404);
        $responseMessage->setMessage(ucfirst($this->getModel()->getSource()) . ' not found.');

        return $responseMessage;
    }

    /**
     * Return the parent and children entities for this model
     * @return array
     */
    private function getParentAndChildrenEntities() {
        $parentEntities = array();
        $childrenEntities = array();
        foreach($this->getModelBuilder()->getRelationships() as $relationship) {
            if($relationship->isPlural()) {
                $parentEntities[] = $relationship->getAlias() . '.*';
            } else {
                $childrenEntities[] = $relationship->getAlias() . '.*';
            }
        }

        return array($parentEntities, $childrenEntities);
    }

    /**
     * Return the parents that prevent deletion
     * @return array
     */
    private function getDeletionPreventingParents() {
        $parentEntities = array();
        foreach($this->getModelBuilder()->getRelationships() as $relationship) {
            if($relationship->isPlural() && !$relationship->isNullable() && $relationship->getAction() != 'ACTION_CASCADE') {
                $parentEntities[] = $relationship->getAlias();
            }
        }

        return $parentEntities;
    }

    /**
     * Bad data was passed via the API
     * @return Swagger\ResponseMessage
     */
    private function invalidValuePassed() {
        $responseMessage = new Swagger\ResponseMessage();
        $responseMessage->setCode(409);
        $responseMessage->setMessage('A passed value violates some restriction.  See the response body for more information.');

        return $responseMessage;
    }

    private function singularModelA_Or_An() {
        $name = ucfirst($this->getModel()->getSource());
        $vowels = array('A', 'E', 'I', 'O', 'U');
        if(in_array($name[0], $vowels)) {
            $name = 'an ' . $name;
        } else {
            $name = 'a ' . $name;
        }
        return $name;
    }
}