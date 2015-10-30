<?php

$router = new Phalcon\Mvc\Router(false);
$router->removeExtraSlashes(true);
$router->notFound(
	array(
		'namespace'  => 'YourService\Controllers',
		'controller' => 'Index',
		'action'     => 'route404'
	)
);

//Add Main Controllers
$router->add(
	'/:controller/:action/:params',
	array(
		'namespace' => 'YourService\Controllers',
		'controller' => 1,
		'action' => 2,
		'params' => 3,
	)
);

$router->add(
	'/:controller/:action',
	array(
		'namespace' => 'YourService\Controllers',
		'controller' => 1,
		'action' => 2
	)
);

$router->add(
	'/:controller',
	array(
		'namespace' => 'YourService\Controllers',
		'controller' => 1
	)
);

$apiRoutes = new \Bullhorn\FastRest\Api\Services\Config\ApiRoutes('Api', 'YourService\Controllers\Api');
$apiRoutes->addRoutes($router);

return $router;