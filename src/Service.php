<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\Webservice\Routes\PageRoute;


final class Service {

	const int MAX_ITER = 5;

	const array HTTP_ERROR_LIST = [
		'400' => ['400', 'Bad Request'],
		'401' => ['401', 'Unauthorized'],
		'403' => ['403', 'Forbidden'],
		'405' => ['405', 'Method Not Allowed'],
		'404' => ['404', 'Not Found'],
		'500' => ['500', 'Internal Error'],
	];

	public static function Main() {
		$urlreq = array_key_exists('urlreq', $_GET) ? trim($_GET['urlreq'], '/') : null;
		$param = [];


		$iter = 0;
		$routeComplete = false;
		while (!$routeComplete) {
			$iter++;
			try {
				if ($iter > self::MAX_ITER) {
					$errmsg = Log::Error("Maximum iteration reached");
					throw new \Exception($errmsg, 500);
				}

				
				$routehandler = Router::createHandle($urlreq);
				
				$param['urlreq'] = $urlreq;
				$routehandler->route($param);
				$routeComplete = true;
			} catch (\Exception $ex) {
				$errCode = $ex->getCode();
				if (in_array($errCode, [400, 401, 403, 404, 405, 500])) {
					throw $ex;	
				} else if ($errCode==4040) {
					$param = [
						'errormessage' => $ex->getMessage(),
						'errorcode' => $ex->getCode(),
						'httpheader' => 'HTTP/1.1 404 Not Found',
					];
					$urlreq = implode('/', [PageRoute::PREFIX, WebPage::PAGE_NOTFOUND]);
				} else {
					$param = [
						'errormessage' => $ex->getMessage(),
						'errorcode' => $ex->getCode(),
					];
					$urlreq =  implode('/', [PageRoute::PREFIX, WebPage::PAGE_ERROR]);
				}
			} 
		}


		
	}

	public static function HandleHttpException(\Exception $ex) {
		$errCode = (string)$ex->getCode();
		if (array_key_exists($errCode, self::HTTP_ERROR_LIST)) {
			$err = self::HTTP_ERROR_LIST[$errCode];
			$httpErrorCode = $err[0];
			$httpErrorName = $err[1];
			header("HTTP/1.1 $httpErrorCode $httpErrorName");
			echo "<h1>$httpErrorCode $httpErrorName</h1>";
		} else {
			header("HTTP/1.1 500 Internal Error");
			echo "<h1>500 Internal Error</h1>";
		}

		echo $ex->getMessage();
		echo "<hr>";
		echo "<small><a href=\"https://github.com/agungdhewe/webservice\">AgungDhewe PHP Webservice</a></small>\n";
	}

	public static function GetBaseUrl() : string {
		$headers = getallheaders(); 
		if (array_key_exists('BASE_HREF', $headers)) {
			return trim($headers['BASE_HREF'], '/');
		} else if (!empty($baseUrl=Configuration::Get('BaseUrl'))) {
			return $baseUrl;
		} else {
			return $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'];
		}
	}

	public static function GetDomainName() : string {
		$baseurl = self::getBaseUrl();
		$host = parse_url($baseurl, PHP_URL_HOST);
		return $host;
	}
}