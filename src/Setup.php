<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

use AgungDhewe\Cli\color;
use AgungDhewe\Cli\shell;
use tidy;

final class Setup {

	const string MIN_PHP_VERSION = "8.3";

	const string YES = 'Y';
	const string NO = 'N';

	const int ERR_CANCEL = 9;
	const int ERR_CRITICAL = 1;
	const int ERR_GENERATED = 2;


	static private string $MOLDDIR;

	static private string $LBL_WARNING = color::FG_BOLD_YELLOW . "[WARNING]" . color::RESET;
	static private string $LBL_OK = color::FG_BOLD_GREEN . "OK" . color::RESET;





	public static function sini(string $dir, ?array $setupconfig=null) : void { 
		self::$MOLDDIR = join(DIRECTORY_SEPARATOR, [__DIR__, '..' , 'mold']);
		try {

			if (!self::valid_php_version()) {
				throw new \Exception("Versi php minimal harus " . self::MIN_PHP_VERSION . "\n" . "Versi saat ini " . phpversion(), self::ERR_CRITICAL);
			}

			$generate_lock = join(DIRECTORY_SEPARATOR, [$dir, "generate.lock"]);
			if (is_file($generate_lock)) {
				throw new \Exception("Setup already done", self::ERR_GENERATED);
			}


			echo "*===================================*\n";
			echo "* PHP AgungDhewe\Webservice Library *\n";
			echo "*===================================*\n";


			if (!is_array($setupconfig)) {
				$conf = self::QuestAndConfigSetup($dir);
			} else {
				$conf = [
					'dir' => $dir,
					'docker' => $setupconfig['docker'],
					'containername' => $setupconfig['containername'],
					'containerport' => $setupconfig['containerport'],
					'networkname' => $setupconfig['networkname'],
					'webservice_dev_mode' => $setupconfig['webservice_dev_mode'],
				];
			}

			echo "\n";
			self::CreateVSCodeDir($conf);
			self::CreateDockerBuild($conf);
			self::CreateGitIgnore($conf);
			self::CreateHtaccess($conf);
			self::CreateConfig($conf);
			self::CreateDebuger($conf);
			self::CreateWorkspace($conf);
			self::CreateIndex($conf);
			self::CreatePlugins($conf);
			self::CreateTemplate($conf); // TODO: lanjutkan untuk ini, belum dibuat per 241108
			self::CreatePages($conf);     // TODO: lanjutkan untuk ini, belum dibuat per 241108
			self::CreateContents($conf);     // TODO: lanjutkan untuk ini, belum dibuat per 241108

			// copy default favicon
			self::copyFavicon($conf);

			// update composer
			self::updateComposer($conf);


			// changemod 
			chmod(join(DIRECTORY_SEPARATOR, [$dir, "docker-up.sh"]), 0774);
			chmod(join(DIRECTORY_SEPARATOR, [$dir, "docker-down.sh"]), 0774);
			chmod(join(DIRECTORY_SEPARATOR, [$dir, "docker-cli.sh"]), 0774);

			chmod(join(DIRECTORY_SEPARATOR, [$dir, "debug-monitor.sh"]), 0774);
			chmod(join(DIRECTORY_SEPARATOR, [$dir, "testurl.sh"]), 0774);
			chmod(join(DIRECTORY_SEPARATOR, [$dir, "log.txt"]), 0666);
			chmod(join(DIRECTORY_SEPARATOR, [$dir, "debug.txt"]), 0666);



			echo "\n\n";
			echo color::FG_BOLD_GREEN. "Generate Data Selesai." . color::RESET . "\n";
			echo "jalankan " . color::FG_BOLD_YELLOW . "composer update" . color::RESET . " untuk memperbarui librari." . "\n";


			// buat file generate.lock
			$fp = fopen($generate_lock, "w");
			fwrite($fp, "project generated at " . date('Y-m-d H:i:s') . "\n");
			fclose($fp);

		} catch (\Exception $ex) {
			if ($ex->getCode()==self::ERR_CANCEL) {
				echo "\n\n";
				echo color::FG_BOLD_YELLOW . "Setup Canceled" . color::RESET;
				echo "\n\n";
				exit(0);
			} else if ($ex->getCode()==self::ERR_GENERATED) {
				echo "\n\n";
				echo color::FG_BOLD_YELLOW . "Setup Canceled is canceleed, because is already locked." . color::RESET;
				echo "\n\n";
				exit(0);
			}

			echo color::FG_BOLD_RED . "ERROR" . color::RESET;
			echo "\n";
			echo $ex->getMessage();
		} finally {
			echo "\n\n";
		}
	}





