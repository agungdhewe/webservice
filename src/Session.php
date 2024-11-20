<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;


final class Session {

	const string SESSION_NAME = 'sessid';

	private static bool $_session_started = false;
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

}