<?php
require_once __DIR__ . '/vendor/autoload.php';

use AgungDhewe\Webservice\Configuration;
use AgungDhewe\Webservice\Service;

use AgungDhewe\Webservice\Router;
use AgungDhewe\Webservice\Routes\PageRoute;


// script ini hanya dijalankan di web server
if (php_sapi_name() === 'cli') {
	die("Script cannot be executed directly from CLI\n\n");
}



try {
	PageRoute::ResetDebugOnPageRequest();

	$configfile = 'config.php';
	if (getenv('CONFIG')) {
		$configfile = getenv('CONFIG');
	}

	$configpath = implode('/', [__DIR__, $configfile]);
	if (!is_file($configpath)) {
		throw new Exception("Configuration '$configfile' is not found");
	}

	require_once $configpath;
	Configuration::setRootDir(__DIR__);
	Configuration::setLogger();

	// Prepare debug
	PageRoute::ResetDebugOnPageRequest(["page/*"]);

	// Route internal
	Router::setupDefaultRoutes();

	// Serve url
	Service::main();

	echo "\n";
} catch (Exception $ex) {
	Service::handleHttpException($ex);
}