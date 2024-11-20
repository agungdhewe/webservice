<?php declare(strict_types=1);
namespace AgungDhewe\Webservice\Routes;


use AgungDhewe\PhpLogger\Log;
use AgungDhewe\PhpLogger\Logger;
use AgungDhewe\Webservice\IRouteHandler;
use AgungDhewe\Webservice\ServiceRoute;
use AgungDhewe\Webservice\Configuration;
use AgungDhewe\Webservice\Database;
use AgungDhewe\Webservice\IWebPage;
use AgungDhewe\Webservice\IWebTemplate;
use AgungDhewe\Webservice\WebTemplate;
use AgungDhewe\Webservice\Page;
use AgungDhewe\Webservice\Session;
use AgungDhewe\Webservice\WebPage;


class PageRoute extends ServiceRoute implements IRouteHandler {

	const PREFIX = 'page';

	private static array $_PAGEHANDLERS = [];
	private static array $_DATA = [];
	private static object $_TPL;
	
	
	private string $_requestedPrefix = self::PREFIX;


	function __construct(string $urlreq) {
		parent::__construct($urlreq); // contruct dulu parentnya
	}

	public function getRequestedPrefix() : string {
		return $this->_requestedPrefix;
	}

	public function setRequestedPrefix(string $prefix) : void {
		$this->_requestedPrefix = $prefix;
	}




	public function route(?array $param = []) : void {
		Log::info("Route Page $this->urlreq");

		if (array_key_exists('httpheader', $param)) {
			$httpheader = $param['httpheader'];
			header($httpheader);
		}

		
		try {

			Database::Connect();
			Session::Start();

			
			// cek dulu, apakah ada requestedPageClass,
			// kalau tidak ada, cek apakah ada di _PAGEHANDLERS
			// kalau masih tidak ada gunakan default AgungDhewe\Webservice\Page
			if (array_key_exists('requestedPageClass', $param)) {
				$requestedPageClass = $param['requestedPageClass'];
			} else if ($classHandler = $this->getPageHandler($this->urlreq)) {
				$requestedPageClass = $classHandler;
			} else {
				$requestedPageClass = 'AgungDhewe\\Webservice\\Page';
			}
		
			// if (array_key_exists($this->urlreq, self::$_PAGEHANDLERS)) {
			// 	$requestedPageClass = self::$_PAGEHANDLERS[$this->urlreq];
			// } else if (array_key_exists('requestedPageClass', $param)) {
			// 	$requestedPageClass = $param['requestedPageClass'];
			// } else {
			// 	$requestedPageClass = 'AgungDhewe\\Webservice\\Page';
			// }

			// cek apakah class ada
			if (!class_exists($requestedPageClass)) {
				$errmsg = Log::error("Class '$requestedPageClass' is not exists");
				throw new \Exception($errmsg, 500);
			}

			// cek apakah subclass dari WebPage
			if (!is_subclass_of($requestedPageClass, WebPage::class)) {
				$errmsg = Log::error("Class '$requestedPageClass' not subclass of WebPage");
				throw new \Exception($errmsg, 500);
			}

			// cek apakah implementasi WebPage
			if (!in_array(IWebPage::class, class_implements($requestedPageClass))) {
				$errmsg = Log::error("Class '$requestedPageClass' not implements IWebPage");
				throw new \Exception($errmsg, 500);
			}


			$module = new $requestedPageClass();
			$tpl = $module->getTemplate();


			// Validasi Template
			if (!is_subclass_of($tpl, WebTemplate::class)) {
				$tplclassname = get_class($tpl);
				$errmsg = Log::error("Class '$tplclassname' not subclass of WebTemplate");
				throw new \Exception($errmsg, 500);
			}

			if (!in_array(IWebTemplate::class, class_implements($tpl))) {
				$tplclassname = get_class($tpl);
				$errmsg = Log::error("Class '$tplclassname' not implements IWebTemplate");
				throw new \Exception($errmsg, 500);
			}

			

			$content = "";
			try {
				self::SetTemplate($tpl);
				ob_start();

				$requestedPrefix = $this->getRequestedPrefix();
				$requestedPage = ServiceRoute::getRequestedParameter("$requestedPrefix/", $this->urlreq);
				$module->LoadPage($requestedPage, $param);
				$data = $module->getPageData();
				self::SetPageData($data);

				$title = $module->getTitle();
				$tpl->setTitle($title);

				$content = ob_get_contents();
			} catch (\Exception $ex) {
				$errmsg = Log::error($ex->getMessage());
				throw new \Exception($errmsg, $ex->getCode());
			} finally {
				ob_end_clean();
			}
	
			// $content = $this->getContent($pagesDir, $requestedPage, $param);			
			$tpl->Render($content);
		
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public static function ResetDebugOnPageRequest(?array $patterns = ["page/*"]) : void {
		if (getenv('DEBUG')) {
			$urlreq = array_key_exists('urlreq', $_GET) ? trim($_GET['urlreq'], '/') : null;
			if (in_array($urlreq, [Page::PAGE_ERROR, Page::PAGE_NOTFOUND])) {
				return;
			}

 			$defaultPage = Configuration::Get('IndexPage');
			if (empty($defaultPage)) {
				$defaultPage = Page::DEFAULT_PAGE;
			}

			if (empty($urlreq)) {
				$urlreq = $defaultPage;
			}
			
			foreach ($patterns as $pattern) {
				$regexPattern = str_replace('*', '.*', $pattern);
				$regexPattern = str_replace('/', '\/', $regexPattern); // Escape slashes
				if (preg_match("/^$regexPattern$/", $urlreq, $matches)) {
					Logger::clearDebug();
					break;
				}
			}
		}	
	}

	public static function addPageHandler(string $handlername, string $handlerclassname) : void {
		self::$_PAGEHANDLERS[$handlername] = $handlerclassname;
	}

	protected static function SetTemplate(object $tpl) : void {
		self::$_TPL = $tpl;
	}

	public static function GetTemplate() : object {
		return self::$_TPL;
	}

	protected static function SetPageData(array $data) : void {
		self::$_DATA = $data;
	}

	public static function GetPageData() : array {
		return self::$_DATA;
	}


	private function getPageHandler(string $urlreq) : ?string {
		foreach (self::$_PAGEHANDLERS as $pattern => $handlerclassname) {
		if ($urlreq === $pattern) {
				return $handlerclassname;
			}
			if (str_contains($pattern, '*')) {
				$regexPattern = str_replace('*', '.*', $pattern);
				$regexPattern = str_replace('/', '\/', $regexPattern); // Escape slashes
				if (preg_match("/^$regexPattern$/", $urlreq, $matches)) {
					return $handlerclassname;
				}
			}
		}
		return null;
	}

}
