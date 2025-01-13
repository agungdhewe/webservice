<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;


use AgungDhewe\PhpLogger\Log;

class Page extends WebPage  {

	public static function GetObject(object $obj) : Page {
		return $obj;
	}

	public function loadPage(string $requestedPage, array $params) : void {
		try {
			parent::loadPage($requestedPage, $params);
		} catch (\Exception $ex) {
			Log::Error($ex->getMessage());
			throw $ex;
		}
	}


	


}