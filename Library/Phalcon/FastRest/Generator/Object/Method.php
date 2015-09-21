<?php
namespace Phalcon\FastRest\Generator\Object;

class Method {
	/** @var  string */
	private $name;
	/** @var  string */
	private $description;
	/** @var  Parameter[] */
	private $parameters = array();
	/** @var  string */
	private $content;
	/** @var  string */
	private $returnType = 'void';
	/** @var  string */
	private $access = 'public';
	/** @var bool  */
	private $final = false;
	/** @var string  */
	private $throws = array();
	/** @var bool  */
	private $static = false;

	/**
	 * Getter
	 * @return boolean
	 */
	public function isStatic() {
		return $this->static;
	}

	/**
	 * Setter
	 * @param boolean $static
	 */
	public function setStatic($static) {
		$this->static = $static;
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getThrows() {
		return $this->throws;
	}

	/**
	 * Setter
	 * @param string $throws
	 */
	public function setThrows(array $throws) {
		$this->throws = $throws;
	}


	/**
	 * Getter
	 * @return boolean
	 */
	public function isFinal() {
		return $this->final;
	}

	/**
	 * Setter
	 * @param boolean $final
	 */
	public function setFinal($final) {
		$this->final = $final;
	}



	/**
	 * Getter
	 * @return string
	 */
	public function getAccess() {
		return $this->access;
	}

	/**
	 * Setter
	 * @param string $access
	 */
	public function setAccess($access) {
		$this->access = $access;
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
	 * Getter
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Setter
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * Getter
	 * @return Parameter[]
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 * Setter
	 * @param Parameter[] $parameters
	 */
	private function setParameters(array $parameters) {
		$this->parameters = $parameters;
	}

	/**
	 * Adds a new parameter
	 *
	 * @param Parameter $parameter
	 *
	 * @return void
	 */
	public function addParameter(Parameter $parameter) {
		$parameters = $this->getParameters();
		$parameters[] = $parameter;
		$this->setParameters($parameters);
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Setter
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getReturnType() {
		return $this->returnType;
	}

	/**
	 * Setter
	 * @param string $returnType
	 */
	public function setReturnType($returnType) {
		$this->returnType = $returnType;
	}

	/**
	 * Gets the string version of this method
	 * @return string
	 */
	public function toString() {
		$documentation = preg_replace_callback(
			'@\r?\n@',
			function($matches) {
				return $matches[0].'	 * ';
			},
			trim($this->getDescription())
		);
		if($documentation=='') {
			$documentation = $this->getName();
		}
		$buffer = '
	/**
	 * '.$documentation.'
';
		foreach($this->getParameters() as $parameter) {
			$buffer .= '	 * @param '.$parameter->getType().' $'.$parameter->getName().' '.$parameter->getDescription()."\n";
		}
		if($this->getName()!='__construct') {
			$buffer .= '	 * @return '.$this->getReturnType().'
';
		}
		foreach($this->getThrows() as $throw) {
			$buffer .= '	 * @throws '.$throw.'
';
		}
		$buffer .= '	 */
	'.($this->isFinal()?'final ':'').$this->getAccess().' '.($this->isStatic()?'static ':'').'function '.$this->getName().'(';
		foreach($this->getParameters() as $key=>$parameter) {
			if($key!=0) {
				$buffer .= ', ';
			}
			if($parameter->isStrictType()) {
				if(!is_null($parameter->getStrictClass())) {
					$buffer .= $parameter->getStrictClass() . ' ';
				} else {
					$buffer .= $parameter->getType() . ' ';
				}
			}
			$buffer .= '$'.$parameter->getName();
			if(!is_null($parameter->getDefaultValue())) {
				$buffer .= '='.$parameter->getDefaultValue();
			}
		}
		$buffer .= ') {
		'.trim($this->getContent()).'
	}
';
		return $buffer;
	}


}