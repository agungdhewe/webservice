<?php namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\Webservice\Routers\PageRoute;


class Service {

	const MAX_ITER = 5;

	public static function main() {
		$urlreq = array_key_exists('urlreq', $_GET) ? trim($_GET['urlreq'], '/') : null;
		$param = [];
		$url = $urlreq;

		Log::info("Starting Service...");
		$iter = 0;
		$routeComplete = false;
		while (!$routeComplete) {
			$iter++;
			try {
				if ($iter > self::MAX_ITER) {
					throw new \Exception("Maximum iteration reached", 500);
				}

				$routehandler = Router::createHandle($urlreq);
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
					$urlreq = implode('/', [PageRoute::PREFIX, PageRoute::PAGE_NOTFOUND]);
				} else {
					$param = [
						'errormessage' => $ex->getMessage(),
						'errorcode' => $ex->getCode(),
					];
					$urlreq =  implode('/', [PageRoute::PREFIX, PageRoute::PAGE_ERROR]);
				}
			}
		}
	}

	public static function handleHttpException(\Exception $ex) {
		Log::error($ex->getMessage());
		$errCode = (string)$ex->getCode();
		$httpErrorList = [
			'400' => ['400', 'Bad Request'],
			'401' => ['401', 'Unauthorized'],
			'403' => ['403', 'Forbidden'],
			'405' => ['405', 'Method Not Allowed'],
			'404' => ['404', 'Not Found']
		];

		if (array_key_exists($errCode, $httpErrorList)) {
			$err = $httpErrorList[$errCode];
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
}