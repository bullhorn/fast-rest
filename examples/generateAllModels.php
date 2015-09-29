<?php
use Phalcon\DI\FactoryDefault;
use Bullhorn\FastRest\Generator\ModelBuilder;
use Bullhorn\FastRest\Generator\Configuration;
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
$configuration->setModelSubNamespace('Instance');
$instanceConfiguration = $configuration;

//Build Controller
ModelBuilder::writeAll($instanceConfiguration, ['ignoreTable1', 'ignoreTable2']);