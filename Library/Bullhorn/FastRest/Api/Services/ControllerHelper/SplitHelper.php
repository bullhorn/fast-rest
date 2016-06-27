<?php
namespace Bullhorn\FastRest\Api\Services\ControllerHelper;

use Phalcon\Http\Request\Exception;

class SplitHelper {
    /** @var  string */
    private $delimiter;

    /**
     * Constructor
     * @param string $delimiter
     */
    public function __construct($delimiter) {
        $this->setDelimiter($delimiter);
    }

    /**
     * Getter
     * @return string
     */
    private function getDelimiter() {
        return $this->delimiter;
    }

    /**
     * Setter
     * @param string $delimiter
     */
    private function setDelimiter($delimiter) {
        $this->delimiter = $delimiter;
    }

    /**
     * Converts from an array of user.branch.firstName=>abc, to standard objects
     * {
     *    user:{
     *        branch:{
     *            firstName:abc
     *        }
     *    }
     * }
     *
     * @param string[] $input
     *
     * @return \stdClass
     * @throws Exception
     */
    public function convert(array $input) {
        //Convert to objects
        $returnVar = new \stdClass();
        foreach($input as $name => $value) {
            $parts = explode($this->getDelimiter(), $name);
            $currentPart = $returnVar;
            $count = sizeOf($parts);
            foreach($parts as $key => $part) {
                //Ignore all that have a blank part
                if($part == '') {
                    break;
                }
                if($key + 1 == $count) {
                    $currentPart->{$part} = $value;
                } else {
                    $part = ucfirst($part);
                    if(!isset($currentPart->{$part})) {
                        $item = new \stdClass();
                        $currentPart->{$part} = $item;
                    } else {
                        //Account for the url of ?employeeType&employeeType.name=1099 with employeeType and employeeType.name
                        if(!is_object($currentPart->{$part})) {
                            throw new Exception('You cannot pass in both ' . $part . ', and ' . implode('.', $parts), 400);
                        }
                    }
                    $currentPart = $currentPart->{$part};
                }
            }
        }
        return $returnVar;
    }


}