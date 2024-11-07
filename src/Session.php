<?php namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;


class Session {

	private static bool $_session_started = false;

	public static function Start() : void {
		if (!self::$_session_started) {
			Log::info("Starting session...");



			self::$_session_started = true;
			Log::info("Session Started.");
		}
	}
}