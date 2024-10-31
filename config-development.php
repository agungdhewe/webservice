<?php

use AgungDhewe\Webservice\Configuration;


Configuration::Set([
	
	'DbMain' => [
		'DSN' => "mysql:host=127.0.0.1;dbname=tfidblocal",
		'user' => "root",
		'pass' => "rahasia123!"
	],

	'Logger' => [
		'output' => 'file',
		'filename' => 'log.txt',
		'ClearOnStart' => true,
		'debug' => true,
	]
]);

Configuration::UseConfig([
	Configuration::DB_MAIN => 'DbMain',
]);

