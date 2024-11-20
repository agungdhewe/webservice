<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;


class PlainTemplate extends WebTemplate {
	const string DEFAULT_NAME = "plaintemplate";

	private ?string $curr_tpldir;
	private ?string $curr_templatename;

	function __construct(?string $tpldir=null, ?string $templatename=null) {
		$this->curr_tpldir = $tpldir;
		$this->curr_templatename = $templatename;

	}

	public function GetName() : string {
		if ($this->curr_templatename!=null) {
			return $this->curr_templatename;
		} else {
			return self::DEFAULT_NAME;
		}
	}

	public function GetTemplateDir() : string {
		$name = $this->GetName();
		if ($this->curr_tpldir==null) {
			$templatedir = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'templates', $name]);
			return $templatedir;
		} else {
			return $this->curr_tpldir;
		}
	}

}