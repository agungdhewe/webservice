<?php namespace AgungDhewe\Webservice;

interface IWebPage {
	public function getTitle() : string;
	public function getPageData() : array;
	public function getData(string $key) : mixed;
	public function getPageAssetUrl(string $path) : string;
	public function getTemplate() : IWebTemplate;
}