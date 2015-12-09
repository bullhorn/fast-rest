<?php
namespace Bullhorn\FastRest\Api\Services\DataTransform;

use Bullhorn\FastRest\Api\Models\ControllerModelInterface;
use Bullhorn\FastRest\Api\Services\ControllerHelper\Params;
use Bullhorn\FastRest\Base as ServiceBase;

abstract class Base extends ServiceBase {
    /** @var  Params */
    private $params;

    /**
     * Constructor
     * @param Params $params
     */
    public function __construct(Params $params) {
        $this->setParams($params);
    }

    /**
     * Getter
     * @return Params
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Setter
     * @param Params $params
     */
    protected function setParams(Params $params) {
        $this->params = $params;
    }

    /**
     * Transforms the params
     * @param ControllerModelInterface $model
     * @return void
     */
    abstract public function transform($model);
}