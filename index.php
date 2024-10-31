<?php
require_once __DIR__ . '/vendor/autoload.php';

use AgungDhewe\Webservice\Configuration;
use AgungDhewe\PhpLogger\Logger;
use AgungDhewe\PhpLogger\LoggerOutput;


try {
	$configfile = 'config-production.php';
	if (php_sapi_name() === 'cli') {
		// jalan di CLI	
		die("Script harus dijalankan di Web Server\n\n");
	}

	if (getenv('DEBUG')=="true") {
		$configfile = 'config-development.php';
	};

	$configpath = implode('/', [__DIR__, $configfile]);
	if (!is_file($configpath)) {
		throw new Exception("File '$configfile' tidak ditemukan");
	}

	require_once $configpath;
	Configuration::setRootDir(__DIR__);
	Configuration::setLogger();

	// WebRequest::init();

} catch (Exception $ex) {
	header("HTTP/1.1 500 Internal Error");
	echo "<h1>Internal Error</h1>";
	echo $ex->getMessage();
}