<?php namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;

class Database {

	public static bool $_database_connected = false;


	public static function Connect() : void {
		try {
			if (!self::$_database_connected) {
				Log::info("Connecting to database...");



				self::$_database_connected = true;
				Log::info("Database Connected.");
			}
		} catch (\Exception $ex) {
			Log::error($ex->getMessage());
			throw new \Exception($ex->getMessage(), 500);
		}
	}
}