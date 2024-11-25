<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

final class User {
	public readonly string $Id;
	public readonly string $Name;
	public readonly ?string $Phone;
	public readonly ?string $Email;
	public readonly ?string $Gender;
	public readonly ?string $Birthdate;
	public readonly string $CustaccessCode;
	public readonly string $CustaccessType;
	public readonly string $KalistaSessionId;


	function __construct(array $data){
		$this->Id = $data['id'];
		$this->Name = $data['name'];
		$this->Phone = array_key_exists('phone', $data) ? $data['phone'] : null;
		$this->Email = array_key_exists('email', $data) ? $data['email'] : null;
		$this->Gender = array_key_exists('gender', $data) ? $data['gender'] : null;
		$this->Birthdate = array_key_exists('birthdate', $data) ? $data['birthdate'] : null;
		$this->CustaccessCode = array_key_exists('custaccess_code', $data) ? $data['custaccess_code'] : null;
		$this->CustaccessType = array_key_exists('custaccesstype_id', $data) ? $data['custaccesstype_id'] : null;
		$this->KalistaSessionId = array_key_exists('kalista_sessid', $data) ? $data['kalista_sessid'] : null;
	}

}