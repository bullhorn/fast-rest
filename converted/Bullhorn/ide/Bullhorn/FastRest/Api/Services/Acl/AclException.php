<?php
namespace Bullhorn\FastRest\Api\Services\Acl
{
    use Phalcon\Validation\Exception;

    class AclException extends Exception 
    {
        /**
         * @var EntityInterface *
         */
        protected $entity;

        /**
         * Getter
         *
         * @return EntityInterface
         */
        public function getEntity()
        {}

        /**
         * Setter
         *
         * @param EntityInterface $entity
         */
        public function setEntity($entity)
        {}

    }

}

