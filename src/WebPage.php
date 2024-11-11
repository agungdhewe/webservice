<?php namespace AgungDhewe\Webservice;


use AgungDhewe\PhpLogger\Log;

abstract class WebPage {

	const PAGE_NOTFOUND = 'notfound';
	const PAGE_ERROR = 'error';
	const DEFAULT_PAGE = 'page/home';

	private string $_title = '';
	private string $_currentpagedir;


	abstract public function LoadPage(string $requestedPage, array $params) : void;


	protected function setTitle(string $text) : void {
		$this->_title = $text;
	}

	


	public static function getPageObject($obj) : IWebPage  {
		return $obj;
	}



	public function getPageAssetUrl(string $path) : string {
		Log::info("get asset $path");

		$rootDir = Configuration::getRootDir();
		$currentpagedir = $this->getCurrentPageDir();
		$pageAssetPath = str_replace($rootDir, '', $currentpagedir);
		$pageAssetPath = trim($pageAssetPath, '/');
		$baseurl = ServiceRoute::getBaseUrl();
		$pageAssetUrl = implode('/', [$baseurl, 'asset', $pageAssetPath, $path]);

		return $pageAssetUrl;
	}

	public function getTitle() : string {
		return $this->_title;
	}

	public function getTemplate() : IWebTemplate {
		$tpl = Configuration::Get('WebTemplate');
		if (empty($tpl)) {
			$tpl = new PlainTemplate();
			Log::warning("WebTemplate in Configuration is empty or not defined, using standard PlainTemplate.");
		}
		return $tpl;
	}

	public function getData() : array {
		return [];
	}


	protected function getContentFilePath(string $contentsDir, string $requestedContent) : string {
		$pagefilepath = implode(DIRECTORY_SEPARATOR, [$contentsDir, $requestedContent . ".phtml"]);
		return $pagefilepath;
	}


	protected function getPageFilePath(string $pagesDir, string $requestedPage) : string {
		$pagefilepath = implode(DIRECTORY_SEPARATOR, [$pagesDir, $requestedPage . ".phtml"]);
		if ($requestedPage === self::PAGE_NOTFOUND || $requestedPage === self::PAGE_ERROR) {
			if (!is_file($pagefilepath)) {
				$pagefilepath = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'pages', $requestedPage . ".phtml"]);
				$pagefilepath = realpath($pagefilepath);
			}
		}

		if (!is_file($pagefilepath)) {
			if ($requestedPage===self::PAGE_ERROR || $requestedPage===self::PAGE_NOTFOUND) {
				throw new \Exception("Internal Error Page '$requestedPage' not available", 500);
			} else {
				Log::error("Page '$requestedPage' not found");
				throw new \Exception("Page '$requestedPage' not found", 4040);
			}
		}
		return $pagefilepath;
	}

	protected function setCurrentPageDir(string $dir) : void {
		$this->_currentpagedir = $dir;
	}

	protected function getCurrentPageDir() : string {
		return $this->_currentpagedir;
	}


	protected function renderPageFile(string $pagefilepath, array $PARAMS) : void {
		if (!is_array($PARAMS)) {
			$PARAMS = [];
		}
		
		
		try {
			if (!is_file($pagefilepath)) {
				$errmsg = Log::error("File $pagefilepath is not found");
				throw new \Exception($errmsg);
			}
			$this->setCurrentPageDir(dirname($pagefilepath));
			
			$tpl = $this->getTemplate();
			$page = $this;

			require_once $pagefilepath;
		} catch (\Exception $ex) {
			$errmsg = Log::error($ex->getMessage());
			throw new \Exception($errmsg);
		}
	}


	protected function renderContent(string $text) : void {
		try {
			echo $text;
		} catch (\Exception $ex) {
			$errmsg = Log::error($ex->getMessage());
			throw new \Exception($errmsg);
		}
	}





}