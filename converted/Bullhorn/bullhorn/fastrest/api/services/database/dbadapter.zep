namespace Bullhorn\FastRest\Api\Services\Database;

use Phalcon\Db;
use Phalcon\Db\Adapter\Pdo\Mysql as ParentDbAdapter;
class DbAdapter extends ParentDbAdapter
{
    const MYSQL_WENT_AWAY_CODE = "HY000";
    /**
     * Override of parent execute() to watch for "MySQL went away" errors
     *
     * @param String $sqlStatement
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return boolean
     */
    public function execute(string! sqlStatement, var bindParams = null, var bindTypes = null) -> boolean
    {
        var e;

        try {
            return this->callParentExecute(sqlStatement, bindParams, bindTypes);
        } catch \PDOException, e {
            if e->getCode() === self::MYSQL_WENT_AWAY_CODE || e->getMessage() === "SQLSTATE[HY000]: General error: 2006 MySQL server has gone away" {
                this->connect(this->_descriptor);
                return this->callParentExecute(sqlStatement, bindParams, bindTypes);
            } else {
                throw e;
            }
        }
    }

    /**
     * Override of parent query() to watch for "MySQL went away" errors
     *
     * @param String $sqlStatement
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return boolean
     */
    public function query(string! sqlStatement, var bindParams = null, var bindTypes = null) -> <ResultInterface> | boolean
    {
        var results, e;

        try {
            let results =  this->callParentQuery(sqlStatement, bindParams, bindTypes);
            results->setFetchMode(Db::FETCH_ASSOC);
            return results;
        } catch \PDOException, e {
            if e->getCode() === self::MYSQL_WENT_AWAY_CODE || e->getMessage() === "SQLSTATE[HY000]: General error: 2006 MySQL server has gone away" {
                this->connect(this->_descriptor);
                return this->callParentQuery(sqlStatement, bindParams, bindTypes);
            } else {
                throw e;
            }
        }
        return false;
    }

    /**
     * Calling the parent execute method in PDO class
     *
     * @param String $sqlStatement
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return boolean
     */
    protected function callParentExecute(string! sqlStatement, var bindParams = null, var bindTypes = null) -> boolean
    {
        return parent::execute(sqlStatement, bindParams, bindTypes);
    }

    /**
     * Calling the parent execute method in PDO class
     *
     * @param String $sqlStatement
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return bool|\Phalcon\Db\ResultInterface
     */
    protected function callParentQuery(string sqlStatement, array bindParams, array bindTypes)
    {
        return parent::query(sqlStatement, bindParams, bindTypes);
    }

}