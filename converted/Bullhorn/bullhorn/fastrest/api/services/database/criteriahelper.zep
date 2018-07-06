namespace Bullhorn\FastRest\Api\Services\Database;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Resultset\Simple as ResultSet;
use Phalcon\Mvc\Model\Row;
class CriteriaHelper
{
    /** @var  Criteria */
    protected criteria;
    /** @var  string[] */
    protected groupBys = [];
    /** @var int */
    protected localParamCount = 0;
    /**
     * Constructor
     * @param Criteria $criteria
     */
    public function __construct(<Criteria> criteria) -> void
    {
        this->setCriteria(criteria);
    }

    /**
     * Get group bys
     * @return \string[]
     */
    public function getGroupBys() -> array
    {
        return this->groupBys;
    }

    /**
     * Sets the group bys
     * @param \string[] $groupBys
     */
    public function setGroupBys(array groupBys) -> void
    {
        let this->groupBys = groupBys;
    }

    /**
     * Increment the next local param count
     * @return int
     */
    protected function incrementLocalParamCount() -> int
    {
        var count;

        let count =  this->getLocalParamCount();
        let count++;
        this->setLocalParamCount(count);
        return count;
    }

    /**
     * Getter
     * @return int
     */
    protected function getLocalParamCount() -> int
    {
        return this->localParamCount;
    }

    /**
     * Setter
     * @param int $localParamCount
     */
    protected function setLocalParamCount(int localParamCount) -> void
    {
        let this->localParamCount = localParamCount;
    }

    /**
     * Getter
     * @return Criteria
     */
    public function getCriteria() -> <Criteria>
    {
        return this->criteria;
    }

    /**
     * Setter
     * @param Criteria $criteria
     */
    protected function setCriteria(<Criteria> criteria) -> void
    {
        let this->criteria = criteria;
    }

    /**
     * This gets the current joins
     * @return string[]
     */
    public function getJoins() -> array
    {
        array params =  (array)this->getCriteria()->getParams();
        if array_key_exists("joins", params) {
            return params["joins"];
        } else {
            return [];
        }
    }

    /**
     * Gets the next param id that can be used to make sure it is unique
     * @return int
     */
    public function getParamId() -> int
    {
        array params =  (array)this->getCriteria()->getParams();
        if array_key_exists("bind", params) {
            return sizeOf(params["bind"]);
        } else {
            return 0;
        }
    }

    /**
     * Appends a condition to the current conditions using an AND operator
     *
     * @param string $conditions
     * @param array $bindParams
     * @param array $bindTypes
     *
     * @return $this
     */
    public function andWhere(string conditions, array bindParams = null, array bindTypes = null)
    {
        if !(is_null(bindParams)) {
            var key, value, param;
            array tmpBindParams =  [];
            for key, value in bindParams {
                if is_int(key) {
                    let param =  "criteriaHelper" . this->incrementLocalParamCount();
                    let tmpBindParams[param] = value;
                    let conditions =  str_replace("?" . key, ":" . param . ":", conditions);
                } else {
                    let tmpBindParams[key] = value;
                }
            }
            let bindParams = tmpBindParams;
        }
        this->getCriteria()->andWhere(conditions, bindParams, bindTypes);
        return this;
    }

    /**
     * Executes a find using the parameters built with the criteria
     * @return ResultSet|Row[]
     */
    public function execute()
    {
        var modelName, params;

        let modelName =  this->getCriteria()->getModelName();
        let params =  this->getCriteria()->getParams();
        if !(empty(this->getGroupBys())) {
            let params["group"] =  this->getGroupBys();
        }
        return {modelName}::find(params);
    }

    /**
     * Converts an array into
     *
     * @param array $list if this is an array of objects that have the getId method, it uses those ids instead
     * @param array &$params
     * @param int $currentParamCount
     *
     * @return string sql
     */
    public function listToIn(array list, params, int currentParamCount) -> string
    {
        var sql, first, key, value;

        let currentParamCount += sizeOf(params);
        let sql = "";
        let first =  true;
        for key, value in list {
            if is_object(value) && method_exists(value, "getId") {
                let value =  value->getId();
            }
            if first {
                let first =  false;
            } else {
                let sql .= ",";
            }
            let sql .= "?" . currentParamCount;
            let params[currentParamCount] = value;
            let currentParamCount++;
        }
        return sql;
    }

    /**
     * Adds a new parameter
     *
     * @param string $value
     * @param array &$params
     * @param int $currentParamCount
     *
     * @return string
     */
    public function addParam(string value, params, int currentParamCount) -> string
    {
        var count;

        let count =  currentParamCount + sizeOf(params);
        let params[] = value;
        return "?" . count;
    }

}