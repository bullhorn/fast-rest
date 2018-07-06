<?php
namespace Bullhorn\FastRest\Api\Services\FileSystem;
use Bullhorn\FastRest\DependencyInjection;
use Bullhorn\FastRest\DependencyInjectionHelper;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;

class File implements InjectionAwareInterface {
    /** @var  string */
    private $path;

    /**
     * File constructor.
     * @param string $path
     */
    public function __construct($path) {
        $this->setPath($path);
    }

    public function getDi() {
        return DependencyInjectionHelper::getDi();
    }

    public function setDi(DiInterface $di) {
        DependencyInjectionHelper::setDi($di);
    }

    /**
     * Getter
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Setter
     * @param string $path
     * @return File
     */
    private function setPath($path) {
        $this->path = $path;
        return $this;
    }

    /**
     * hash
     * @param string $algorithm Name of selected hashing algorithm (i.e. "md5", "sha256", "haval160,4", etc..)
     * @return string
     */
    public function getHash($algorithm) {
        return hash_file($algorithm, $this->getPath());
    }

    /**
     * scanDir
     * @return string[]
     */
    public function scanDir() {
        return scandir($this->getPath());
    }

    /**
     * __toString
     * @return string
     */
    public function __toString() {
        return $this->getPath();
    }

    /**
     * exists
     * @return bool
     */
    public function exists() {
        return file_exists($this->getPath());
    }

    /**
     * getContents
     * @return string
     */
    public function getContents() {
        return file_get_contents($this->getPath());
    }

    /**
     * realPath
     * @return string
     */
    public function getRealPath() {
        return realpath($this->getPath());
    }

    /**
     * isDir
     * @return bool
     */
    public function isDir() {
        return is_dir($this->getPath());
    }

    /**
     * unlink
     * @return void
     */
    public function unlink() {
        unlink($this->getPath());
    }
}