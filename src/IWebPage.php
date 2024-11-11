<?php namespace AgungDhewe\Webservice;

interface IWebPage {
	public function getTitle() : string;
	public function getData() : array;
	public function getPageAssetUrl(string $path) : string;
	public function getTemplate() : IWebTemplate;
}