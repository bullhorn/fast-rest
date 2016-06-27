<?php
namespace Bullhorn\FastRest\Api\Services\ControllerHelper;

use Bullhorn\FastRest\Api\Services\Database\CriteriaHelper;
use Phalcon\Mvc\Model\Resultset\Simple as ResultSet;
use Phalcon\Http\Request;
use Bullhorn\FastRest\Api\Models\ApiInterface;
use Bullhorn\FastRest\Api\Models\ControllerModelInterface;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Request\Exception;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

class Index extends Base {
    /** @var  Request */
    private $request;
    /** @var  ControllerModelInterface */
    private $entityFactory;
    /** @var  Criteria */
    private $criteria;
    /** @var  int */
    private $count;
    /** @var  ResultSet */
    private $resultSet;
    /** @var  CriteriaHelper */
    private $criteriaHelper;
    /** @var string[] Fields that are always allowed */
    private $whiteList = ['fields', 'start', 'count', 'sort'];
    /** @var  IndexCriteria */
    private $indexCriteria;

    /**
     * Constructor
     * @param Request $request
     * @param ControllerModelInterface $entityFactory
     * @param string[] $whiteList The list of fields that are always allowed
     */
    public function __construct(Request $request, ControllerModelInterface $entityFactory, array $whiteList = []) {
        $this->addToWhiteList($whiteList);
        $this->setRequest($request);
        $this->setIndexCriteria(new IndexCriteria($this->getRequest()));
        $this->setEntityFactory($entityFactory);
        $this->setCriteria($this->getEntityFactory()->query());
        $this->setCriteriaHelper(new CriteriaHelper($this->getCriteria()));
        $entityFactory->buildListCriteria($this->getCriteriaHelper());
        $this->buildSearchCriteria();
        $this->buildSortParameter();
        $this->buildGroupBy();
        /** @var ResultSet $countResultSet */
        $countResultSet = $this->getCriteriaHelper()->execute();
        $this->setCount($countResultSet->count());
        $this->buildLimit();
        $this->setResultSet($this->getCriteriaHelper()->execute());
    }

    /**
     * @return IndexCriteria
     */
    private function getIndexCriteria() {
        return $this->indexCriteria;
    }

    /**
     * @param IndexCriteria $indexCriteria
     */
    private function setIndexCriteria(IndexCriteria $indexCriteria) {
        $this->indexCriteria = $indexCriteria;
    }


    /**
     * Gets the whitelisted list of fields always allowed
     * @return \string[]
     */
    private function getWhiteList() {
        return $this->whiteList;
    }

    /**
     * Sets the whitelisted list of fields always allowed
     * @param \string[] $whiteList
     * @throws \Exception
     */
    private function setWhiteList(array $whiteList) {
        $this->whiteList = $whiteList;
    }

    /**
     * Add Fields to the white list so they are ignored
     *
     * @param array $whiteList
     *
     * @return void
     */
    private function addToWhiteList(array $whiteList) {
        $current = $this->getWhiteList();
        $current = array_merge($current, $whiteList);
        $this->setWhiteList($current);
    }

    /**
     * Adds the group by to make sure that only one result is returned for each main entity
     * @return void
     */
    private function buildGroupBy() {
        $this->getCriteriaHelper()->setGroupBys([get_class($this->getEntityFactory()) . '.id']);
    }

    /**
     * Getter
     * @return CriteriaHelper
     */
    protected function getCriteriaHelper() {
        return $this->criteriaHelper;
    }

    /**
     * Setter
     * @param CriteriaHelper $criteriaHelper
     */
    private function setCriteriaHelper($criteriaHelper) {
        $this->criteriaHelper = $criteriaHelper;
    }

    /**
     * Getter
     * @return Criteria
     */
    protected function getCriteria() {
        return $this->criteria;
    }

    /**
     * Setter
     * @param Criteria $criteria
     */
    private function setCriteria(Criteria $criteria) {
        $this->criteria = $criteria;
    }

    /**
     * Getter
     * @return ControllerModelInterface
     */
    private function getEntityFactory() {
        return $this->entityFactory;
    }

    /**
     * Setter
     * @param ControllerModelInterface $entityFactory
     */
    private function setEntityFactory(ControllerModelInterface $entityFactory) {
        $this->entityFactory = $entityFactory;
    }


    /**
     * Getter
     * @return Request
     */
    private function getRequest() {
        return $this->request;
    }

    /**
     * Setter
     * @param Request $request
     */
    private function setRequest(Request $request) {
        $this->request = $request;
    }

    /**
     * Builds the search criteria
     * @return void
     */
    private function buildSearchCriteria() {
        $this->buildSearchFieldsRecursive($this->getIndexCriteria()->getSearch(), $this->getEntityFactory());
    }

