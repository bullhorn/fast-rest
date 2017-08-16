<?php
namespace Bullhorn\FastRest\Api\Services\DataTransform;

use Bullhorn\FastRest\Api\Models\ControllerModelInterface;
use Bullhorn\FastRest\Api\Services\ControllerHelper\ParamNotFoundException;
use Bullhorn\FastRest\Api\Services\Filter;
use Bullhorn\FastRest\Base as ServiceBase;
use stdClass;

abstract class Base extends ServiceBase {
    /** @var  stdClass */
    private $params;

    /**
     * Constructor
     * @param stdClass $params
     */
    public function __construct(stdClass $params) {
        $this->setParams($params);
    }

    /**
     * getParam
     *
     * @param string       $name
     * @param string|array $filters
     *
     * @return mixed
     * @throws ParamNotFoundException
     */
    public function getParam($name, $filters = null) {
        $parts = explode('.', $name);
        $currentObject = $this->getParams();
        foreach($parts as $key=>$part) {
            if(!property_exists($currentObject, $part)) {
                throw new ParamNotFoundException('Param Not Found: ' . $name);
            }
            $currentObject = $currentObject->$part;
        }
        $value = $currentObject;
        if(!is_null($filters)) {
            $filter = new Filter();
            $value = $filter->sanitize($value, $filters);
        }
        return $value;
    }

    /**
     * setParam
     * @param string $name
     * @param mixed  $value
     * @return void
     */
    public function setParam($name, $value) {
        $parts = explode('.', $name);
        $currentObject = $this->getParams();
        foreach($parts as $key=>$part) {
            if($key+1==count($parts)) {
                $currentObject->$part = $value;
            } else {
                if (!property_exists($currentObject, $part)) {
                    $currentObject->$part = new stdClass();
                }
                $currentObject = $currentObject->$part;
            }
        }
    }

    /**
     * removeParam
     * @param string $name
     * @return void
     */
    public function removeParam($name) {
        $parts = explode('.', $name);
        $currentObject = $this->getParams();
        foreach($parts as $key=>$part) {
            if (!property_exists($currentObject, $part)) {
                return; //Property already does not exist
            }
            if($key+1==count($parts)) {
                unset($currentObject->$part);
            } else {
                $currentObject = $currentObject->$part;
            }
        }
    }

    /**
     * hasParam
     * @param string $name
     * @return bool
     */
    protected function hasParam($name) {
        try {
            $this->getParam($name);
            return true;
        } catch(ParamNotFoundException $e) {
            return false;
        }
    }

    /**
     * Getter
     * @return stdClass
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Setter
     * @param stdClass $params
     */
    protected function setParams(stdClass $params) {
        $this->params = $params;
    }

    /**
     * Transforms the params
     * @param ControllerModelInterface $model
     * @return void
     */
    abstract public function transform($model);
}