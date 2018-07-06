namespace Bullhorn\FastRest\Api\Services\Database;

use Bullhorn\FastRest\DependencyInjection;
use Bullhorn\FastRest\DependencyInjectionHelper;
use Phalcon\DI\InjectionAwareInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model\Transaction\Failed;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Mvc\Model\TransactionInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Db\Exception as DbException;
class Transaction implements InjectionAwareInterface
{
    const EVENT_ROLLBACK = "transaction:rollback";
    const EVENT_COMMIT = "transaction:commit";
    protected static stackTrace;
    /** @type  string */
    protected dbService;
    /** @type TransactionInterface[] */
    protected static transactions = [];
    /**
     * Transaction constructor.
     * @param string $dbService
     */
    public function __construct(string dbService = "db") -> void
    {
        this->setDbService(dbService);
    }

    public function getDi()
    {
        return DependencyInjectionHelper::getDi();
    }

    public function setDi(<DiInterface> di) -> void
    {
        DependencyInjectionHelper::setDi(di);
    }

    /**
     * Getter
     * @return TransactionInterface[]
     */
    protected function getTransactions() -> array
    {
        return self::transactions;
    }

    /**
     * Setter
     * @param TransactionInterface[] $transactions
     */
    protected function setTransactions(array transactions) -> void
    {
        let self::transactions = transactions;
    }

    /**
     * setTransaction
     * @param TransactionInterface $transaction
     * @return void
     */
    protected function setTransaction(<TransactionInterface> transaction)
    {
        var transactions;

        let transactions =  this->getTransactions();
        let transactions[this->getDbService()] = transaction;
        this->setTransactions(transactions);
    }

    /**
     * Getter
     * @return string
     */
    protected function getDbService() -> string
    {
        return this->dbService;
    }

    /**
     * Setter
     * @param string $dbService
     */
    protected function setDbService(string dbService) -> void
    {
        let this->dbService = dbService;
    }

    /**
     * Begin a transaction
     * @return void
     * @throws \Exception
     */
    public function begin()
    {
        var e, transactionManager;

        if this->isInTransaction() {
            throw new \Exception("You cannot start a new transaction once you are already in one (Originally started at: " . self::stackTrace . ")");
        } else {
            let e =  new \Exception();
            let self::stackTrace =  e->getTraceAsString();
        }
        let transactionManager =  new TransactionManager();
        transactionManager->setDbService(this->getDbService());
        this->setTransaction(transactionManager->get());
    }

    /**
     * isInTransaction
     * @return bool
     */
    public function isInTransaction() -> bool
    {
        var transactions;

        let transactions =  this->getTransactions();
        return array_key_exists(this->getDbService(), transactions);
    }

    /**
     * isInAnyTransaction
     * @return bool
     */
    public function isInAnyTransaction() -> bool
    {
        return !(empty(this->getTransactions()));
    }

    /**
     * Gets the current transaction, throws an exception if there isn't one
     * @return TransactionInterface
     * @throws \Exception
     */
    public function getTransaction() -> <TransactionInterface>
    {
        var transactions;

        if !(this->isInTransaction()) {
            throw new \Exception("This transaction has already been rolled back, or committed, or has not began yet");
        }
        let transactions =  this->getTransactions();
        return transactions[this->getDbService()];
    }

    /**
     * Rollback a transaction
     * @return void
     * @throws \Exception
     */
    public function rollback()
    {
        var e, transactions;

        try {
            this->getTransaction()->rollback();
        } catch Failed, e {
            if e->getMessage() != "Transaction aborted" {
                throw e;
            }
        } catch DbException, e {
            if e->getMessage() != "There is no active transaction" {
                throw e;
            }
        }
        let transactions =  this->getTransactions();
        unset transactions[this->getDbService()];
        this->setTransactions(transactions);
        this->findEventsManager()->fire(self::EVENT_ROLLBACK, this);
    }

    /**
     * Commit a transaction
     * @return void
     * @throws \Exception
     */
    public function commit()
    {
        var e, transactions;

        try {
            this->getTransaction()->commit();
        } catch DbException, e {
            if e->getMessage() != "There is no active transaction" {
                throw e;
            }
        }
        let transactions =  this->getTransactions();
        unset transactions[this->getDbService()];
        this->setTransactions(transactions);
        this->findEventsManager()->fire(self::EVENT_COMMIT, this);
    }

    /**
     * findEventsManager
     * @return EventsManager
     */
    public function findEventsManager()
    {
        return this->getDi()->get("eventsManager");
    }

}