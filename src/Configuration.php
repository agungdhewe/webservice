<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

use AgungDhewe\Setingan\Config;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\PhpLogger\Logger;
use AgungDhewe\PhpLogger\LoggerOutput;

define('DAYS', 86400);
define('HOURS', 3600);
define('MINUTES', 60);


final class Configuration extends Config
{
	const string DB_MAIN = "DbMain";	

	const string DEBUG_CHANNEL_NAME = 'webservice-debug-channel';

	const array DB_PARAM = [
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_PERSISTENT=>true
	];


	public static function SetLogger() : void {
		$logfilename = Configuration::Get("Logger.filename");
		$logfilepath = implode(DIRECTORY_SEPARATOR, [Configuration::GetRootDir(), $logfilename]);
		$maxLogSize = Configuration::Get("Logger.maxLogSize");
		$output = Configuration::Get("Logger.output");
		$debugmode = Configuration::Get("Logger.debug");
		$showCallerFileOnInfo = Configuration::Get("Logger.showCallerFileOnInfo");


		// rotate_log_file, apabila log file telah melebihi ukuran yang ditentukan
		$logSize = filesize($logfilepath);
		if ($logSize > $maxLogSize) {
			$logsArchieveDir = implode(DIRECTORY_SEPARATOR, [Configuration::GetRootDir(), "logs"]);
			if (!is_dir($logsArchieveDir)) {
				mkdir($logsArchieveDir);
			}
			$logsArchieveFileName = "log-" . date('YmdHis') . ".txt";
			$logsArchievePath = implode(DIRECTORY_SEPARATOR, [$logsArchieveDir, $logsArchieveFileName ]);
			copy($logfilepath, $logsArchievePath);
			file_put_contents($logfilepath, "");
		}	

		
		if ($debugmode) {
			$set_debug_mode = false;
			$debug_channel = Configuration::Get("Logger.debugChannel");
			if (empty($debug_channel)) {
				// jika debug channel tidak ditemukan, otomatis debug on;
				$set_debug_mode = true;
			} else {
				// jika debug channel ditemukan, cek apakah channel sesuai dengan header webservice-debug-channel
				$headers = getallheaders();
				if (array_key_exists(self::DEBUG_CHANNEL_NAME, $headers)) {
					Log::info(self::DEBUG_CHANNEL_NAME . ": ".$headers[self::DEBUG_CHANNEL_NAME]);
					if ($headers[self::DEBUG_CHANNEL_NAME] == $debug_channel) {
						$set_debug_mode = true;
					}
				}
			}

			if ($set_debug_mode) {
				Logger::SetDebugMode(true); // set debug mode, clear debug apabila ada parameter $_GET['cleardebug'] = 1
			}
		}

		if ($showCallerFileOnInfo==true) {
			Logger::ShowCallerFileOnInfo(true);
		}

		if ($output == "file") {
			Logger::SetOutput(LoggerOutput::FILE);
		} else if ($output == "none") {
			Logger::SetOutput(LoggerOutput::NONE);
		} 
	}

}