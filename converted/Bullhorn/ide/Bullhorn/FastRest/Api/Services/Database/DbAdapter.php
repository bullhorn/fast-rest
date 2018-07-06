<?php
namespace Bullhorn\FastRest\Api\Services\Database
{
    use Phalcon\Db;
    use Phalcon\Db\Adapter\Pdo\Mysql as ParentDbAdapter;

    class DbAdapter extends ParentDbAdapter 
    {
        const MYSQL_WENT_AWAY_CODE = 'HY000';

        /**
         * Override of parent execute() to watch for "MySQL went away" errors
         *
         * @param String $sqlStatement
         * @param array  $bindParams
         * @param array  $bindTypes
         * @return boolean
         */
        public function execute($sqlStatement, $bindParams = null, $bindTypes = null)
        {}

        /**
         * Override of parent query() to watch for "MySQL went away" errors
         *
         * @param String $sqlStatement
         * @param array  $bindParams
         * @param array  $bindTypes
         * @return boolean
         */
        public function query($sqlStatement, $placeholders = null, $dataTypes = null)
        {}

        /**
         * Calling the parent execute method in PDO class
         *
         * @param String $sqlStatement
         * @param array  $bindParams
         * @param array  $bindTypes
         * @return boolean
         */
        protected function callParentExecute(string $sqlStatement, $placeholders = null, $dataTypes = null)
        {}

        /**
         * Calling the parent execute method in PDO class
         *
         * @param String $sqlStatement
         * @param array  $bindParams
         * @param array  $bindTypes
         * @return bool|\Phalcon\Db\ResultInterface
         */
        protected function callParentQuery(string $sqlStatement, array $bindParams, array $bindTypes)
        {}

    }

}

