<?php
namespace Bullhorn\FastRest\Api\Services\ControllerHelper;

use Phalcon\Http\Request;

class ShowCriteria {
    /** @var  Request */
    private $request;
    /** @var  Field */
    private $field;
    const SUB_FIELD_REGEX = '/(,(?<=,)(?=[^a-z])(.+?\((.*?)\)))((?(R),|\)*)|(?R))|(.(?<=^[A-Z])(.+?\((.*?)\)))((?(R),|\)*))/';

    /**
     * Constructor
     * @param Request $request
     * @param string $defaultFieldsValue
     */
    public function __construct(Request $request, $defaultFieldsValue = '*') {
        $this->setRequest($request);
        $this->buildFields($defaultFieldsValue);
    }

    /**
     * buildFields
     * @param string $defaultFieldsValue
     * @return void
     * @throws Request\Exception
     */
    private function buildFields($defaultFieldsValue) {
        $rawFields = $this->getRequest()->get('fields', null, $defaultFieldsValue);
        $entitiesWithFields = [];
        preg_match_all(ShowCriteria::SUB_FIELD_REGEX, $rawFields, $entitiesWithFields);
        $fields = explode(',', preg_replace(ShowCriteria::SUB_FIELD_REGEX, '', $rawFields));
        $this->filterSubEntityFields($entitiesWithFields[0], $fields);
        $helper = new SplitHelper('.');
        $keyedFields = array_fill_keys($fields, null);
        $this->setField(new Field($helper->convert($keyedFields)));
    }

    /**
     * Getter
     * @return Field
     */
    public function getField() {
        return $this->field;
    }

    /**
     * Setter
     * @param Field $field
     */
    private function setField(Field $field) {
        $this->field = $field;
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
     * filterSubEntityFields
     * @param array  $entitiesWithFields
     * @param array  $fields
     * @param string $entityPath
     * @return void
     */
    private function filterSubEntityFields(array $entitiesWithFields, array &$fields, string $entityPath = '') {
        foreach($entitiesWithFields as $entityAndFields) {
            $entity = preg_split('/\((.*?)\)((?(R),|\)*)|(?R))/', $entityAndFields)[0];
            $entity = trim($entity,',');
            if($entityPath !== '') {
                $entityPath .= '.'.$entity;
            } else {
                $entityPath = $entity;
            }
            $entityFields = [];
            preg_match_all('/\((.*?)\)((?(R),|\)*)|(?R))/', $entityAndFields, $entityFields);
            $rawEntityFields = substr($entityFields[0][0], 1, -1);
            $subEntityWithFields = [];
            if(strpos($rawEntityFields,'(') !== false) {
                preg_match_all(ShowCriteria::SUB_FIELD_REGEX, $rawEntityFields, $subEntityWithFields);
                $currentEntityFields = explode(',', preg_replace(ShowCriteria::SUB_FIELD_REGEX, '', $rawEntityFields));
            } else {
                $currentEntityFields = explode(',', $rawEntityFields);
            }
            foreach($currentEntityFields as $subField) {
                $fields[] = $entityPath . '.' . $subField;
            }
            foreach($subEntityWithFields as $subEntity) {
                if(count($subEntity) > 0 && $subEntity[0] !== '' && strpos($subEntity[0],'(') !== false) {
                    $this->filterSubEntityFields($subEntity, $fields, $entityPath);
                }
            }
        }
    }

}