<?php namespace AgungDhewe\Webservice;

interface IWebTemplate {
	function getBlockContent(string $blokname) : string;
	function getMainContent() : string;
	function getTemplateAssetUrl(string $path) : string;
	function getTitle() : string;
	function getBaseHref() : string;
	function getUrl(string $path) : string;
}