<?php
namespace Bullhorn\FastRest\Api\Services\Database
{
    use Phalcon\Mvc\Model\Criteria;
    use Phalcon\Mvc\Model\Resultset\Simple as ResultSet;
    use Phalcon\Mvc\Model\Row;

    class CriteriaHelperRoot 
    {
        /**
         * @var  Criteria *
         */
        protected $criteria;

        /**
         * @var  string[] *
         */
        protected $groupBys;

        /**
         * @var int *
         */
        protected $localParamCount;

        /**
         * Constructor
         *
         * @param Criteria $criteria
         */
        public function __construct($criteria)
        {}

        /**
         * Get group bys
         *
         * @return \string[]
         */
        public function getGroupBys()
        {}

        /**
         * Sets the group bys
         *
         * @param \string[] $groupBys
         */
        public function setGroupBys(array $groupBys)
        {}

        /**
         * Increment the next local param count
         *
         * @return int
         */
        protected function incrementLocalParamCount()
        {}

        /**
         * Getter
         *
         * @return int
         */
        protected function getLocalParamCount()
        {}

        /**
         * Setter
         *
         * @param int $localParamCount
         */
        protected function setLocalParamCount(int $localParamCount)
        {}

        /**
         * Getter
         *
         * @return Criteria
         */
        public function getCriteria()
        {}

        /**
         * Setter
         *
         * @param Criteria $criteria
         */
        protected function setCriteria($criteria)
        {}

        /**
         * This gets the current joins
         *
         * @return string[]
         */
        public function getJoins()
        {}

        /**
         * Gets the next param id that can be used to make sure it is unique
         *
         * @return int
         */
        public function getParamId()
        {}

        /**
         * Appends a condition to the current conditions using an AND operator
         *
         * @param string $conditions
         * @param array $bindParams
         * @param array $bindTypes
         * @return $this
         */
        public function andWhere(string $conditions, array $bindParams = null, array $bindTypes = null)
        {}

        /**
         * Executes a find using the parameters built with the criteria
         *
         * @return ResultSet|Row[]
         */
        public function execute()
        {}

    }

}

