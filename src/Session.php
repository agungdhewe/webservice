<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;


final class Session {

	const string SESSION_NAME = 'sessid';
	const int HANDLER_FILE = 1;
	const int HANDLER_DB = 2;


	private static bool $_session_started = false;
	private static int $_session_handler_by = self::HANDLER_FILE;

	public static function Start() : void {
		
		$sessid = $_COOKIE[self::SESSION_NAME] ?? null;


		if (!self::$_session_started) {
			Log::info("Starting session...");

			// Durasi sesi dalam detik (30 hari)
			$lifetime  = Configuration::Get('Session.Lifetime') ?? 30*DAYS;
			// $lifetime = 30 * 24 * 60 * 60; // 30 hari

			// Mengatur parameter cookie sesi di browser
			session_name(self::SESSION_NAME);
			ini_set('session.gc_maxlifetime',  $lifetime); 
			session_set_cookie_params([
				'lifetime' => $lifetime, 				// Waktu kedaluwarsa
				'path' => '/',           				// Path cookie
				'domain' => Service::getDomainName(), 	// Domain cookie
				'secure' => true,        				// Hanya mengirimkan cookie melalui HTTPS
				'httponly' => true,      				// Mencegah akses melalui JavaScript
				'samesite' => 'Lax',     				// Kebijakan SameSite untuk keamanan tambahan
			]);

			// Memulai sesi
			if ($sessid!=null) {
				session_id($sessid);
			}
			
			session_start();

			self::$_session_started = true;
			Log::info("Session Started.");
		}
	}

	// public static function Activate(string $sessid) : bool {
	// 	if (session_status() !== PHP_SESSION_ACTIVE) {
	// 		session_name(self::SESSION_NAME);
	// 		session_id($sessid);
	// 		session_start();
	// 		return true;
	// 	} else {
	// 		return false;
	// 	}
	// }


	public static function Id() : string {
		return session_id();
	}


	public static function GetUser() : ?User {
		if (self::IsLoggedIn()) {
			$user = new User([
				'id' => $_SESSION['cust_id'],
				'name' => $_SESSION['cust_name'] ,
				'phone' => array_key_exists('cust_phone', $_SESSION) ? $_SESSION['cust_phone'] : null,
				'email' => array_key_exists('cust_email', $_SESSION) ? $_SESSION['cust_email'] : null,
				'gender' => array_key_exists('gender_id', $_SESSION) ? $_SESSION['gender_id'] : null,
				'birthdate' => array_key_exists('cust_birthdate', $_SESSION) ? $_SESSION['cust_birthdate'] : null,
				'custaccess_code' => array_key_exists('custaccess_code', $_SESSION) ? $_SESSION['custaccess_code'] : '',
				'custaccesstype_id' => array_key_exists('custaccesstype_id', $_SESSION) ? $_SESSION['custaccesstype_id'] : '',
				'kalista_sessid' => array_key_exists('kalista_sessid', $_SESSION) ? $_SESSION['kalista_sessid'] : '',
			]);
			return $user;
		} else {
			return null;
		}
	}

	public static function SetUser(?User $user) : void {
		if (!self::IsStarted()) {
			self::Start();
		}


		if ($user!=null) {
			// Logged In
			$_SESSION['is_login'] = true;
			$_SESSION['cust_id'] = $user->Id;
			$_SESSION['cust_name'] = $user->Name;
			$_SESSION['cust_phone'] = $user->Phone;
			$_SESSION['cust_email'] =$user->Email;
			$_SESSION['gender_id'] = $user->Gender;
			$_SESSION['cust_birthdate'] = $user->Birthdate;
			$_SESSION['custaccess_code'] = $user->CustaccessCode;
			$_SESSION['custaccesstype_id'] = $user->CustaccessType;
			$_SESSION['kalista_sessid'] = $user->KalistaSessionId;
		} else {
			// Logged Out
			$_SESSION['is_login'] = false;
			$_SESSION['cust_id'] = null;
			$_SESSION['cust_name'] = null;
			$_SESSION['cust_phone'] = null;
			$_SESSION['cust_email'] = null;
			$_SESSION['gender_id'] = null;
			$_SESSION['cust_birthdate'] = null;
			$_SESSION['custaccess_code'] = null;
			$_SESSION['kalista_sessid'] = null;
		}
	}


	public static function IsLoggedIn() : bool {
		if (array_key_exists('is_login', $_SESSION)) {
			return $_SESSION['is_login'];
		} else {
			return false;
		}
	}

	public static function IsStarted() : bool {
		if (!isset(self::$_session_started)) {
			return false;
		}
		return self::$_session_started;
	}

	public static function SetSessionHandlerBy(int $handlermodel) : void {
		if (self::$_session_handler_by!=self::HANDLER_FILE) {
			$errmsg = Log::Error('session handler mode ' . self::HANDLER_FILE . ' is not implemented yet.');
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
		$errmsg = Log::Error('session handler in DB is not implemented');
		throw new \Exception($errmsg, 500);
	}
}