<?php
namespace Bullhorn\FastRest\Api\Services\Acl
{
    use Phalcon\Http\Request\Exception;

    interface AclInterface 
    {
        /**
         * Gets if you have access to read this entity
         *
         * @param EntityInterface $entity
         * @return void
         * @throws Exception
         * @throws AclException
         */
        public function canRead($entity);

        /**
         * THIS ONLY CHECKS IF YOU CAN READ A SPECIFIC FIELD DO TO PERSONAL INFORMATION RULES, NOT IF YOU CAN READ THE ENTITY.
         *
         * @param EntityInterface $entity
         * @param string          $fieldName
         * @return bool
         */
        public function canReadField($entity, string $fieldName);

        /**
         * THIS ONLY CHECKS IF YOU CAN WRITE A SPECIFIC FIELD DO TO PERSONAL INFORMATION RULES, NOT IF YOU CAN READ THE ENTITY.
         *
         * @param EntityInterface $entity
         * @param string          $fieldName
         * @return bool
         */
        public function canWriteField($entity, string $fieldName);

        /**
         * Gets if you have access to write this entity
         *
         * @param EntityInterface $entity
         * @return void
         * @throws Exception
         * @throws AclException
         */
        public function canWrite($entity);

    }


}

