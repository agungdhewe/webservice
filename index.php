<?php
require_once __DIR__ . '/vendor/autoload.php';

use AgungDhewe\Webservice\Configuration;
use AgungDhewe\Webservice\Database;
use AgungDhewe\Webservice\Webservice;;


// script ini hanya dijalankan di web server
if (php_sapi_name() === 'cli') {
	die("Script harus dijalankan di Web Server\n\n");
}


try {
	$configfile = 'config.php';
	if (getenv('CONFIG')) {
		$configfile = getenv('CONFIG');
	}

	$configpath = implode('/', [__DIR__, $configfile]);
	if (!is_file($configpath)) {
		throw new Exception("File '$configfile' tidak ditemukan");
	}

	echo $configpath;

	require_once $configpath;
	Configuration::setRootDir(__DIR__);
	Configuration::setLogger();

	Database::Connect();

	Webservice::main();

} catch (Exception $ex) {
	header("HTTP/1.1 500 Internal Error");
	echo "<h1>Internal Error</h1>";
	echo $ex->getMessage();
}