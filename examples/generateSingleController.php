<?php
use Bullhorn\FastRest\Api\Models\ApiInterface;
use Phalcon\DI\FactoryDefault;
use Bullhorn\FastRest\Generator\ControllerBuilder;
use Bullhorn\FastRest\Generator\Configuration;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
require_once '/var/www/vendor/autoload.php';
/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = FactoryDefault::getDefault();
$di->set(
	'YourService',
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
$configuration->setModelSubNamespace('Database');
$instanceConfiguration = $configuration;

//Build Controller
//You frequently only want to include the bootstrap for loading the controller, not for the models directly
require_once '/var/www/web/app/config/bootstrap.php';
/** @type ApiInterface $model */
$model = new \Api\Models\Instance\Example();
$controllerBuilder = new ControllerBuilder($instanceConfiguration, $model);
//Output to ui
$controllerBuilder->output();
//Return as a string to write directly
file_put_contents('/var/www/web/app/controllers/Api/'.ucfirst(strtolower($model->getSource())).'sController.php', $controllerBuilder->__toString());