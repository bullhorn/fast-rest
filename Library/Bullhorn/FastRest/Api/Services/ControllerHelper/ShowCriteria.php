<?php
namespace Bullhorn\FastRest\Api\Services\ControllerHelper;

use Phalcon\Http\Request;

class ShowCriteria {
    /** @var  Request */
    private $request;
    /** @var  Field */
    private $field;

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
        $fields = preg_split('/,(?![^\(\]]*\))/', $rawFields);
        $entityAndFields = preg_grep('/.+?\((.*?)\)/', $fields);
        $fields = array_diff($fields, $entityAndFields);
        foreach($entityAndFields as $entityAndField) {
            $entity = preg_split('/\((.*?)\)/', $entityAndField)[0];
            $entityFields = [];
            preg_match('/\((.*?)\)/', $entityAndField, $entityFields);
            foreach(explode(',', $entityFields[0]) as $subField) {
                $subField = str_replace(['(',')'], '', $subField);
                array_push($fields, $entity.'.'.$subField);
            }
        }
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


}