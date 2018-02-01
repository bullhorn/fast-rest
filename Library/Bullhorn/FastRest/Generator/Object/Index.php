<?php
namespace Bullhorn\FastRest\Generator\Object;

use Bullhorn\FastRest\Api\Services\Exception\CatchableException;
use Bullhorn\FastRest\Generator\Configuration;

class Index {
    /** @var  Method[] */
    private $methods = array();
    /** @var Variable[] */
    private $variables = array();
    /** @var string */
    private $namespace;
    /** @var string[] */
    private $uses = array();
    /** @var string */
    private $name;
    /** @var  string */
    private $extends;
    /** @var  bool */
    private $abstract;
    /** @var  string[] */
    private $constants = array();
    /** @var string */
    private $documentation;
    /** @var  string[] */
    private $implements = array();
    /** @var string[] */
    private $traits = array();
    /** @var  Configuration */
    private $configuration;

    public function __construct(Configuration $configuration) {
        $this->setConfiguration($configuration);
    }

    /**
     * Getter
     * @return Configuration
     */
    private function getConfiguration() {
        return $this->configuration;
    }

    /**
     * Setter
     * @param Configuration $configuration
     */
    private function setConfiguration(Configuration $configuration) {
        $this->configuration = $configuration;
    }


    /**
     * Getter
     * @return \string[]
     */
    public function getImplements() {
        return $this->implements;
    }

    /**
     * Setter
     * @param \string[] $implements
     */
    public function setImplements(array $implements) {
        $this->implements = $implements;
    }

    /**
     * Getter
     * @return \string[]
     */
    public function getTraits() {
        return $this->traits;
    }

    /**
     * Setter
     * @param \string[] $traits
     */
    public function setTraits(array $traits) {
        $this->traits = $traits;
    }


    /**
     * Getter
     * @return string
     */
    public function getDocumentation() {
        return $this->documentation;
    }

    /**
     * Setter
     * @param string $documentation
     */
    public function setDocumentation($documentation) {
        $this->documentation = $documentation;
    }


    /**
     * Getter
     * @return \string[]
     */
    public function getConstants() {
        return $this->constants;
    }

    /**
     * Setter
     * @param \string[] $constants
     */
    private function setConstants(array $constants) {
        $this->constants = $constants;
    }

    /**
     * Adds a new class constant
     *
     * @param string $name
     * @param string $value
     *
     * @return void
     * @throws \Exception
     */
    public function addConstant($name, $value) {
        $constants = $this->getConstants();
        if(array_key_exists($name, $constants)) {
            throw new \Exception('Constant Already Exists: ' . $name);
        }
        $constants[$name] = $value;
        asort($constants);
        $this->setConstants($constants);
    }

    /**
     * Checks if the constant exists
     *
     * @param string $name The name of the constant you are looking for
     *
     * @return bool
     */
    public function hasConstant($name) {
        $hasConstant = false;
        if(array_key_exists($name, $this->getConstants())) {
            $hasConstant = true;
        }

        return $hasConstant;
    }

    /**
     * Getter
     * @return boolean
     */
    public function isAbstract() {
        return $this->abstract;
    }

    /**
     * Setter
     * @param boolean $abstract
     */
    public function setAbstract($abstract) {
        $this->abstract = $abstract;
    }


    /**
     * Getter
     * @return string
     */
    public function getExtends() {
        return $this->extends;
    }

    /**
     * Setter
     * @param string $extends
     */
    public function setExtends($extends) {
        $this->extends = $extends;
    }


    /**
     * Getter
     * @return Method[]
     */
    public function getMethods() {
        return $this->methods;
    }

    /**
     * Setter
     * @param Method[] $methods
     */
    private function setMethods(array $methods) {
        $this->methods = $methods;
    }

    /**
     * Checks whether the method name already exists on the class
     *
     * @param string $methodName The name to be checked
     *
     * @return bool
     */
    public function hasMethod($methodName) {
        return array_key_exists($methodName, $this->methods);
    }

    /**
     * Adds a new method
     *
     * @param Method  $method
     * @param boolean $override
     * @return void
     * @throws \Exception
     */
    public function addMethod(Method $method, $override = false) {
        $methods = $this->getMethods();
        $key = $method->getName();
        if(array_key_exists($key, $methods) && !$override) {
            throw new \Exception('A Method with the name of: ' . $method->getName() . ' already exists');
        }
        $methods[$key] = $method;
        ksort($methods);
        $this->setMethods($methods);
    }

