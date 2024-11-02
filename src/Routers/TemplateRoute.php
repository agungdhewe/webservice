<?php namespace AgungDhewe\Webservice\Routers;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\Webservice\IRouteHandler;
use AgungDhewe\Webservice\ServiceRoute;
use AgungDhewe\Webservice\Configuration;

class TemplateRoute extends AssetRoute implements IRouteHandler {
	function __construct(string $urlreq) {
		parent::__construct($urlreq); // contruct dulu parentnya
	}

	function route(?array $param = []) : void {
		Log::info("Route Page $this->urlreq");

		try {

			// get current template renderer
			$tpl = Configuration::Get('WebTemplate');
			if (empty($tpl)) {
				throw new \Exception("WebTemplate in Configuration is empty or not defined", 500);
			}

			$requestedAsset = $this->getRequestedParameter('template/', $this->urlreq);
			$templatedir = $tpl->GetTemplateDir();
			
			$this->sendAsset($templatedir, $requestedAsset);

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	

}