    /**
     * Builds the search fields
     *
     * @param Search $search
     * @param ApiInterface $entity
     *
     * @return void
     * @throws Exception
     */
    private function buildSearchFieldsRecursive(Search $search, ApiInterface $entity) {
        $isRoot = false;
        $alias = $search->getAlias();
        if(is_null($alias)) {
            $isRoot = true;
            $alias = get_class($entity);
        }

        foreach($search->getFields() as $field => $value) {
            if(in_array(preg_replace("/[<|>]$/", "", $field), $entity->getModelsMetaData()->getColumnMap($entity))) {
                $this->addSearchField($field, $value, $entity, $alias);
            } else {
                if(!$isRoot || !in_array($field, $this->getWhiteList())) {
                    throw new Exception('Could not find the field: ' . ($isRoot ? '' : $alias . '.') . $field, 400);
                }
            }
        }
        foreach($search->getChildren() as $child) {
            if(in_array($child->getAlias(), $entity->getParentRelationships())) {
                $subEntity = $this->addJoin($entity, $child->getAlias(), false);
                $this->buildSearchFieldsRecursive($child, $subEntity);
            } elseif(in_array($child->getAlias(), $entity->getChildrenRelationships())) {
                throw new Exception('Cannot search on children: ' . ($isRoot ? '' : $alias . '.') . $child->getAlias(), 400);
            } else {
                throw new Exception('Could not find the parent: ' . ($isRoot ? '' : $alias . '.') . $child->getAlias(), 400);
            }
        }

        $this->addSoftDelete($entity, $alias);
    }

    /**
     * Add soft delete to the where clause if the entity supports it
     *
     * @param ApiInterface $entity
     * @param string $alias
     *
     * @return void
     */
    public function addSoftDelete(ApiInterface $entity, $alias) {
        $reflectionClass = new \ReflectionClass(SoftDelete::class);
        $reflectionMethod = $reflectionClass->getMethod("getOptions");
        $reflectionMethod->setAccessible(true);

        /** @var SoftDelete[] $softDeleteBehaviors */
        $softDeleteBehaviors = $entity->getAllBehaviorsByClassName(SoftDelete::class);

        foreach($softDeleteBehaviors as $softDeleteBehavior) {
            $options = $reflectionMethod->invoke($softDeleteBehavior);
            $sql = $alias . '.' . $options["field"] . '!=?0';
            $params = [$options["value"]];
            $this->getCriteriaHelper()->andWhere($sql, $params);
        }
    }

    /**
     * Adds a specific field
     *
     * @param string $name
     * @param string $value
     * @param ApiInterface $entity
     * @param string $alias
     *
     * @return void
     */
    private function addSearchField($name, $value, ApiInterface $entity, $alias) {
        $operator = "=";
        if(preg_match("/[<|>]$/", $name)) {
            $operator = substr($name, -1) . "=";
            $name = substr($name, 0, -1);

            $fieldTypes = $entity->getFieldTypes();
            $fieldTypesWhiteList = [ApiInterface::FIELD_TYPE_DATE, ApiInterface::FIELD_TYPE_DATE_TIME, ApiInterface::FIELD_TYPE_DOUBLE, ApiInterface::FIELD_TYPE_INT];
            if(!in_array($fieldTypes[$name], $fieldTypesWhiteList)) {
                throw new Exception(
                    'Cannot perform ' . $operator . ' search on any fields that are not in ' . implode(", ", $fieldTypesWhiteList) . ': ' . $name . ' has a type of ' . $fieldTypes[$name],
                    400
                );
            }
        }

        $sql = $alias . '.' . $name;
        if(is_array($value)) {
            $attributes = [];
            foreach($value as $subValue) {
                $entity->writeAttribute($name, $subValue);
                $attributes[] = $entity->readAttribute($name);
            }
            $this->getCriteria()->inWhere($sql, $attributes);
        } else {
            $entity->writeAttribute($name, $value);
            $attribute = $entity->readAttribute($name);
            $params = [];


            if (is_null($attribute)) {
                $sql .= " IS NULL";
            } else {
                $attribute .= ''; //Make sure to convert to string, for Date and DateTime
                if (preg_match('@(^|[^\\\])%@', $attribute)) {
                    $operator = ' LIKE ';
                }
                $sql .= $operator . '?0';
                $params[] = $attribute;
            }

            $this->getCriteriaHelper()->andWhere($sql, $params);
        }
    }

    /**
     * Adds a join
     *
     * @param ApiInterface $entity
     * @param string $alias
     * @param bool $forceFind
     *
     * @return ApiInterface|false False if sub part was not found
     * @throws Exception
     */
    private function addJoin(ApiInterface $entity, $alias, $forceFind) {
        if(in_array($alias, $entity->getParentRelationships())) {
            $referencedModel = $entity->addJoin($this->getCriteriaHelper(), $alias);
            return new $referencedModel();
        } else {
            if($forceFind) {
                throw new Exception('Could Not Find Part in current entity: ' . $alias, 400);
            }
            return false;
        }
    }


