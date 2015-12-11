<?php
namespace Bullhorn\FastRest\Api\Services\Output;

use \Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;

class Json implements OutputInterface {
    /**
     * This takes in object, and outputs it in the respective format, including sending the headers
     *
     * @param \stdClass $object
     * @param ResponseInterface $response
     *
     * @return void
     */
    public function output(\stdClass $object, ResponseInterface $response) {
        $response->setContentType('application/json');
        $response->setJsonContent($object);
        $response->send();
    }
}