    /**
     * Getter
     * @return Variable[]
     */
    public function getVariables() {
        return $this->variables;
    }

    /**
     * Adds a new method
     *
     * @param Variable $variable
     *
     * @return void
     * @throws \Exception
     */
    public function addVariable(Variable $variable) {
        $variables = $this->getVariables();
        $key = $variable->getName();
        if(array_key_exists($key, $variables)) {
            throw new \Exception('A Variable with the name of: ' . $variable->getName() . ' already exists');
        }
        $variables[$key] = $variable;
        ksort($variables);
        $this->setVariables($variables);
    }

    /**
     * Setter
     * @param Variable[] $variables
     */
    private function setVariables(array $variables) {
        $this->variables = $variables;
    }

    /**
     * Getter
     * @return string
     */
    public function getNamespace() {
        return $this->namespace;
    }

    /**
     * Setter
     * @param string $namespace
     */
    public function setNamespace($namespace) {
        $this->namespace = trim($namespace, '\\');
    }

    /**
     * Getter
     * @return string[]
     */
    public function getUses() {
        return $this->uses;
    }

    /**
     * Setter
     * @param string[] $uses
     */
    private function setUses(array $uses) {
        $this->uses = $uses;
    }

    /**
     * Adds a new use
     *
     * @param string $use
     *
     * @return void
     */
    public function addUse($use) {
        $use = trim($use, '\\');
        $uses = $this->getUses();
        if(!in_array($use, $uses)) {
            $uses[] = $use;
        }
        sort($uses);
        $this->setUses($uses);
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
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Converts this class to a string
     * @return string
     */
    public function toString() {
        $documentation = preg_replace_callback(
            '@\r?\n@',
            function ($matches) {
                return $matches[0] . ' * ';
            },
            trim($this->getDocumentation())
        );
        if($documentation == '') {
            $documentation = $this->getName();
        }
        $buffer = '<?php
namespace ' . $this->getNamespace() . ';
';
        foreach($this->getUses() as $use) {
            $buffer .= 'use ' . $use . ';' . "\n";
        }
        $buffer .= '/**
 * ' . $documentation . '
 */
' . ($this->isAbstract() ? 'abstract ' : '') . 'class ' . $this->getName() . ' extends ' . $this->getExtends() . (sizeOf($this->getImplements()) > 0 ? ' implements ' . implode(', ', $this->getImplements()) : '') . ' {
';
        foreach($this->getTraits() as $trait) {
            $buffer .= '	use ' . $trait . ';
';
        }
        foreach($this->getConstants() as $name => $value) {
            $buffer .= '	const ' . $name . ' = \'' . str_replace("'", "\\'", $value) . '\';
';
        }
        foreach($this->getVariables() as $variable) {
            $buffer .= $variable->toString();
        }
        foreach($this->getMethods() as $method) {
            $buffer .= $method->toString();
        }
        $buffer .= '}';
        return str_replace("\r\n", "\n", $buffer);
    }

    /**
     * Gets the filename for this class
     * @return string
     * @throws \Exception
     */
    public function getFileName() {
        $isTestClass = substr($this->getName(), -4) == 'Test';
        if($isTestClass) {
            $rootDirectory = $this->getConfiguration()->getRootTestDirectory();
        } else {
            $rootDirectory = $this->getConfiguration()->getRootDirectory();
        }
        if(substr($this->getNamespace(), 0, strlen($this->getConfiguration()->getRootNamespace())) != $this->getConfiguration()->getRootNamespace()) {
            throw new \Exception('The Root namespace does not match this classes namespace, could not get the file name'."\n".$this->getNamespace()."\n".$this->getConfiguration()->getRootNamespace());
        }
        if($isTestClass) {
            $namespace = $this->getNamespace();
        } else {
            $namespace = substr($this->getNamespace(), strlen($this->getConfiguration()->getRootNamespace()));
        }
        if(DIRECTORY_SEPARATOR != '\\') {
            $namespace = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
        }
        return $rootDirectory . DIRECTORY_SEPARATOR . $namespace . DIRECTORY_SEPARATOR . $this->getName() . '.php';
    }

    /**
     * Writes this class to the server
     * @return void
     */
    public function write() {
        $filename = $this->getFileName();
        if(basename($filename) == '.php') {
            throw new CatchableException('Invalid Filename: '.$filename);
        }
        if(!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        file_put_contents($filename, $this->toString());
    }

}