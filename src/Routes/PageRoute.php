<?php namespace AgungDhewe\Webservice\Routes;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\Webservice\IRouteHandler;
use AgungDhewe\Webservice\ServiceRoute;
use AgungDhewe\Webservice\Configuration;
use AgungDhewe\Webservice\Database;
use AgungDhewe\Webservice\WebTemplate;

class PageRoute extends ServiceRoute implements IRouteHandler {

	const PREFIX = 'page';
	const PAGE_NOTFOUND = 'notfound';
	const PAGE_ERROR = 'error';
	const DEFAULT_PAGE = 'page/home';

	function __construct(string $urlreq) {
		parent::__construct($urlreq); // contruct dulu parentnya
	}

	public function route(?array $param = []) : void {
		Log::info("Route Page $this->urlreq");

		if (array_key_exists('httpheader', $param)) {
			$httpheader = $param['httpheader'];
			header($httpheader);
		}

		
		try {

			Database::Connect();

			// get pages directory
			$pagesDir = Configuration::Get('PagesDir'); 
			if (empty($pagesDir)) {
				$errmsg = Log::error("PagesDir in Configuration is empty or not defined");
				throw new \Exception($errmsg, 500);
			}
	
			// get template renderer
			$tpl = Configuration::Get('WebTemplate');
			if (empty($tpl)) {
				$errmsg = Log::error("WebTemplate in Configuration is empty or not defined");
				throw new \Exception($errmsg, 500);
			}

			// cek apakah $tpl inherit dari WebTemplate
			if (!is_subclass_of($tpl, WebTemplate::class)) {
				$tplclassname = get_class($tpl);
				$errmsg = Log::error("Class '$tplclassname' not subclass of WebTemplate");
				throw new \Exception($errmsg, 500);
			}


			$rootDir = Configuration::getRootDir();
			$pagesDir = implode('/', [$rootDir, $pagesDir]);
			$requestedPage = ServiceRoute::getRequestedParameter('page/', $this->urlreq);
			$content = $this->getContent($pagesDir, $requestedPage, $param);
			$tpl->Render($content);
		
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	protected function getContent(string $pagesDir, string $requestedPage, array $CONTENTPARAMS) : string {
		$pagefile = implode('/', [$pagesDir, $requestedPage . ".phtml"]);
		if ($requestedPage === self::PAGE_NOTFOUND || $requestedPage === self::PAGE_ERROR) {
			if (!is_file($pagefile)) {
				$pagefile = implode('/', [__DIR__, '..', '..', 'pages', $requestedPage . ".phtml"]);
			}
		}
		
		
		Log::info("load '$pagefile'");
		if (!is_file($pagefile)) {
			if ($requestedPage===self::PAGE_ERROR || $requestedPage===self::PAGE_NOTFOUND) {
				throw new \Exception("Internal Error Page '$requestedPage' not available", 500);
			} else {
				Log::error("Page '$requestedPage' not found");
				throw new \Exception("Page '$requestedPage' not found", 4040);
			}
		}

		// 
		try {
			ob_start();
			require_once $pagefile;
			$content = ob_get_contents();
			$success = true;
		} catch (\Exception $ex) {
			$content = $ex->getMessage();
		} finally {
			ob_end_clean();
			if (isset($success)) {
				return $content;
			} else {
				$filename =  basename($pagefile);
				Log::error("Error occured when rendering page file '$filename'");
				return "";
			}
		}		
	}


	public static function ResetDebugOnPageRequest() {
		if (getenv('DEBUG')) {
			$urlreq = array_key_exists('urlreq', $_GET) ? trim($_GET['urlreq'], '/') : self::DEFAULT_PAGE;
			$pattern = "page/*";
			$regexPattern = str_replace('*', '.*', $pattern);
			$regexPattern = str_replace('/', '\/', $regexPattern); // Escape slashes
			if (preg_match("/^$regexPattern$/", $urlreq, $matches)) {
				$_GET['cleardebug'] = 1;
			}
		}	
	}


}
