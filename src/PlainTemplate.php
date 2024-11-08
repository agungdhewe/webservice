<?php namespace AgungDhewe\Webservice;


class PlainTemplate extends WebTemplate {
	const string NAME = "plaintemplate";

	private ?string $curr_tpldir;

	function __construct(?string $tpldir=null) {
		$this->curr_tpldir = $tpldir;
	}

	public function GetName() : string {
		return self::NAME;
	}

	public function GetTemplateDir() : string {
		if ($this->curr_tpldir==null) {
			$name = $this->GetName();
			$templatedir = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'templates', $name]);
			return $templatedir;
		} else {
			return $this->curr_tpldir;
		}
	}

}