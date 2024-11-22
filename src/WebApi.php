<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

abstract class WebApi {
	private WebApi $_api;


	public function isNeedSession(string $name) : bool {
		return false;
	}

	public function isNeedVerifierCode(string $name) : bool {
		return false;
	}
	
	public function isNeedAuthenticatedUser(string $name) : bool {
		return false;
	}
	
	public function isNeedAuthenticatedApps(string $name, ?array $apps = []) : bool {
		return false;
	}



	public function isValidVerifierCode() : bool {
		// TODO: buat validasi kode verifier
		return true;
	}

	public function isValidUser() : bool {
		if (!Session::IsLoggedIn()) {  // cek apakah Session::isLoggedIn() === true ?
			return false;
		} else {
			return true;
		}
	}

	public function isValidApps() : bool {
		// TODO: buat valid applikasi
		$apps_id = "xxx"; // ambil dari header
		$apps_secret = "yyy"; // ambil dari header
		return true;
	}




	public final function setCurrentApi(WebApi $api) : void {
		$this->_api = $api;
	}

	public function webApiVerify(string $name) : void {
		$api = $this->_api;

		if ($api->isNeedAuthenticatedUser($name)) {
			if (!$api->isValidUser()) {
				throw new \Exception("User Authentication failed", 401);
			}
		}

		if ($api->isNeedAuthenticatedApps($name)) {
			if (!$api->isValidApps()) {
				throw new \Exception("Apps Authentication failed", 401);
			}
		}

		if ($api->isNeedVerifierCode($name)) {
			if (!$api->isValidVerifierCode()) {
				throw new \Exception("Verifier code failed", 403);
			}
		}

	}





}