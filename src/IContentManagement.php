<?php namespace AgungDhewe\Webservice;

interface IContentManagement {
	public static function GetContent(string $requestedContent) : Content; 
}