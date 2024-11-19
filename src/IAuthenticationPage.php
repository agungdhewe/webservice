<?php namespace AgungDhewe\Webservice;

interface IAuthenticationPage extends IWebPage {
	static function getAuthenticationPageObject(object $obj) : IAuthenticationPage;
}