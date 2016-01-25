<?php
namespace Bullhorn\FastRest\Api\Services\Output;

use \Phalcon\Http\ResponseInterface;

interface OutputInterface {
    const DI_NAME = 'Output';

    /**
     * This takes in object, and outputs it in the respective format, including sending the headers
     *
     * @param \stdClass $object
     * @param ResponseInterface $response
     *
     * @return void
     */
    public function output(\stdClass $object, ResponseInterface $response);
}
