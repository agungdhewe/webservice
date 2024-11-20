<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\PhpLogger\Logger;
use AgungDhewe\PhpLogger\LoggerOutput;

final class Configuration
{
	const string SPARATOR = ".";
	const string DB_MAIN = "DbMain";	

	const string DEBUG_CHANNEL_NAME = 'webservice-debug-channel';

	const array DB_PARAM = [
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_PERSISTENT=>true
	];

	private static array $_config;
	private static array $_usedConfig;
	private static string $_rootDir;

	public static function Set(array $config) : void {
		self::$_config = $config;
	}

	public static function Get(?string $keypath = null) : mixed {
		try {
			if ($keypath!==null) {
				$value = self::getValueByPath(self::$_config, $keypath);
				return $value;
			} else {
				return self::$_config;
			}
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public static function GetUsedConfig(string $name) : string {
		if (!array_key_exists($name, self::$_usedConfig)) {
			throw new \Exception("Config '$name' tidak ditemukan");
		}
		return self::$_usedConfig[$name];
	}

	public static function UseConfig(array $usedconfig) : void {
		self::$_usedConfig = $usedconfig;
	}

	private static function getValueByPath(array $array, string $path, ?string $separator = self::SPARATOR) : mixed {
		$keys = explode($separator, $path);
		foreach ($keys as $key) {
			if (!isset($array[$key])) {
				return null; // Kunci tidak ditemukan
			}
			$array = $array[$key]; // Melangkah lebih dalam ke array
		}
		return $array;
	}


	public static function setRootDir($dir) : void {
		if (!defined('__ROOT_DIR__')) {
			define('__ROOT_DIR__', $dir);
		}

		self::$_rootDir = $dir;


		
	}

	public static function getRootDir() : string {
		return self::$_rootDir;
	}


	public static function setLogger() : void {
		$logfilename = Configuration::Get("Logger.filename");
		$logfilepath = implode(DIRECTORY_SEPARATOR, [Configuration::getRootDir(), $logfilename]);
		$maxLogSize = Configuration::Get("Logger.maxLogSize");
		$output = Configuration::Get("Logger.output");
		$debugmode = Configuration::Get("Logger.debug");
		$showCallerFileOnInfo = Configuration::Get("Logger.showCallerFileOnInfo");


		// rotate_log_file, apabila log file telah melebihi ukuran yang ditentukan
		$logSize = filesize($logfilepath);
		if ($logSize > $maxLogSize) {
			$logsArchieveDir = implode(DIRECTORY_SEPARATOR, [Configuration::getRootDir(), "logs"]);
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