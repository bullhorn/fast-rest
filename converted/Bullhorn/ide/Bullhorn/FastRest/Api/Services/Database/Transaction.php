<?php
namespace Bullhorn\FastRest\Api\Services\Database
{
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
        const EVENT_ROLLBACK = 'transaction:rollback';

        const EVENT_COMMIT = 'transaction:commit';

        /**
         * 
         *
         * @static
         */
        protected static $stackTrace;

        /**
         * @type  string *
         */
        protected $dbService;

        /**
         * @type TransactionInterface[] *
         * @static
         */
        protected static $transactions;

        /**
         * Transaction constructor.
         *
         * @param string $dbService
         */
        public function __construct(string $dbService = 'db')
        {}

        public function getDi()
        {}

        public function setDi($di)
        {}

        /**
         * Getter
         *
         * @return TransactionInterface[]
         */
        protected function getTransactions()
        {}

        /**
         * Setter
         *
         * @param TransactionInterface[] $transactions
         */
        protected function setTransactions(array $transactions)
        {}

        /**
         * setTransaction
         *
         * @param TransactionInterface $transaction
         * @return void
         */
        protected function setTransaction($transaction)
        {}

        /**
         * Getter
         *
         * @return string
         */
        protected function getDbService()
        {}

        /**
         * Setter
         *
         * @param string $dbService
         */
        protected function setDbService(string $dbService)
        {}

        /**
         * Begin a transaction
         *
         * @return void
         * @throws \Exception
         */
        public function begin()
        {}

        /**
         * isInTransaction
         *
         * @return bool
         */
        public function isInTransaction()
        {}

        /**
         * isInAnyTransaction
         *
         * @return bool
         */
        public function isInAnyTransaction()
        {}

        /**
         * Gets the current transaction, throws an exception if there isn't one
         *
         * @return TransactionInterface
         * @throws \Exception
         */
        public function getTransaction()
        {}

        /**
         * Rollback a transaction
         *
         * @return void
         * @throws \Exception
         */
        public function rollback()
        {}

        /**
         * Commit a transaction
         *
         * @return void
         * @throws \Exception
         */
        public function commit()
        {}

        /**
         * findEventsManager
         *
         * @return EventsManager
         */
        public function findEventsManager()
        {}

    }

}

