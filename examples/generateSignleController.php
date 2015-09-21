<?php
use Phalcon\DI\FactoryDefault;
use Phalcon\ApiGenerator\Generator\ControllerBuilder;
use Phalcon\ApiGenerator\Generator\Configuration;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
require_once '/var/www/vendor/autoload.php';
require_once '/var/www/web/app/config/bootstrap.php';
/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = FactoryDefault::getDefault();
$di->set(
	'instanceDb',
	function() {
		return new DbAdapter(
			array(
				'host'     => 'localhost',
				'username' => 'root',
				'password' => 'root',
				'dbname'   => 'db',
				'port'     => '3306'

			)
		);
	}
);

$configuration = new Configuration();
$configuration->setConnectionService('db');
$configuration->setRootDirectory('/var/www/web/app/');
$configuration->setRootTestDirectory('/var/www/tests');
$configuration->setRootNamespace('Api');
$configuration->setDateClassName('Api\Services\Date\Date');
$configuration->setDateTimeClassName('Api\Services\Date\DateTime');
$configuration->setModelSubNamespace('Instance');
$instanceConfiguration = $configuration;

//Build Controller
$model = new \Api\Models\Instance\Example();
$controllerBuilder = new ControllerBuilder($instanceConfiguration, $model);
$controllerBuilder->output();