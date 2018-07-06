namespace Bullhorn\FastRest\Api\Services\Acl;

use Phalcon\Http\Request\Exception;
interface AclInterface
{
    const DI_NAME = "Acl";
    /**
     * Gets if you have access to read this entity
     *
     * @param EntityInterface $entity
     *
     * @return void
     * @throws Exception
     * @throws AclException
     */
    public function canRead(<EntityInterface> entity);
    
    /**
     * THIS ONLY CHECKS IF YOU CAN READ A SPECIFIC FIELD DO TO PERSONAL INFORMATION RULES, NOT IF YOU CAN READ THE ENTITY.
     *
     * @param EntityInterface $entity
     * @param string          $fieldName
     *
     * @return bool
     */
    public function canReadField(<EntityInterface> entity, string fieldName) -> bool;
    
    /**
     * THIS ONLY CHECKS IF YOU CAN WRITE A SPECIFIC FIELD DO TO PERSONAL INFORMATION RULES, NOT IF YOU CAN READ THE ENTITY.
     *
     * @param EntityInterface $entity
     * @param string          $fieldName
     *
     * @return bool
     */
    public function canWriteField(<EntityInterface> entity, string fieldName) -> bool;
    
    /**
     * Gets if you have access to write this entity
     *
     * @param EntityInterface $entity
     *
     * @return void
     * @throws Exception
     * @throws AclException
     */
    public function canWrite(<EntityInterface> entity);

}