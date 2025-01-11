<?php namespace AgungDhewe\Webservice;

interface IAuthenticationPage extends IWebPage {
	static function GetPageObject(object $obj) : IAuthenticationPage;
}