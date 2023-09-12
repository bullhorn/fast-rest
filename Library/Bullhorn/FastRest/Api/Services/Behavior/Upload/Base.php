<?php
namespace Bullhorn\FastRest\Api\Services\Behavior\Upload;

use Bullhorn\FastRest\Api\Services\Behavior\ValidationException;
use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\DI\InjectionAwareInterface;
use Bullhorn\FastRest\DependencyInjection;
use Bullhorn\FastRest\Api\Models\ApiInterface;
use Phalcon\Http\Request;
use Phalcon\Http\Request\Exception;
use Phalcon\Mvc\Model\Message;
use Phalcon\Mvc\ModelInterface as MvcInterface;

abstract class Base extends Behavior implements BehaviorInterface, InjectionAwareInterface {
    use DependencyInjection;
    const EVENT_UPLOAD_FILE_CREATE = 'uploadFileCreate';
    const EVENT_UPLOAD_FILE_UPDATE = 'uploadFileUpdate';
    /** @var  File[] */
    private $files;
    /** @var  ApiInterface */
    private $entity;

    /**
     * Getter
     * @return File[]
     */
    public function getFiles() {
        return $this->files;
    }

    /**
     * Setter
     * @param File[] $files
     */
    private function setFiles(array $files) {
        $this->files = $files;
    }

    /**
     * Add a new file
     *
     * @param File $file
     *
     * @return void
     */
    protected function addFile(File $file) {
        $files = $this->getFiles();
        $files[$file->getName()] = $file;
        $this->setFiles($files);
    }

    /**
     * getEntity
     * @return ApiInterface
     */
    protected function getEntity() {
        return $this->entity;
    }

    /**
     * setEntity
     * @param ApiInterface $entity
     * @return ApiInterface
     */
    protected function setEntity(ApiInterface $entity) {
        $this->entity = $entity;
    }

    /**
     * Gets the requested object
     * @return Request
     */
    protected function getRequest() {
        return $this->getDi()->get('Request');
    }

    /**
     * processUploads
     * @param bool $isCreating
     * @return void
     * @throws Exception
     */
    protected function processUploads($isCreating) {
        $files = $this->getFiles();
        $keysFound = [];
        foreach($this->getRequest()->getUploadedFiles() as $uploadedFile) {
            $key = preg_replace('@\..*$@', '', $uploadedFile->getKey());
            if(array_key_exists($key, $files)) {
                $file = $files[$key];
                if(!$file->isAllowMultiple() && array_key_exists($key, $keysFound)) {
                    continue; //Already used
                }
                $keysFound[$key] = true;
                if($isCreating ? $file->isAllowedOnCreate() : $file->isAllowedOnUpdate()) {
                    $file->handle($uploadedFile);
                }
            }
        }
        $this->validateAllRequired($isCreating);
    }

    /**
     * Receives notifications from the Models Manager
     * @param string $eventType
     * @param ApiInterface|MvcInterface $entity
     * @return bool
     */
    final public function notify($eventType, MvcInterface $entity) {
        $instance = new static();
        $instance->setEntity($entity);
        switch($eventType) {
            case self::EVENT_UPLOAD_FILE_CREATE:
                $instance->processUploads(true);
                break;
            case self::EVENT_UPLOAD_FILE_UPDATE:
                $instance->processUploads(false);
                break;
        }
        $instance->notifyInstance($eventType);
    }

    protected function notifyInstance($eventType): void {
        //Override in child if necessary
    }

    /**
     * Validates that all required files were passed in
     * @param bool $isCreating
     * @return void
     * @throws ValidationException
     */
    private function validateAllRequired($isCreating) {
        foreach($this->getFiles() as $file) {
            if(!$file->isUsed()) {
                if($isCreating ? $file->isRequiredOnCreate() : $file->isRequiredOnUpdate()) {
                    $this->getEntity()->appendMessage(new Message('Missing the required file of: ' . $file->getName()));
                }
            }
        }
        if($this->getEntity()->validationHasFailed() == true) {
            $validationException = new ValidationException();
            $validationException->setEntity($this->getEntity());
            throw $validationException;
        }
    }
}