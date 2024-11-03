<?php
require_once __DIR__ . '/vendor/autoload.php';

use AgungDhewe\Webservice\Configuration;
use AgungDhewe\Webservice\Service;

use AgungDhewe\Webservice\Router;


// script ini hanya dijalankan di web server
if (php_sapi_name() === 'cli') {
	die("Script cannot be executed directly from CLI\n\n");
}


try {
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

	

	// Route internal
	// Router::GET('container', 'AgungDhewe\Webservice\Routers\ContainerRoute');
	// Router::GET('login', 'AgungDhewe\Webservice\Routers\LoginRoute');
	Router::GET('template/*', 'AgungDhewe\Webservice\Routers\TemplateRoute');
	Router::GET('asset/*', 'AgungDhewe\Webservice\Routers\AssetRoute');
	Router::GET('page/*', 'AgungDhewe\Webservice\Routers\PageRoute');
	Router::POST('api/*', 'AgungDhewe\Webservice\Routers\ApiRoute');

	// Route external: akan menggunakan format PSR4
	Router::GET('module/asset/*', 'AgungDhewe\Webservice\Routers\ModuleAssetRoute');
	Router::GET('module/page/*', 'AgungDhewe\Webservice\Routers\ModulePageRoute');
	Router::POST('module/api/*', 'AgungDhewe\Webservice\Routers\ModuleApiRoute');

	// Serve url
	Service::main();

	echo "\n";
} catch (Exception $ex) {
	Service::handleHttpException($ex);
}