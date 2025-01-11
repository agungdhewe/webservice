<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;


use AgungDhewe\PhpLogger\Log;

class Page extends WebPage implements IWebPage {

	public function loadPage(string $requestedPage, array $params) : void {
		try {
			$pagesDir = Configuration::Get('PagesDir'); 
			if (empty($pagesDir)) {
				$errmsg = Log::Error("PagesDir in Configuration is empty or not defined");
				throw new \Exception($errmsg, 500);
			}

			$rootDir = Configuration::GetRootDir();
			$pagesDir = implode(DIRECTORY_SEPARATOR, [$rootDir, $pagesDir]);
			$pagefilepath = $this->getPageFilePath($pagesDir, $requestedPage);

			// $this->setTitle("Default Halaman");

			Log::Info("rendering file $pagefilepath");
			$this->renderPageFile($pagefilepath, $params);
		} catch (\Exception $ex) {
			Log::Error($ex->getMessage());
			throw $ex;
		}
	}


	


}