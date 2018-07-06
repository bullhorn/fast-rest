namespace Bullhorn\FastRest\Api\Services\Acl;

use Phalcon\Validation\Exception;
class AclException extends Exception
{
    /** @var EntityInterface */
    protected entity;
    /**
     * Getter
     * @return EntityInterface
     */
    public function getEntity() -> <EntityInterface>
    {
        return this->entity;
    }
    
    /**
     * Setter
     * @param EntityInterface $entity
     */
    public function setEntity(<EntityInterface> entity) -> void
    {
        let this->entity = entity;
    }

}