<?php

use AgungDhewe\Webservice\Configuration;


Configuration::Set([
	
	'DbMain' => [
		'DSN' => "mysql:host=127.0.0.1;dbname=webdblocal",
		'user' => "root",
		'pass' => ""
	],

	'Logger' => [
		'output' => 'file',
		'filename' => 'log.txt',
		'ClearOnStart' => false,
		'debug' => false,
	]
]);

Configuration::UseConfig([
	Configuration::DB_MAIN => 'DbMain',
]);

