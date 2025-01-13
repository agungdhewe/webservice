<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;


use AgungDhewe\PhpLogger\Log;

abstract class WebPage {

	const PAGE_NOTFOUND = 'notfound';
	const PAGE_ERROR = 'error';
	const DEFAULT_PAGE = 'page/home';

	private string $_title = '';
	private string $_currentpagedir;

	protected array $_pageData = [];


	abstract static function GetObject(object $obj) ;
	abstract function loadPage(string $requestedPage, array $params) : void;



	public function getPageAssetUrl(string $path) : string {
		Log::Info("get asset $path");

		$rootDir = Configuration::GetRootDir();
		$currentpagedir = $this->getCurrentPageDir();
		$pageAssetPath = str_replace($rootDir, '', $currentpagedir);
		$pageAssetPath = trim($pageAssetPath, '/');
		$baseurl = Service::GetBaseUrl();
		$pageAssetUrl = implode('/', [$baseurl, 'asset', $pageAssetPath, $path]);

		return $pageAssetUrl;
	}


	protected function setTitle(string $text) : void {
		$this->_title = $text;
	}

	public function getTitle() : string {
		return $this->_title;
	}

	public function getTemplate() : IWebTemplate {
		$tpl = Configuration::Get('WebTemplate');
		if (empty($tpl)) {
			$tpl = new PlainTemplate();
			Log::Warning("WebTemplate in Configuration is empty or not defined, using standard PlainTemplate.");
		}
		return $tpl;
	}

	public function getPageData() : array {
		return $this->_pageData;
	}


	public function setPageData(array $data) : void {
		$this->_pageData = $data;
	}

	public function setData(string $key, mixed $value) : void {
		$this->_pageData[$key] = $value;
	}


	public function getData(string $key) : mixed {
		if (!array_key_exists($key, $this->_pageData)) {
			return null;
		} else {
			return $this->_pageData[$key];
		}
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



	protected function renderPage(string $requestedPage, array $PARAMS) : void {
		try {
			$pagesDir = Configuration::Get('PagesDir'); 
			if (empty($pagesDir)) {
				$errmsg = Log::Error("PagesDir in Configuration is empty or not defined");
				throw new \Exception($errmsg, 500);
			}

			$rootDir = Configuration::GetRootDir();
			$pagesDir = implode(DIRECTORY_SEPARATOR, [$rootDir, $pagesDir]);
			$pagefilepath = $this->getPageFilePath($pagesDir, $requestedPage);

			$this->renderPageFile($pagefilepath, $PARAMS);
		} catch (\Exception $ex) {
			Log::Error($ex->getMessage());
			throw $ex;
		}
	}

	protected function renderPageFile(string $pagefilepath, array $PARAMS) : void {
		if (!is_array($PARAMS)) {
			$PARAMS = [];
		}

		try {
			Log::Info("rendering file $pagefilepath");
			if (!is_file($pagefilepath)) {
				$errmsg = Log::Error("File $pagefilepath is not found");
				throw new \Exception($errmsg);
			}
			$this->setCurrentPageDir(dirname($pagefilepath));
			require_once $pagefilepath;
		} catch (\Exception $ex) {
			Log::Error($ex->getMessage());
			throw $ex;
		}
	}


	protected function renderContent(string $text) : void {
		try {
			echo $text;
		} catch (\Exception $ex) {
			$errmsg = Log::Error($ex->getMessage());
			throw new \Exception($errmsg);
		}
	}





}