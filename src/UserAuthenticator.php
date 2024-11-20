<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

class UserAuthenticator implements IUserAuthenticator {
	public static function Verify(string $loginname, string $password) : ?string {
		$user_id = "xxx";
		return $user_id;
	}

	public static function GetUser(string $user_id) : ?User {
		return null;
	}
}