<?php
namespace Bullhorn\FastRest\Api\Services\FileSystem;

class FileNotFoundException extends \Exception {
    /** @var  string */
    private $path;

    public function __construct($message = null, $code = 0, \Exception $previous = null, $path = null) {
        if (null === $message) {
            if (null === $path) {
                $message = 'File could not be found.';
            } else {
                $message = sprintf('File "%s" could not be found.', $path);
            }
        }
        $this->path = $path;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }


}