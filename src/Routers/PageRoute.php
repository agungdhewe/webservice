<?php namespace AgungDhewe\Webservice\Routers;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\Webservice\IRouteHandler;
use AgungDhewe\Webservice\ServiceRoute;
use AgungDhewe\Webservice\Configuration;
use AgungDhewe\Webservice\Database;


class PageRoute extends ServiceRoute implements IRouteHandler {

	const PREFIX = 'page';
	const PAGE_NOTFOUND = 'notfound';
	const PAGE_ERROR = 'error';

	function __construct(string $urlreq) {
		parent::__construct($urlreq); // contruct dulu parentnya
	}

	function route(?array $param = []) : void {
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
				throw new \Exception("PagesDir in Configuration is empty or not defined", 500);
			}
	
			// get template renderer
			$tpl = Configuration::Get('WebTemplate');
			if (empty($tpl)) {
				throw new \Exception("WebTemplate in Configuration is empty or not defined", 500);
			}

			$rootDir = Configuration::getRootDir();
			$pagesDir = implode('/', [$rootDir, $pagesDir]);
			$requestedPage = $this->getRequestedParameter('page/', $this->urlreq);
			$content = $this->getContent($pagesDir, $requestedPage, $param);
			$tpl->Render($content);
		
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function getContent(string $pagesDir, string $requestedPage, array $CONTENTPARAMS) : string {
		$pagefile = implode('/', [$pagesDir, $requestedPage . ".phtml"]);
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


}