	private static function CreateVSCodeDir(array $conf) : void {
		echo "Generate .vscode ... \n";

		$dir = $conf['dir'];
		$DATA = self::getData($conf);

		// buat direktory .vscode
		$vscodedir = join(DIRECTORY_SEPARATOR, [$dir, '.vscode']);
		self::createDirectory($vscodedir);
		$subjects = [
			['mold'=>'vscode_launch_json.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$vscodedir, "launch.json"])],
		];
		
		self::generate($subjects, $DATA);
		echo "\n";
	}


	private static function CreateDockerBuild(array $conf) : void {
		if (!$conf['docker']) {
			echo "skipping create dockerbuilder scripts.\n";
			echo "\n";
			return;
		}

		// buat direktorinya dulu
		echo "Generate Docker Builder ... \n";

		$dir = $conf['dir'];
		$DATA = self::getData($conf);

		// buat direktory dockerbuild
		$dockerbuilddir = join(DIRECTORY_SEPARATOR, [$dir, 'dockerbuild']);
		self::createDirectory($dockerbuilddir);
		$subjects = [
			['mold'=>'xdebug_ini.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dockerbuilddir, "docker-php-ext-xdebug.ini"])],
			['mold'=>'container_yml.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dockerbuilddir, $conf['containername']. ".yml"])],
			['mold'=>'webserver_conf.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dockerbuilddir, "webserver.conf"])],

			['mold'=>'docker_up.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dir, "docker-up.sh"])],
			['mold'=>'docker_down.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dir, "docker-down.sh"])],
			['mold'=>'docker_cli.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dir, "docker-cli.sh"])],

		];

		self::generate($subjects, $DATA);
		echo "\n";
	}

	private static function CreateGitIgnore(array $conf) : void {
		echo "Generate Gitignore ... \n";
		$dir = $conf['dir'];
		$DATA = self::getData($conf);
	
		$subjects = [
			['mold'=>'gitignore.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dir, ".gitignore"])],
		];

		self::generate($subjects, $DATA);
		echo "\n";
		

	}

	private static function CreateHtaccess(array $conf) : void {
		echo "Generate htaccess ... \n";
		$dir = $conf['dir'];
		$DATA = self::getData($conf);

		$subjects = [
			['mold'=>'htaccess.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dir, ".htaccess"])],
		];

		self::generate($subjects, $DATA);
		echo "\n";
	
	}

	private static function CreateConfig(array $conf) : void {
		echo "Generate Config ... \n";
		$dir = $conf['dir'];
		$DATA = self::getData($conf);

		$subjects = [
			['mold'=>'config_php.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dir, "config.php"])],
			['mold'=>'config_php.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dir, "config-development.php"])],
		];

		self::generate($subjects, $DATA);
		echo "\n";
	}

	



	private static function CreateDebuger(array $conf) : void {
		echo "Generate Debugger ... \n";
		$dir = $conf['dir'];
		$DATA = self::getData($conf);

		$subjects = [
			['mold'=>'testurl.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dir, "testurl.sh"])],
			['mold'=>'debugmonitor.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dir, "debug-monitor.sh"])],
			['mold'=>'blank.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dir, "debug.txt"])],
			['mold'=>'blank.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dir, "log.txt"])],
		];

		self::generate($subjects, $DATA);
		echo "\n";

	}


	private static function CreatePlugins(array $conf) : void {
		echo "Generate Plugin directory ... \n";

		$dir = $conf['dir'];
		$DATA = self::getData($conf);

		// buat direktory dockerbuild
		$pluginsdir = join(DIRECTORY_SEPARATOR, [$dir, 'plugins']);
		self::createDirectory($pluginsdir);

		echo "\n";
	}

	private static function CreateWorkspace(array $conf) : void {
		echo "Generate VSCode Workspace ... \n";
		$dir = $conf['dir'];
		$DATA = self::getData($conf);

		$subjects = [
			['mold'=>'workspace.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dir, $conf['containername']. ".code-workspace"])],
		];

		self::generate($subjects, $DATA);
		echo "\n";

	}

	private static function CreateIndex(array $conf) : void {
		echo "Generate Index ... \n";
		$dir = $conf['dir'];
		$DATA = self::getData($conf);

		$subjects = [
			['mold'=>'index.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$dir, "index.php"])],
		];

		self::generate($subjects, $DATA);
		echo "\n";
	}




	private static function CreateTemplate(array $conf) : void {
		echo "Generate Template ... \n";
		$dir = $conf['dir'];
		$DATA = self::getData($conf);

		// buat direktory template
		$templatedir = join(DIRECTORY_SEPARATOR, [$dir, 'templates']);
		self::createDirectory($templatedir);

		// buat direktory template asset
		$assetdir = join(DIRECTORY_SEPARATOR, [$templatedir, 'assets']);
		self::createDirectory($assetdir);


		$containername = $DATA['containername'];
		$subjects = [
			['mold'=>'template_phtml.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$templatedir, $containername . ".phtml"])],
			['mold'=>'template_style.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$assetdir, "style.css"])],
		];

		self::generate($subjects, $DATA);


		// copy contoh image
		$sourcefile = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'templates', 'plaintemplate', 'assets', 'mywebservice.png']);
		$targetfile = join(DIRECTORY_SEPARATOR, [$assetdir, 'logo.png']);
		copy($sourcefile, $targetfile);


		echo "\n";
	}


	private static function CreatePages(array $conf) : void {
		echo "Generate Index ... \n";
		$dir = $conf['dir'];
		$DATA = self::getData($conf);

		// buat direktory pages
		$pagesdir = join(DIRECTORY_SEPARATOR, [$dir, 'pages']);
		self::createDirectory($pagesdir);


		$subjects = [
			['mold'=>'home_phtml.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$pagesdir, "home.phtml"])],
			['mold'=>'notfound_phtml.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$pagesdir, "notfound.phtml"])],
			['mold'=>'error_phtml.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$pagesdir, "error.phtml"])],
		];

		self::generate($subjects, $DATA);
		echo "\n";
	}


	private static function CreateContents(array $conf) : void {
		echo "Generate Index ... \n";
		$dir = $conf['dir'];
		$DATA = self::getData($conf);

		// buat direktory pages
		$contentsdir = join(DIRECTORY_SEPARATOR, [$dir, 'contents']);
		self::createDirectory($contentsdir);


		$subjects = [
			['mold'=>'membuat_halaman_phtml.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$contentsdir, "membuat-halaman.phtml"])],
			['mold'=>'membuat_template_phtml.phtml', 'target'=>join(DIRECTORY_SEPARATOR, [$contentsdir, "membuat-template.phtml"])],
		];

		// copy contoh image
		$sourcefile = join(DIRECTORY_SEPARATOR, [__DIR__,  '..', 'mold', 'image.jpg']);
		$targetfile = join(DIRECTORY_SEPARATOR, [$contentsdir, 'image.jpg']);
		copy($sourcefile, $targetfile);


		self::generate($subjects, $DATA);
		echo "\n";
	}


	private static function copyFavicon($conf) : void {
		echo "set favicon ... \n";
		$dir = $conf['dir'];

		$source = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'favicon.ico']);
		$target = join(DIRECTORY_SEPARATOR, [$dir, 'favicon.ico']);

		if (!is_file($source)) {
			throw new \Exception('favicon.ico not found in ' . __DIR__, self::ERR_CRITICAL);
		}

		if (!is_file($target)) {
			copy($source, $target);
		}
		
		echo "\n";
	}

	private static function updateComposer(array $conf) : void {
		echo "Update Composer ... \n";
		$dir = $conf['dir'];

		$composerfilepath = join(DIRECTORY_SEPARATOR, [$dir, "composer.json"]);
		$fp = fopen($composerfilepath, "r");
		$content = fread($fp, filesize($composerfilepath));
		fclose($fp);

		$jsondata = json_decode($content, true);
		
		// print_r($jsondata);

		// add agungdhewe\phplogger;
		if (!array_key_exists('require', $jsondata)) {
			$jsondata['require'] = [];
		} 
		$require = $jsondata['require'];
		$jsondata['require'] = $require;


		$repo_exists = false;
		if (!array_key_exists('repositories', $jsondata)) {
			$jsondata['repositories'] = [];
		} 
		$repositories = $jsondata['repositories'];
		foreach ($repositories as $repo) {
			if (array_key_exists('url', $repo)) {
				$url = $repo['url'];
				if ($url=='plugins/*/*') {
					$repo_exists = true;
				}
			}
		}

		if (!$repo_exists) {
			$repositories[] = [
				'type' => 'path',
				'url' => 'plugins/*/*',
				'options' => [
					'symlink' => true
				]
			];
		}
		$jsondata['repositories'] = $repositories;


		// test 
		$jsontext =  json_encode($jsondata, JSON_PRETTY_PRINT);
		$jsontext = str_replace('\/', '/', $jsontext);

		// backup first original composer file before generate
		$backupfile = $composerfilepath.".original";
		if (!is_file($backupfile)) {
			copy($composerfilepath, $backupfile);
		}

		// backup prev composer file
		$backupfile = $composerfilepath.".prev";
		copy($composerfilepath, $backupfile);
		$fp = fopen($composerfilepath, "w");
		fputs($fp, $jsontext);
		fclose($fp);		
	}


	private static function QuestAndConfigSetup(string $dir) : array {
		$answers = [];
		
		try {

			echo color::FG_BOLD_WHITE . "Anda akan setup webserver di " . color::FG_BOLD_BLUE . $dir . color::RESET . "\n";
			$answ = shell::ask("Anda yakin?", [self::YES, self::NO], self::NO);
			$answ = strtoupper($answ);
			if ($answ==self::NO) {
				throw new \Exception("Setup Canceled", self::ERR_CANCEL);
			}
			$answers['dir'] = $dir;


			// apakah akan menggunakan docker container ?
			echo "\n";
			echo "librari Webservice ini menggunakan " . color::FG_BOLD_WHITE . "PHP 8.3" . color::RESET . " keatas\n";
			echo "disarankan menggunakan docker container untuk mempermudah setup.\n";
			echo "image untuk server ini bisa di download di:\n";
			echo "\n";
			echo "      ".  color::FG_BOLD_BLUE . "https://github.com/agungdhewe/docker_webserver_8_3". color::RESET ."\n";
			echo "\n";
			$answ = shell::ask("apakah anda akan menggunakan docker container?", [self::YES, self::NO], self::YES);
			$answ = strtoupper($answ);
			$answers['docker'] = $answ==self::YES ? true : false;

			
			$confirmed = false;
			while (!$confirmed) {


				// menggunakan docker container;
				if ($answers['docker']==self::YES) {
					echo "\n\n";
					echo color::FG_BOLD_WHITE . "Container Setup" . color::RESET . "\n";
					
					// nama Container
					$answ = shell::ask("Nama Container:", null, 'mywebservice', '/^[a-zA-Z][a-zA-Z0-9]*$/', 'nama container hanya boleh huruf dan angka');
					$answers['containername'] = $answ;

					// port
					$answ = shell::ask("Port:", null, '80', '/^(8[0-9]|8[0-9]{3})$/', 'port harus dalam rentang 80-89 atau 8000-8999');
					$answers['containerport'] = $answ;

					// nama network
					$answ = shell::ask("Nama Network (external):", null, 'devnetwork', '/^[a-zA-Z][a-zA-Z0-9]*$/', 'nama network hanya boleh huruf dan angka');
					$answers['networkname'] = $answ;

				}

				// review ulang
				echo "\n\n";
				echo color::FG_BOLD_WHITE . "Konfigurasi" . color::RESET . "\n";
				echo "Direktori tujuan  :  " . $dir . "\n";
				echo "Container         :  " . ($answers['docker'] ? 'yes' : 'no') . "\n";
				echo "   Nama Container :  " . $answers['containername'] . "\n";
				echo "   Port           :  " . $answers['containerport'] . "\n";
				echo "   Network        :  " . $answers['networkname'] . "\n";
				echo "\n";

				// konfirmasi
				$answ = shell::ask("apakah konfigurasi sudah sesuai?", [self::YES, self::NO, 'E'], self::YES);
				$answ = strtoupper($answ);
				if ($answ=='E') {
					throw new \Exception("Setup Canceled", self::ERR_CANCEL);

				} else if ($answ==self::YES) {
					$confirmed = true;
					break;
				}
			}

			// konfirmasi lagi untuk memastikan setup
			echo "\n\nSelanjutnya file-file yang diperlukan akan diinstall ke direktori " .color::FG_BOLD_BLUE . $dir . color::RESET . "\n";
			$answ = shell::ask("Anda yakin?", [self::YES, self::NO], self::NO);
			$answ = strtoupper($answ);
			if ($answ==self::NO) {
				throw new \Exception("Setup Canceled", self::ERR_CANCEL);
				exit(0);
			}

			return $answers;
		} catch (\Exception $ex) {
			throw $ex;
		}
	} 


	private static function getDebugPort(array $conf) : int {
		$debugport = 9003;
		if ($conf['docker']) {
			$port =  $conf['containerport'];
			if ($port<90) {
				$debugport = 9000 + ($port-80);
			} else {
				$debugport = 9000 + ($port-8000);
			}
		}
		return $debugport;
	}


	public static function generate(array $subjects, array $DATA) : void {
		foreach ($subjects as $subject) {
			$moldfilename = $subject['mold'];
			$targetfilepath = $subject['target'];
			$filename = basename($targetfilepath);
			$moldfilepath = join(DIRECTORY_SEPARATOR, [self::$MOLDDIR, $moldfilename]);
			if (self::confirmOverwriteIfExists($targetfilepath)) {
				echo "creating $filename ... ";
				self::produce($moldfilepath, $targetfilepath, $DATA);
				echo self::$LBL_OK . "\n";
			} else {
				echo "skip $filename";
			}
		}
	}


	public static function produce(string $moldfilepath, string $targetfilepath, array $data) : void {
		$DATA = $data; // $DATA akan digunakan di $moldfilepath
		if (!is_file($moldfilepath)) {
			$moldfilename = basename($moldfilepath);
			throw new \Exception("mold file $moldfilename is not exists", self::ERR_CRITICAL);
		}

		ob_start();
		include $moldfilepath;
		$content = ob_get_contents();
		ob_end_clean();

		// tulis ke file
		$fp = fopen($targetfilepath, "w");
		fwrite($fp, $content);
		fclose($fp);
	}

	public static function confirmOverwriteIfExists(string $filepath) : bool {
		if (is_file($filepath)) {
			// file sudah ada, tanya apakah akan di overide
			$filename = basename($filepath);
			$dirname = dirname($filepath);
			echo "\n";
			echo self::$LBL_WARNING . "\n";
			echo "File $filename sudah ada di direktori $dirname\n";
			$answ = shell::ask("Overwrite ?", [self::YES, self::NO], self::NO);
			$answ = strtoupper($answ);
			if ($answ==self::NO) {
				return false;
			}
		}
		return true;
	}


	public static function getData(array $conf) : array {
		$DATA = [];
		$DATA['port']= $conf['containerport']; 
		$DATA['debugport'] = self::getDebugPort($conf);
		$DATA['containername'] = $conf['containername'];
		$DATA['network'] = $conf['networkname'];
		$DATA['dir'] = $conf['dir'];
		$DATA['webservice_dev_mode'] = array_key_exists('webservice_dev_mode', $conf) ? $conf['webservice_dev_mode'] : false;

		if ($DATA['webservice_dev_mode']==true) {
			$DATA['webservice_version'] = "@dev";
		} else {
			$DATA['webservice_version'] = "^0.8";
		}


		return $DATA;
	}

	private static function createDirectory(string $dirpath) : void {
		$dirname = basename($dirpath);
		if (!is_dir($dirpath)) {
			echo "creating $dirname  ... ";
			mkdir($dirpath);
			echo self::$LBL_OK;
			echo "\n";
		} 
	}

	private static function valid_php_version() : bool {
		$versi_php_sekarang = phpversion();
		// Membandingkan dengan versi minimal (misalnya, 8.3)
		if (version_compare($versi_php_sekarang, self::MIN_PHP_VERSION, '<')) {
			// echo "<br>Versi PHP Anda terlalu rendah. Minimal dibutuhkan PHP 8.3";
			return false;
		} else {
			// echo "<br>Versi PHP Anda sudah memenuhi syarat (atau lebih tinggi)";
			return true;
		}

	}
}