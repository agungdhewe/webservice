<?php declare(strict_types=1);
namespace AgungDhewe\Webservice\Routes;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\Webservice\IRouteHandler;
use AgungDhewe\Webservice\Configuration;
use AgungDhewe\Webservice\ServiceRoute;

class TemplateRoute extends AssetRoute implements IRouteHandler {
	function __construct(string $urlreq) {
		parent::__construct($urlreq); // contruct dulu parentnya
	}

	public function route(?array $param = []) : void {
		Log::Info("Route Template $this->urlreq");

		try {

			// get current template renderer
			$tpl = Configuration::Get('WebTemplate');
			if (empty($tpl)) {
				$errmsg = Log::Error("WebTemplate in Configuration is empty or not defined");
				throw new \Exception($errmsg, 500);
			}

			$requestedAsset = ServiceRoute::GetRequestedParameter('template/', $this->urlreq);
			$templatedir = $tpl->GetTemplateDir();
			
			$this->sendAsset($templatedir, $requestedAsset);

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	

}