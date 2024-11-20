<?php declare(strict_types=1);
namespace AgungDhewe\Webservice\Routes;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\Webservice\IRouteHandler;
use AgungDhewe\Webservice\Database;
use AgungDhewe\Webservice\Session;

use AgungDhewe\Webservice\ContentPage;

class ContentRoute extends PageRoute implements IRouteHandler {

	const PREFIX = 'page';

	function __construct(string $urlreq) {
		parent::__construct($urlreq); // contruct dulu parentnya
		
	}

	public function route(?array $param = []) : void {
		Log::info("Route Content $this->urlreq");

		try {
			Database::Connect();
			Session::Start();

			$param['requestedPageClass'] = ContentPage::class;
		
			parent::setRequestedPrefix('content');
			parent::route($param);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


}
