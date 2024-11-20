<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;


use AgungDhewe\PhpLogger\Log;

class Page extends WebPage implements IWebPage {

	public function LoadPage(string $requestedPage, array $params) : void {
		try {
			$pagesDir = Configuration::Get('PagesDir'); 
			if (empty($pagesDir)) {
				$errmsg = Log::error("PagesDir in Configuration is empty or not defined");
				throw new \Exception($errmsg, 500);
			}

			$rootDir = Configuration::getRootDir();
			$pagesDir = implode(DIRECTORY_SEPARATOR, [$rootDir, $pagesDir]);
			$pagefilepath = $this->getPageFilePath($pagesDir, $requestedPage);

			// $this->setTitle("Default Halaman");

			Log::info("rendering file $pagefilepath");
			$this->renderPageFile($pagefilepath, $params);
		} catch (\Exception $ex) {
			Log::error($ex->getMessage());
			throw $ex;
		}
	}


	


}