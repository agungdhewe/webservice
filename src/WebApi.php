<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

abstract class WebApi {
	abstract protected function isNeedVerifierCode(string $name) : bool;
	abstract protected function isNeedAuthentication(string $name) : bool;

	public function webApiVerify(string $name) : void {
		if ($this->isNeedAuthentication($name) && !$this->isValidAuthentication()) {
			throw new \Exception("Authentication failed", 401);
		}

		if ($this->isNeedVerifierCode($name) && !$this->isValidVerifierCode()) {
			throw new \Exception("Verifier code failed", 403);
		}

	}


	public function isValidAuthentication() : bool {
		return true;
	}

	public function isValidVerifierCode() : bool {
		return true;
	}


}