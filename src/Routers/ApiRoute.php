<?php namespace AgungDhewe\Webservice\Routers;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\Webservice\IRouteHandler;
use AgungDhewe\Webservice\ServiceRoute;

class ApiRoute extends ServiceRoute implements IRouteHandler {
	function __construct(string $urlreq) {
		parent::__construct($urlreq); // contruct dulu parentnya
	}

	function route(?array $param = []) : void {
		Log::info("Route Page $this->urlreq");
	}
}

