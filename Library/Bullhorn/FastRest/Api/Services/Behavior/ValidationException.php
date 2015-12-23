<?php
namespace Bullhorn\FastRest\Api\Services\Behavior;

use Bullhorn\FastRest\Api\Models\GeneratedInterface;
use Phalcon\Validation\Exception as Exception;

class ValidationException extends Exception {
    /** @var GeneratedInterface */
    private $entity;

    /**
     * Getter
     * @return GeneratedInterface
     */
    public function getEntity() {

        return $this->entity;
    }

    /**
     * Setter
     * @param GeneratedInterface $entity
     */
    public function setEntity(GeneratedInterface $entity) {
        $this->entity = $entity;
    }


}