<?php namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\Webservice\Routes\PageRoute;
use AgungDhewe\Webservice\Routes\TemplateRoute;
use AgungDhewe\Webservice\Routes\AssetRoute;
use AgungDhewe\Webservice\Routes\ContentRoute;
use AgungDhewe\Webservice\Routes\ApiRoute;


class Router {

	private static $GROUTES = [];
	private static $PROUTES = [];


	public static function GET(string $path, string $serviceRouteClassName) : void {
		if (array_key_exists($path, self::$GROUTES)) {
			Log::warning("override GET route for existing path '$path");
		}

		self::$GROUTES[$path] = [
			"classname" => $serviceRouteClassName
		];
	}

	public static function POST(string $path, string $serviceRouteClassName) : void {
		if (array_key_exists($path, self::$PROUTES)) {
			Log::warning("override POST route for existing path '$path");
		}
		self::$PROUTES[$path] = [
			"classname" => $serviceRouteClassName
		];
	}


	public static function setupDefaultRoutes() : void {
		self::GET('template/*', TemplateRoute::class);
		self::GET('asset/*', AssetRoute::class);
		self::GET('page/*', PageRoute::class);
		self::GET('content/*', ContentRoute::class);		// Content
		self::POST('api/*', ApiRoute::class);
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


	public static function createHandle(?string &$urlreq) : IRouteHandler {	
		$REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
		
		if ($urlreq==null) {
			$indexpage = Configuration::Get('IndexPage');
			if (!empty($indexpage)) {
				$urlreq = $indexpage;
			} else {
				$urlreq = WebPage::DEFAULT_PAGE;
			}
			
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
				$urlreq = join('/', [PageRoute::PREFIX, $urlreq]);
				$routedata = ['classname' => PageRoute::class];
			} else {
				$errmsg = Log::error("$REQUEST_METHOD request to '$urlreq' is not allowed");
				throw new \Exception($errmsg, 405);
			}
		}

		$classname = $routedata['classname'];
		if (!class_exists($classname)) {
			$errmsg = Log::error("Class '$classname' not found");
			throw new \Exception($errmsg, 500);
		}
		
		// check if class implements IRouteHandler
		if (!in_array(IRouteHandler::class, class_implements($classname))) {
			$errmsg = Log::error("Class '$classname' not implements IRouteHandler");
			throw new \Exception($errmsg, 500);
		}

		// check if class is subclass of ServiceRoute
		if (!is_subclass_of($classname, ServiceRoute::class)) {
			$errmsg = Log::error("Class '$classname' not subclass of ServiceRoute");
			throw new \Exception($errmsg, 500);
		}

		$route = new $classname($urlreq);
		return $route;
	}
}