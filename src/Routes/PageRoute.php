<?php namespace AgungDhewe\Webservice\Routes;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\PhpLogger\Logger;
use AgungDhewe\Webservice\IRouteHandler;
use AgungDhewe\Webservice\ServiceRoute;
use AgungDhewe\Webservice\Configuration;
use AgungDhewe\Webservice\Database;
use AgungDhewe\Webservice\PlainTemplate;
use AgungDhewe\Webservice\WebTemplate;
use AgungDhewe\Webservice\Page;

class PageRoute extends ServiceRoute implements IRouteHandler {

	const PREFIX = 'page';



	private static array $_DATA = [];
	private static object $_TPL;



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
			
			$module = new Page();
			$tpl = $module->getTemplate([]);

			if (!WebTemplate::Validate($tpl)) {
				$tplclassname = get_class($tpl);
				$errmsg = Log::error("Class '$tplclassname' not subclass of WebTemplate");
				throw new \Exception($errmsg, 500);
			}


			

			$content = "";
			try {
				self::SetTemplate($tpl);
				ob_start();

				$requestedPage = ServiceRoute::getRequestedParameter('page/', $this->urlreq);
				$module->LoadPage($requestedPage, $param);
				$data = $module->getData();
				self::SetData($data);

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

	// protected function getContent(string $pagesDir, string $requestedPage, array $CONTENTPARAMS) : string {
	// 	$pagefile = implode(DIRECTORY_SEPARATOR, [$pagesDir, $requestedPage . ".phtml"]);
	// 	if ($requestedPage === self::PAGE_NOTFOUND || $requestedPage === self::PAGE_ERROR) {
	// 		if (!is_file($pagefile)) {
	// 			$pagefile = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'pages', $requestedPage . ".phtml"]);
	// 			$pagefile = realpath($pagefile);
	// 		}
	// 	}
		
		
	// 	Log::info("load '$pagefile'");
	// 	if (!is_file($pagefile)) {
	// 		if ($requestedPage===self::PAGE_ERROR || $requestedPage===self::PAGE_NOTFOUND) {
	// 			throw new \Exception("Internal Error Page '$requestedPage' not available", 500);
	// 		} else {
	// 			Log::error("Page '$requestedPage' not found");
	// 			throw new \Exception("Page '$requestedPage' not found", 4040);
	// 		}
	// 	}

	// 	// 
	// 	try {
	// 		ob_start();
	// 		require_once $pagefile;
	// 		$content = ob_get_contents();
	// 		$success = true;
	// 	} catch (\Exception $ex) {
	// 		$content = $ex->getMessage();
	// 	} finally {
	// 		ob_end_clean();
	// 		if (isset($success)) {
	// 			return $content;
	// 		} else {
	// 			$filename =  basename($pagefile);
	// 			Log::error("Error occured when rendering page file '$filename'");
	// 			$content = fread($pagefile, filesize($pagefile));
	// 			return $content;
	// 		}
	// 	}		
	// }


	public static function ResetDebugOnPageRequest(?array $patterns = ["page/*"]) : void {
		if (getenv('DEBUG')) {
			$urlreq = array_key_exists('urlreq', $_GET) ? trim($_GET['urlreq'], '/') : null;
			if (in_array($urlreq, ['page/error', 'page/notfound'])) {
				return;
			}

 			$defaultPage = Configuration::Get('IndexPage');
			if (empty($defaultPage)) {
				$defaultPage = self::DEFAULT_PAGE;
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


	protected static function SetTemplate(object $tpl) : void {
		self::$_TPL = $tpl;
	}

	public static function GetTemplate() : object {
		return self::$_TPL;
	}

	protected static function SetData(array $data) : void {
		self::$_DATA = $data;
	}

	public static function GetData() : array {
		return self::$_DATA;
	}

}
