<?php
namespace Phalcon\FastRest;
use Phalcon\DI\InjectionAwareInterface;
abstract class Base implements InjectionAwareInterface {
	use DependencyInjection;
}
