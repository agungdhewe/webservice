<?php namespace AgungDhewe\Webservice;

interface IAuthenticationPage extends IWebPage {
	static function getPageObject(object $obj) : IAuthenticationPage;
}