<?php namespace AgungDhewe\Webservice;

interface IContentManagement {
	public static function getContent(string $requestedContent) : Content; 
}