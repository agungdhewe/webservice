<?php

use \AgungDhewe\Webservice\Configuration;
use \AgungDhewe\Webservice\PlainTemplate;


Configuration::Set([
	
	'DbMain' => [
		'DSN' => "mysql:host=127.0.0.1;dbname=mydb",
		'user' => "root",
		'pass' => ""
	],

	'Logger' => [
		'output' => 'none',      // output ke filename (log.txt)
		'filename' => 'log.txt',
		'clearOnStart' => false, // jika true, hapus file log.txt saat start
		'debug' => true,         // output ke debug.txt, isi akan dikosongkan apabila ada parameter $_GET['cleardebug'] = 1, atau pada CLI, saat script dijalankan
		'showCallerFileOnInfo' => false  // default false, jika true, menampilkan referensi caller file di Log:info()
		
	],

	'WebTemplate' => new PlainTemplate(), 
	'PagesDir' => 'pages',
	'BaseUrl' => 'fgta5.localhost',
	'IndexPage' => 'module/page/Fgta5/Framework/Pages/Container',
	'DebugChannel' => 'webservice-debug-channel'
]);

Configuration::UseConfig([
	Configuration::DB_MAIN => 'DbMain',
]);

