<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

interface IUserAuthenticator {
	static function Verify(string $loginname, string $password) : ?string ;
	static function GetUser(string $user_id) : ?User;
}