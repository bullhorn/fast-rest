<?php
namespace Bullhorn\FastRest\Api\Controllers;

use Bullhorn\FastRest\Api\Models\HttpStatusCode;
use Bullhorn\FastRest\Api\Services\ControllerHelper\SearchTerm;
use Bullhorn\FastRest\Api\Services\Exception\CatchableException;
use Bullhorn\FastRest\Api\Models\ControllerModelInterface as ModelInterface;
use Bullhorn\FastRest\Api\Models\CreateObject;
use Bullhorn\FastRest\Api\Services\ControllerHelper\Index;
use Bullhorn\FastRest\Api\Services\ControllerHelper\Params;
use Bullhorn\FastRest\Api\Services\ControllerHelper\Save;
use Bullhorn\FastRest\Api\Services\ControllerHelper\Delete;
use Bullhorn\FastRest\Api\Services\ControllerHelper\Show;
use Bullhorn\FastRest\Api\Services\Acl\AclException;
use Bullhorn\FastRest\Api\Services\ControllerHelper\ShowCriteria;
use Phalcon\Http\Request\Exception;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Resultset\Simple as ResultSet;
use Bullhorn\FastRest\Api\Services\Behavior\ValidationException;
use Bullhorn\FastRest\Api\Services\Output\OutputInterface;
use Bullhorn\FastRest\Api\Services\Acl\AclInterface;
use Bullhorn\FastRest\Api\Services\DataTransform\Base as DataTransformer;

/**
 * Class ControllerBase
 */
abstract class Base extends Controller {
    /** @var  \stdClass */
    private $outputObject;
    /** @var Exception[] */
    private $errors = array();
    /** @var int Server Status Code */
    private $statusCode;

    const STATUS_CODE_BAD_REQUEST = 400;
    const STATUS_CODE_NOT_FOUND = 404;

    /**
     * Initializes
     * @return void
     */
    public function beforeExecuteRoute() {
        $this->validateServicesDefined();
        $this->getDi()->set('Request', $this->request);
        $this->setStatusCode(200);
        $this->setOutputObject(new \stdClass());
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        try {
            $this->validateLogin();
        } catch(Exception $e) {
            $this->handleError($e);
            $this->afterExecuteRoute();
            exit;
        }
    }

    /**
     * Validates to make sure all required services are defined
     * @return void
     * @throws \Exception
     */
    private function validateServicesDefined() {
        if(!$this->getDi()->has(AclInterface::DI_NAME)) {
            throw new \Exception('Service ' . AclInterface::class . '::DI_NAME must be defined with a type of: ' . AclInterface::class);
        }
        if(!$this->getDi()->has(OutputInterface::DI_NAME)) {
            throw new \Exception('Service ' . OutputInterface::class . '::DI_NAME must be defined with a type of: ' . OutputInterface::class);
        }
    }


    /**
     * This is used to give an error if you are accessing an action that you are not allowed to, such as if a controller is not creatable or deletable
     *
     * @param string $errorMessage
     *
     * @return void
     */
    protected function throwUnAccessibleAction($errorMessage) {
        $this->setErrors(
            [
                new Exception($errorMessage, 405)
            ]
        );
    }

    /**
     * When called, this should return a new entity
     * @return ModelInterface
     */
    abstract public function generateEntity();

