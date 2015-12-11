<?php
namespace Bullhorn\FastRest\Api\Services\ControllerHelper;

use Bullhorn\FastRest\Api\Models\ApiInterface;
use Bullhorn\FastRest\Api\Services\Database\Transaction;
use Phalcon\Mvc\Model\Transaction\Failed as TransactionException;

/**
 * Class Delete
 * @package Bullhorn\FastRest\Api\Services\ControllerHelper
 */
class Delete extends Base {

    /** @var  ApiInterface */
    private $entity;

    const EVENT_DATA_PROPAGATION_DELETE = 'event_data_propagation_delete';

    /**
     * Constructor
     * @param ApiInterface $entity
     */
    public function __construct(ApiInterface $entity) {
        $this->setEntity($entity);
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

    /**
     * Saves all possible post variables
     *
     * @param \Bullhorn\FastRest\Api\Services\Acl\AclInterface $acl
     *
     * @return bool if anything was changed
     *
     * @throws \Exception
     */
    public function process($acl) {
        $transactionManager = new Transaction();
        $transactionManager->begin();
        $transaction = $transactionManager->getTransaction();
        $this->getEntity()->setTransaction($transaction);
        try {
            $acl->canWrite($this->getEntity());
            $this->getEntity()->fireEvent(self::EVENT_DATA_PROPAGATION_DELETE);
            $this->getEntity()->delete();
            $this->getEntity()->fireEvent(Save::EVENT_DATA_FINAL_CLEANUP);
            $transactionManager->commit();
        } catch(TransactionException $e) {
            $transactionManager->rollback();
        }
    }
}