<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;

final class Database {

	public static bool $_database_connected = false;


	public static function Connect() : void {
		try {
			if (!self::$_database_connected) {
				Log::Info("Connecting to database...");



				self::$_database_connected = true;
				Log::Info("Database Connected.");
			}
		} catch (\Exception $ex) {
			Log::Error($ex->getMessage());
			throw new \Exception($ex->getMessage(), 500);
		}
	}
}