    /**
     * Sets HTTP Status Code
     * @return int
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * Returns HTTP Status Code
     * @param int $statusCode
     */
    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
    }

    /**
     * Adds an error
     *
     * @param Exception $e
     *
     * @return void
     */
    protected function addError(Exception $e) {
        $errors = $this->getErrors();
        $errors[] = $e;
        $this->setErrors($errors);
    }

    /**
     * Getter
     * @return \Phalcon\Http\Request\Exception[]
     */
    protected function getErrors() {
        return $this->errors;
    }

    /**
     * Setter
     * @param \Phalcon\Http\Request\Exception[] $errors
     */
    protected function setErrors(array $errors) {
        $this->errors = $errors;
    }

    /**
     * Getter
     * @return \stdClass
     */
    protected function getOutputObject() {
        return $this->outputObject;
    }

    /**
     * Setter
     * @param \stdClass $outputObject
     */
    protected function setOutputObject(\stdClass $outputObject) {
        $this->outputObject = $outputObject;
    }

    /**
     * Gets a list of parameters that are always allowed in the query, aka any tokens or authentication information
     * @return string[]
     */
    abstract protected function getQueryWhiteList();

    /**
     * This is used if you want to filter the list of entities, or append to the list
     * @param ResultSet|ModelInterface[] $entities
     * @return array
     */
    protected function filterEntities($entities) {
        return $entities;
    }

    /**
     * This provides a list of the entities
     * @return void
     */
    protected function indexAction() {
        try {
            $entity = $this->generateEntity();

            $query = new Index($this->request, $entity, $this->getQueryWhiteList());
            $this->response->setHeader('link', $query->generateLinks());
            /** @var ResultSet|ModelInterface[] $entities */
            $entities = $query->getResultSet();
            $entities = $this->filterEntities($entities);
            $this->indexActionInternal($entities);
            if($this->hasFlag((new FlagEnum(FlagEnum::PAGE_COUNTS)))) {
                $this->getOutputObject()->PageCounts = $query->generatePageCounts();
            }
        } catch(Exception $e) {
            $this->handleError($e);
        } catch(ValidationException $e) {
            $this->handleValidationError($e);
        } catch(AclException $e) {
            $this->handleAclError($e);
        } catch(CatchableException $e) {
            $this->handleCatchableError($e);
        }
    }

    private function hasFlag(FlagEnum $name): bool {
        if(!$this->request->hasQuery('flag')) {
            return false;
        }
        $flags = $this->request->getQuery('flag');
        if(!is_array($flags)) {
            return false;
        }
        return array_key_exists((string)$name, $flags);
    }

    /**
     * getExtraAllowedHeaders
     * @return string[]
     */
    public function getExtraAllowedHeaders() {
        return [];
    }

    /**
     * Needed for CORs
     * @return void
     */
    public function optionsAction() {
        $this->response->setHeader('Access-Control-Allow-Methods', 'POST, GET, PUT, DELETE, OPTIONS');
        $allowedHeaders = array_merge(
            ['X-Requested-With', 'X-HTTP-Method-Override', 'Content-Type', 'Accept', 'Cache-Control'],
            $this->getExtraAllowedHeaders()
        );
        $this->response->setHeader('Access-Control-Allow-Headers', implode(', ', $allowedHeaders));
        $this->setStatusCode(200);
    }

    /**
     * This is how you create a new entity, returns showAction on the specified entity.
     * This would be called using the POST method, with the url: v{versionNumber}/{Entities}
     * This returns the showAction
     * @return void
     */
    public function createAction() {
        $this->setStatusCode(201);
        $createObjects = $this->createActionProcess();

        if(sizeOf($createObjects) == 1) { //Default handling of passing in one
            $createObject = $createObjects[0];
            $this->setErrors($createObject->getErrors());
            $this->setStatusCode($createObject->getStatusCode());
            if(!is_null($createObject->getEntity())) {
                try {
                    $this->showActionInternal($createObject->getEntity());
                } catch(Exception $e) {
                    $this->handleError($e);
                } catch(ValidationException $e) {
                    $this->handleValidationError($e);
                } catch(AclException $e) {
                    $this->handleAclError($e);
                } catch(CatchableException $e) {
                    $this->handleCatchableError($e);
                }
            }
        } else {
            $this->setStatusCode(400);
            $hasOneSuccessful = false;
            $objects = [];
            foreach($createObjects as $createObject) {
                $object = new \stdClass();
                try {
                    if(!is_null($createObject->getEntity())) {
                        $hasOneSuccessful = true;
                        $object->{$createObject->getEntity()->getEntityName()} = $this->generateEntityAction($createObject->getEntity());
                    }
                    $object = $this->addErrorsAndStatus($createObject->getErrors(), $createObject->getStatusCode(), $object);
                } catch(Exception $e) {
                    $this->handleError($e);
                } catch(ValidationException $e) {
                    $this->handleValidationError($e);
                } catch(AclException $e) {
                    $this->handleAclError($e);
                } catch(CatchableException $e) {
                    $this->handleCatchableError($e);
                }
                $objects[] = $object;
            }
            $blankEntity = $this->generateEntity();
            $outputObject = $this->getOutputObject();
            $outputObject->{$blankEntity->getEntityName() . 's'} = $objects;
            $this->setOutputObject($outputObject);
            if($hasOneSuccessful) {
                $this->setStatusCode(201);
            }
        }


    }

    /**
     * Creates the entities, returns a list of createObjects, to process accordingly
     * @return \Bullhorn\FastRest\Api\Models\CreateObject[]
     */
    private function createActionProcess() {
        /** @var CreateObject[] $createObjects */
        $createObjects = [];
        try {
            $params = new Params($this->request);
        } catch(Exception $e) {
            $this->handleError($e);
            return $createObjects;
        }
        $postParams = $params->getParams();
        if(!is_array($postParams)) {
            $postParams = [$postParams];
        }
        foreach($postParams as $postParam) {
            $createObject = new CreateObject($postParam);
            try {
                $entity = $this->createActionInternal($postParam);
                $createObject->setEntity($entity);

            } catch(Exception $e) {
                $this->handleError($e);
            } catch(ValidationException $e) {
                $this->handleValidationError($e);
            } catch(AclException $e) {
                $this->handleAclError($e);
            } catch(CatchableException $e) {
                $this->handleCatchableError($e);
            }
            $createObject->setStatusCode($this->getStatusCode());
            $createObject->setErrors($this->getErrors());
            $this->setErrors([]);
            $createObjects[] = $createObject;
        }
        return $createObjects;
    }

    /**
     * Provides the actual creating of a new entity.
     * @param \stdClass $postParams
     * @return ModelInterface
     */
    private function createActionInternal(\stdClass $postParams) {
        $entity = $this->generateEntity();
        $this->saveEntity($postParams, $entity, true);
        // since our entity can be manipulated after saving, we need to find it again, just in case.
        /** @var ModelInterface $newEntity */
        $newEntity = $entity->findFirst($entity->getId());
        //Fix if there are two entities created (with bulk insert), and the first fails, but the second succeeds, the second with get the first's errors
        $reflectionClass = new \ReflectionClass($newEntity);
        $reflectionProperty = $reflectionClass->getProperty('_errorMessages');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($newEntity, []);
        return $newEntity;
    }

    /**
     * Looks up an individual entity
     * This would be called using the GET method, with the url: v{versionNumber}/{Entities}/{entityId}
     *
     * @return void
     */
    public function showAction() {
        try {
            if(sizeOf($this->dispatcher->getParams()) == 0) {
                throw new Exception('Invalid Entity Id Passed In', 400);
            }
            $entity = $this->validateEntityId($this->dispatcher->getParam(0));
            $this->showActionInternal($entity);
        } catch(Exception $e) {
            $this->handleError($e);
        } catch(ValidationException $e) {
            $this->handleValidationError($e);
        } catch(AclException $e) {
            $this->handleAclError($e);
        }
    }

    /**
     * getSearchFields
     * @return string[] db fields to search on
     * @throws \Exception
     */
    protected function getSearchFields(): array {
        throw new \Exception('If you wish to use searchAction, you must implement the searchFields method');
    }

    public function searchAction() {
        try {
            if(sizeOf($this->dispatcher->getParams()) == 0) {
                $this->indexAction();
                return;
            }
            $entity = $this->generateEntity();

            $searchTerm = new SearchTerm($this->getSearchFields(), $this->dispatcher->getParam(0));
            $query = new Index($this->request, $entity, $this->getQueryWhiteList(), $searchTerm);
            $this->response->setHeader('link', $query->generateLinks());
            /** @var ResultSet|ModelInterface[] $entities */
            $entities = $query->getResultSet();
            $entities = $this->filterEntities($entities);
            $objects = array();
            foreach($entities as $entity) {
                $objects[] = $this->generateEntityAction($entity);
            }
            $outputObject = $this->getOutputObject();
            $blankEntity = $this->generateEntity();
            $outputObject->{$blankEntity->getEntityName() . 's'} = $objects;
            if($this->hasFlag((new FlagEnum(FlagEnum::PAGE_COUNTS)))) {
                $outputObject->PageCounts = $query->generatePageCounts();
            }
            $this->setOutputObject($outputObject);
        } catch(Exception $e) {
            $this->handleError($e);
        } catch(ValidationException $e) {
            $this->handleValidationError($e);
        } catch(AclException $e) {
            $this->handleAclError($e);
        } catch(CatchableException $e) {
            $this->handleCatchableError($e);
        }
    }


    /**
     * Looks up an individual entity
     *
     * @param ModelInterface $entity
     *
     * @return void
     */
    protected function showActionInternal(ModelInterface $entity) {
        $outputObject = $this->getOutputObject();
        $outputObject->{$entity->getEntityName()} = $this->generateEntityAction($entity);
        $this->setOutputObject($outputObject);
    }

    /**
     * Generates the output of an entity
     *
     * @param ModelInterface $entity
     *
     * @return \stdClass
     */
    private function generateEntityAction(ModelInterface $entity) {
        $show = new Show($entity);
        $showCriteria = new ShowCriteria($this->request);
        return $show->generate($showCriteria->getField());
    }


    /**
     * Updates an individual entity
     * This would be called using the PUT method, with the url: v{versionNumber}/{Entities}/{entityId}
     * This returns the showAction
     *
     * @return void
     */
    public function updateAction() {
        try {
            if(sizeOf($this->dispatcher->getParams()) == 0) {
                throw new Exception('Invalid Entity Id Passed In', 400);
            }
            $entity = $this->validateEntityId($this->dispatcher->getParam(0));
            $isChanged = $this->updateActionInternal($entity);
            if($isChanged) {
                $this->showActionInternal($entity->findFirst($entity->getId()));
            }
        } catch(Exception $e) {
            $this->handleError($e);
        } catch(ValidationException $e) {
            $this->handleValidationError($e);
        } catch(AclException $e) {
            $this->handleAclError($e);
        } catch(CatchableException $e) {
            $this->handleCatchableError($e);
        }
    }

    /**
     * Gets the data transformer, if there is one
     * @param \stdClass $postParams
     * @return null|DataTransformer
     */
    protected function getDataTransformer(\stdClass $postParams) {
        return null;
    }

    /**
     * Saves an entity (either creating or updating)
     *
     * @param \stdClass      $postParams
     * @param ModelInterface $entity
     * @param bool           $isCreating
     *
     * @return bool if anything was changed
     */
    protected function saveEntity(\stdClass $postParams, ModelInterface $entity, $isCreating) {
        $postParams = $this->findPostParams($postParams, $entity);
        /** @var Save $save */
        $save = $this->getDI()->get(Save::class, [$this->request, $entity, $isCreating]);
        return $save->process($postParams);
    }

    /**
     * Finds the post parameters
     * @param \stdClass      $postParams
     * @param ModelInterface $entity
     * @return \stdClass
     */
    private function findPostParams(\stdClass $postParams, ModelInterface $entity) {
        //Entity is passed in so children can access it
        $dataTransformer = $this->getDataTransformer($postParams);
        if(!is_null($dataTransformer)) {
            $dataTransformer->transform($entity);
            $postParams = $dataTransformer->getParams();
        }
        return $postParams;
    }

    /**
     * Updates an entity
     *
     * @param ModelInterface $entity
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function updateActionInternal(ModelInterface $entity) {
        $params = new Params($this->request);
        if(is_array($params->getParams())) {
            throw new Exception('Bulk Updating is not supported: An array of objects was passed in', 400);
        }
        $this->saveEntity($params->getParams(), $entity, false);
        // we stopped returning a 304 because the internet is a terrible place where no one follows the rules.
        return true;
    }

    /**
     * Deletes an individual entity
     *
     * @return void
     */
    public function deleteAction() {
        try {
            if(sizeOf($this->dispatcher->getParams()) == 0) {
                throw new Exception('Invalid Entity Id Passed In', 400);
            }
            $entity = $this->validateEntityId($this->dispatcher->getParam(0));
            $this->deleteActionInternal($entity);
        } catch(Exception $e) {
            $this->handleError($e);
        } catch(ValidationException $e) {
            $this->handleValidationError($e);
        } catch(AclException $e) {
            $this->handleAclError($e);
        }
    }

    /**
     * Gets the Access Control Layer
     * @return AclInterface
     * @throws \Exception
     */
    public function getAcl() {
        $returnVar = $this->getDi()->get(AclInterface::DI_NAME);
        if(!($returnVar instanceof AclInterface)) {
            throw new \Exception('The Acl must implement: ' . AclInterface::class);
        }
        return $returnVar;
    }

    /**
     * Default delete action for our entities.
     *
     * @param ModelInterface $entity
     *
     * @return void
     */
    protected function deleteActionInternal(ModelInterface $entity) {
        $this->setStatusCode(204);
        $delete = new Delete($entity);
        $delete->process($this->getAcl());
    }

    /**
     * This validates an entity id, and looks up the entity associated with it
     *
     * @param int $entityId
     *
     * @return ModelInterface
     * @throws Exception
     */
    protected function validateEntityId($entityId) {
        if(!is_numeric($entityId)) {
            throw new Exception('Invalid Entity Id: Must be numeric', 400);
        }
        return $this->lookUpEntity($entityId);
    }

    /**
     * This looks up an entity based off of the entity id
     *
     * @param int $entityId
     *
     * @throws Exception - If unable to find the entity, return a 404 to the user.
     *
     * @return ModelInterface
     */
    protected function lookUpEntity($entityId) {
        $entity = $this->generateEntity();
        $entityInstance = $entity->findFirst($entityId);
        if($entityInstance === false) {
            throw new Exception("Invalid Entity Id: Entity not found.", 404);
        }
        return $entityInstance;
    }

    /**
     * Executed after it is routed
     *
     * @return void
     *
     * @throws \Exception
     */
    public function afterExecuteRoute() {
        /** @var \Bullhorn\FastRest\Api\Services\Output\OutputInterface $output */
        $output = $this->getDI()->get(OutputInterface::DI_NAME);
        if(!($output instanceof OutputInterface)) {
            throw new \Exception('The Output must implement: ' . OutputInterface::class);
        }
        $object = $this->getOutputObject();
        $errors = $this->getErrors();
        $code = $this->getStatusCode();

        $object = $this->addErrorsAndStatus($errors, $code, $object);
        $this->response->setStatusCode($object->statusCode, 'Check Document Body For More Details');
        $output->output($object, $this->response);
        $this->view->disable();
    }

    /**
     * addErrorsAndStatus
     * @param Exception[] $errors
     * @param int $code
     * @param \stdClass $outputObject
     * @return \stdClass
     */
    private function addErrorsAndStatus(array $errors, $code, \stdClass $outputObject) {
        if(!empty($errors)) {
            $outputErrors = array();
            foreach($errors as $error) {
                $outputErrors[] = $error->getMessage();
                if($error->getCode() != 0) {
                    $code = $error->getCode();
                }
            }
            $outputObject->errors = $outputErrors;
        }
        $outputObject->statusCode = $code;
        return $outputObject;
    }

    /**
     * Adds the error message
     *
     * @param Exception $e
     *
     * @return void
     */
    protected function handleError(Exception $e) {
        $this->addError($e);
    }

    /**
     * handleCatchableError
     * @param CatchableException $e
     * @return void
     */
    protected function handleCatchableError(CatchableException $e) {
        $this->addError(new Exception($e->getMessage(), 400));
    }

    /**
     * Handles exceptions from validations
     *
     * @param ValidationException $e
     * @param int $errorCode
     *
     * @return void
     */
    protected function handleValidationError(ValidationException $e, $errorCode = 409) {
        $entity = $e->getEntity();
        foreach($entity->getMessages() as $message) {
            $this->addError(new Exception($message->getMessage(), $errorCode));
        }
    }

    /**
     * Handles exceptions from acl
     *
     * @param AclException $e
     *
     * @return void
     */
    protected function handleAclError(AclException $e) {
        if(!empty($e->getMessage())) {
            $this->addError(new Exception($e->getMessage(), 401));
        }
        $entity = $e->getEntity();
        if(!is_null($entity)) {
            foreach ($entity->getMessages() as $message) {
                $this->addError(new Exception($message->getMessage(), 401));
            }
        }
    }

    /**
     * Validates that they have a valid login
     * @return void
     * @throws Exception
     */
    abstract protected function validateLogin();

    /**
     * indexActionInternal
     * @param $entities
     * @return void
     */
    protected function indexActionInternal($entities): void {
        $objects = [];
        foreach($entities as $entity) {
            $objects[] = $this->generateEntityAction($entity);
        }
        $outputObject = $this->getOutputObject();
        $blankEntity = $this->generateEntity();
        $outputObject->{$blankEntity->getEntityName() . 's'} = $objects;

        $this->setOutputObject($outputObject);
    }

}
