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
		'output' => 'file',      			// output ke filename (log.txt)
		'filename' => 'log.txt',			// nama file log
		'maxLogSize' => '10485760',			// ukuran maksimal log (bytes)
		'debug' => true,         			// output ke debug.txt, isi akan dikosongkan apabila ada parameter $_GET['cleardebug'] = 1, atau pada CLI, saat script dijalankan
		'showCallerFileOnInfo' => false,  	// default false, jika true, menampilkan referensi caller file di Log:info()
		'debugChannel' => 'your-debug-channel-name'
	],

	'WebTemplate' => new PlainTemplate(), 
	'PagesDir' => 'pages',
	'BaseUrl' => 'fgta5.localhost',
	'IndexPage' => 'module/page/Fgta5/Framework/Pages/Container',
	
]);

Configuration::UseConfig([
	Configuration::DB_MAIN => 'DbMain',
]);

