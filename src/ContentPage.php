<?php namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;

class ContentPage extends WebPage {
	protected static IContentManagement $cms;

	public static function setContentManagement(IContentManagement $cms) : void {
		self::$cms = $cms;
	}


	public function LoadPage(string $requestedContent, array $params): void {
		try {
			$contentDir = Configuration::Get('ContentDir'); 
			if (empty($contentDir)) {
				Log::warning("ContentDir in Configuration is empty or not defined");
			}

			$rootDir = Configuration::getRootDir();
			$contentDir = implode(DIRECTORY_SEPARATOR, [$rootDir, $contentDir]);
			$contentfilepath = $this->getContentFilePath($contentDir, $requestedContent);
			if (is_file($contentfilepath)) {
				Log::info("rendering content file $contentfilepath");
				$this->renderPageFile($contentfilepath, $params);
			} else {
				$content = self::$cms::getContent($requestedContent);
				$title = $content->getTitle();
				$text = $content->getText();
				$this->setTitle($title);
				$this->renderContent($text);
			}
		} catch (\Exception $ex) {
			Log::error($ex->getMessage());
			throw $ex;
		}
	}

}