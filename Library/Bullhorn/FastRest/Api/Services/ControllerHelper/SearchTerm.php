<?php
namespace Bullhorn\FastRest\Api\Services\ControllerHelper;
class SearchTerm {
    /** @var string[] */
    private $searchFields;
    /** @var string */
    private $term;

    /**
     * SearchTerm constructor.
     * @param string[] $searchFields
     * @param string $term
     */
    public function __construct(array $searchFields, string $term) {
        $this->setSearchFields($searchFields);
        $this->setTerm($term);
    }

    /**
     * SearchFields
     * @return string[]
     */
    public function getSearchFields(): array {
        return $this->searchFields;
    }

    /**
     * SearchFields
     * @param string[] $searchFields
     * @return SearchTerm
     */
    private function setSearchFields(array $searchFields): SearchTerm {
        $this->searchFields = $searchFields;
        return $this;
    }

    /**
     * Term
     * @return string
     */
    public function getTerm(): string {
        return $this->term;
    }

    /**
     * Term
     * @param string $term
     * @return SearchTerm
     */
    private function setTerm(string $term): SearchTerm {
        $this->term = $term;
        return $this;
    }


}