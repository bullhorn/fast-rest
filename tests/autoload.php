<?php
require_once __DIR__ . '/../vendor/autoload.php';
$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerNamespaces(
    array(
        'Tests' => __DIR__
    )
);
$loader->register();