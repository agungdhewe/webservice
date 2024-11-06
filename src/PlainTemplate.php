<?php namespace AgungDhewe\Webservice;


class PlainTemplate extends WebTemplate {
	const string NAME = "plaintemplate";

	public function GetName() : string {
		return self::NAME;
	}

	public function GetTemplateDir() : string {
		$name = $this->GetName();
		$rootDir = Configuration::getRootDir();
		$templatedir = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'templates', $name]);
		return $templatedir;
	}

}