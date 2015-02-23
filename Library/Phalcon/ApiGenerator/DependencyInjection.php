<?php
//To use this, you must also implement \Phalcon\DI\InjectionAwareInterface
namespace Phalcon\ApiGenerator;
use Phalcon\DI\FactoryDefault;
trait DependencyInjection {
	/** @var  FactoryDefault */
	private $di;

	/**
	 * Gets the dependency injector
	 * @return FactoryDefault
	 */
	public function getDi() {
		if(is_null($this->di)) {
			$this->di = FactoryDefault::getDefault();
		}
		return $this->di;
	}

	/**
	 * Sets the dependency injector
	 * We cannot strict type the variable
	 * @param FactoryDefault $di
	 *
	 * @return void
	 */
	public function setDi($di) {
		$this->di = $di;
	}
}