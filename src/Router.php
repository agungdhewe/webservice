<?php namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;
use \AgungDhewe\Webservice\Routers\PageRoute;


class Router {

	private static $GROUTES = [];
	private static $PROUTES = [];


	public static function GET(string $path, string $serviceRouteClassName) : void {
		if (!array_key_exists($path, self::$GROUTES)) {
			self::$GROUTES[$path] = [
				"classname" => $serviceRouteClassName
			];
		}
	}

	public static function POST(string $path, string $serviceRouteClassName) : void {
		if (!array_key_exists($path, self::$PROUTES)) {
			self::$PROUTES[$path] = [
				"classname" => $serviceRouteClassName
			];
		}
	}


	public static function getRouteData(?string $url, array $routers): ?array {
		foreach ($routers as $pattern => $routedata) {
			// Check for exact match
			if ($url === $pattern) {
				return $routedata;
			}
	
			// Check for wildcard match, handling slashes correctly
			if (str_contains($pattern, '*')) {
				$regexPattern = str_replace('*', '.*', $pattern);
				$regexPattern = str_replace('/', '\/', $regexPattern); // Escape slashes
				if (preg_match("/^$regexPattern$/", $url, $matches)) {
					return $routedata;
				}
			}
		}
		return null; // No matching routedata found
	}


	public static function createHandle(?string $urlreq) : IRouteHandler {	
		$REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
		
		if ($urlreq==null) {
			$urlreq = 'page/home';
		}

		if ($REQUEST_METHOD==='GET') {
			Log::info("parse url GET '$urlreq'");
			$routes = self::$GROUTES;			
		} else {
			Log::info("parse url $REQUEST_METHOD '$urlreq'");
			$routes = self::$PROUTES;
		}
	
		$routedata = self::getRouteData($urlreq, $routes);	
		if ($routedata==null) {
			if ($REQUEST_METHOD==='GET') {
				$routedata = ['classname' => 'AgungDhewe\Webservice\Routers\PageRoute'];
			} else {
				throw new \Exception("$REQUEST_METHOD request to '$urlreq' is not allowed", 405);
			}
		}

		$classname = $routedata['classname'];
		if (!class_exists($classname)) {
			throw new \Exception("Class '$classname' not found", 500);
		}
		
		// check if class implements IRouteHandler
		if (!in_array(IRouteHandler::class, class_implements($classname))) {
			throw new \Exception("Class '$classname' not implements IRouteHandler", 500);
		}

		// check if class is subclass of ServiceRoute
		if (!is_subclass_of($classname, ServiceRoute::class)) {
			throw new \Exception("Class '$classname' not subclass of ServiceRoute", 500);
		}

		$route = new $classname($urlreq);
		return $route;
	}
}