    /**
     * Gets sort parameters from the URL.
     *
     * @return void
     */
    protected function buildSortParameter() {
        $sorts = $this->getIndexCriteria()->getSorts();
        if(!empty($sorts)) {
            $orderBy = '';
            foreach($sorts as $sort) {
                $this->buildSortRecursive($this->getEntityFactory(), $sort->getFields(), $sort->isAsc(), $orderBy);
            }
            $this->getCriteria()->orderBy($orderBy);
        }
    }

    /**
     * Builds the order by
     *
     * @param ApiInterface $entity
     * @param string[] $parts
     * @param bool $isAsc
     * @param string &$orderBy
     * @param string $alias
     *
     * @return void
     * @throws Exception
     */
    private function buildSortRecursive(ApiInterface $entity, $parts, $isAsc, &$orderBy, $alias = null) {
        if(is_null($alias)) {
            $alias = get_class($entity);
        }
        $part = array_shift($parts);
        if(sizeOf($parts) == 0) { //Is Last part
            if(in_array($part, $entity->getModelsMetaData()->getColumnMap($entity))) {
                if($orderBy != '') {
                    $orderBy .= ',';
                }
                $orderBy .= $alias . '.' . $part . ' ' . ($isAsc ? 'ASC' : 'DESC');
            } else {
                throw new Exception('Could Not Find sort part: ' . $part, 400);
            }
        } else {
            $subAlias = ucfirst($part);
            $subEntity = $this->addJoin($entity, $subAlias, true); //Throws an exception if not found
            $this->buildSortRecursive($subEntity, $parts, $isAsc, $orderBy, $subAlias);
        }
    }

    /**
     * Builds the limit query
     * @return void
     */
    private function buildLimit() {
        $this->getCriteria()->limit($this->getIndexCriteria()->getLimit(), $this->getIndexCriteria()->getOffset());
    }

    /**
     * Getter
     * @return ResultSet
     */
    public function getResultSet() {
        return $this->resultSet;
    }

    /**
     * Setter
     * @param ResultSet $resultSet
     */
    private function setResultSet($resultSet) {
        $this->resultSet = $resultSet;
    }

    /**
     * Getter
     * @return int
     */
    private function getCount() {
        return $this->count;
    }

    /**
     * Gets the url
     *
     * @param string[] $queryParams
     *
     * @return string
     */
    private function generateUrl($queryParams) {
        $params = $this->getRequest()->getQuery();
        //Remove htaccess mod rewrite query
        if(array_key_exists('_url', $params)) {
            unset($params['_url']);
        }
        //Override with the new parameters
        foreach($queryParams as $name => $value) {
            $params[$name] = $value;
        }
        $url = new Url();
        return 'https://' . $this->getRequest()->getHttpHost() . substr($url->get($this->getRequest()->getQuery('_url'), $params), 1);
    }

    /**
     * Generates the links for the link header
     * @return string
     */
    public function generateLinks() {
        return '<' . $this->generateUrl($this->getNextPageQueryCriteria()) . '>; rel="next",'
        . '<' . $this->generateUrl($this->getLastPageQueryCriteria()) . '>; rel="last",'
        . '<' . $this->generateUrl($this->getFirstPageQueryCriteria()) . '>; rel="first",'
        . '<' . $this->generateUrl($this->getPreviousPageQueryCriteria()) . '>; rel="prev"';
    }

    /**
     * Gets the query params for the first page
     * @return string[]
     */
    private function getFirstPageQueryCriteria() {
        return array(
            'start' => 0,
            'count' => $this->getIndexCriteria()->getLimit()
        );
    }

    /**
     * Gets the query params for the first page
     * @return string[]
     */
    private function getPreviousPageQueryCriteria() {
        $start = $this->getIndexCriteria()->getOffset() - $this->getIndexCriteria()->getLimit();
        if($start < 0) {
            $start = 0;
        }
        return array(
            'start' => $start,
            'count' => $this->getIndexCriteria()->getLimit()
        );
    }

    /**
     * Gets the query params for the first page
     * @return string[]
     */
    private function getNextPageQueryCriteria() {
        $start = $this->getIndexCriteria()->getOffset() + $this->getIndexCriteria()->getLimit();
        return array(
            'start' => $start,
            'count' => $this->getIndexCriteria()->getLimit()
        );
    }

    /**
     * Gets the query params for the first page
     * @return string[]
     */
    private function getLastPageQueryCriteria() {
        $start = $this->getCount() - $this->getIndexCriteria()->getLimit();
        if($start < 0) {
            $start = 0;
        }
        return array(
            'start' => $start,
            'count' => $this->getIndexCriteria()->getLimit()
        );
    }

    /**
     * Setter
     * @param int $count
     */
    private function setCount($count) {
        $this->count = $count;
    }


}