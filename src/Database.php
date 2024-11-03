<?php namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;

class Database {

	public static function Connect() : void {
		try {
			Log::info("Connecting to database...");

			Log::info("Database Connected.");
		} catch (\Exception $ex) {
			Log::error($ex->getMessage());
			throw new \Exception($ex->getMessage(), 500);
		}
	}
}