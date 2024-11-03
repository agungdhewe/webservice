<?php namespace AgungDhewe\Webservice;


class PlainTemplate extends WebTemplate {
	const string NAME = "plaintemplate";


	


	public function GetTemplateFilepath() : string {
		$templatedir = $this->GetTemplateDir();
		$templatefile = implode('/', [$templatedir, self::NAME . '.phtml']);
		return $templatefile;
	}

	public function GetTemplateDir() : string {
		$rootDir = Configuration::getRootDir();
		$templatedir = implode('/', [$rootDir, 'templates', self::NAME]);
		return $templatedir;
	}

	public function GetAssetUrl() : string {
		return 'assets/';
	}





}