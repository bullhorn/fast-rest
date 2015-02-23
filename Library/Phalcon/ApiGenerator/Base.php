<?php
namespace Phalcon\ApiGenerator;
use Phalcon\DI\InjectionAwareInterface;
abstract class Base implements InjectionAwareInterface {
	use DependencyInjection;
}
