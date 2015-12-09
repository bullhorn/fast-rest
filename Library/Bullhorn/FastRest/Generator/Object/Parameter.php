<?php
namespace Bullhorn\FastRest\Generator\Object;

class Parameter {
    /** @var  string */
    private $name;
    /** @var  string */
    private $description;
    /** @var  string */
    private $type;
    /** @var  string */
    private $defaultValue = null;
    /** @var  bool */
    private $class;
    /** @var  bool */
    private $strictType;
    /** @type  string */
    private $strictClass;

    /**
     * toDocTypeString
     * @param Method $method
     * @return string
     */
    public function toDocTypeString(Method $method) {
        $longestType = 0;
        $longestName = 0;
        foreach($method->getParameters() as $parameter) {
            if(strlen($parameter->getType()) > $longestType) {
                $longestType = strlen($parameter->getType());
            }
            if(strlen($parameter->getName()) > $longestName) {
                $longestName = strlen($parameter->getName());
            }
        }
        $returnVar = '	 * @param';
        $returnVar .= ' ' . $this->getType() . (str_repeat(' ', $longestType - strlen($this->getType())));
        $returnVar .= ' $' . $this->getName();
        if(strlen($this->getDescription()) > 0) {
            $returnVar .= str_repeat(' ', $longestName - strlen($this->getName())) . ' ' . $this->getDescription();
        }
        $returnVar .= "\n";
        return $returnVar;
    }

    /**
     * String
     * @return string
     */
    public function getStrictClass() {
        return $this->strictClass;
    }

    /**
     * String
     * @param string $strictClass
     */
    public function setStrictClass($strictClass) {
        $this->setStrictType(true);
        $this->strictClass = $strictClass;
    }


    /**
     * Getter
     * @return boolean
     */
    public function isStrictType() {
        return $this->strictType;
    }

    /**
     * Setter
     * @param boolean $strictType
     */
    public function setStrictType($strictType) {
        $this->strictType = $strictType;
    }


    /**
     * Getter
     * @return string
     */
    public function getDefaultValue() {
        return $this->defaultValue;
    }

    /**
     * Setter
     * @param string $defaultValue
     *
     * @return $this
     */
    public function setDefaultValue($defaultValue) {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * Getter
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Setter
     * @param string $name
     *
     * @return $this;
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * Getter
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Is this type a class
     * @return boolean
     */
    public function isClass() {
        return $this->class;
    }

    /**
     * Setter
     * @param boolean $class
     */
    public function setClass($class) {
        $this->class = $class;
    }


    /**
     * Setter
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * Getter
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Setter
     * @param string $type
     *
     * @return $this
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }


}