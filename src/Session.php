<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;


final class Session {

	const string SESSION_NAME = 'sessid';
	const int HANDLER_FILE = 1;
	const int HANDLER_DB = 2;


	private static bool $_session_started = false;
	private static int $_session_handler_by = self::HANDLER_FILE;
	private static User $_user;

	public static function Start() : void {
		
		$sessid = $_COOKIE[self::SESSION_NAME] ?? null;


		if (!self::$_session_started) {
			Log::info("Starting session...");

			// Durasi sesi dalam detik (30 hari)
			$lifetime = 30 * 24 * 60 * 60; // 30 hari

			// Mengatur parameter cookie sesi di browser
			session_name(self::SESSION_NAME);
			session_set_cookie_params([
				'lifetime' => $lifetime, 				// Waktu kedaluwarsa
				'path' => '/',           				// Path cookie
				'domain' => Service::getDomainName(), 	// Domain cookie
				'secure' => true,        				// Hanya mengirimkan cookie melalui HTTPS
				'httponly' => true,      				// Mencegah akses melalui JavaScript
				'samesite' => 'Lax',     				// Kebijakan SameSite untuk keamanan tambahan
			]);

			// Memulai sesi
			if ($sessid==null) {
				$sessid=uniqid();
			}

			session_id($sessid);
			session_start();

			self::$_session_started = true;
			Log::info("Session Started.");
		}
	}

	public static function Id() : string {
		return session_id();
	}


	public static function GetUser() : ?User {
		if (isset(self::$_user)) {
			return self::$_user;
		} else {
			return null;
		}
	}

	public static function SetUser(User $user) : void {
		self::$_user = $user;
	}


	public static function IsLoggedIn() : bool {
		return false;
	}

	public static function IsStarted() : bool {
		if (!isset(self::$_session_started)) {
			return false;
		}
		return self::$_session_started;
	}

	public static function SetSessionHandlerBy(int $handlermodel) : void {
		if (self::$_session_handler_by!=self::HANDLER_FILE) {
			$errmsg = Log::error('session handler mode ' . self::HANDLER_FILE . ' is not implemented yet.');
			throw new \Exception($errmsg, 500);
		}
		self::$_session_handler_by = $handlermodel;
	}

	public static function IsExists(string $sessid) : bool {
		if (self::$_session_handler_by==self::HANDLER_DB) {
			return self::IsExistInDb($sessid);
		} else {
			return self::IsExistInFile( $sessid);
		}
	} 

	private static function IsExistInFile(string $sessid) : bool {
		$sessionPath = ini_get('session.save_path');
		$sessionFile = $sessionPath . DIRECTORY_SEPARATOR . "sess_$sessid";
		if (file_exists($sessionFile)) {
			return true;
		} else {
			return false;
		}
	}

	private static function IsExistInDb(string $sessid) : ?bool {
		$errmsg = Log::error('session handler in DB is not implemented');
		throw new \Exception($errmsg, 500);
	}
}