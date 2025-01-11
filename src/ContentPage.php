<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;

class ContentPage extends WebPage implements IWebPage {
	protected static IContentManagement $cms;

	public static function SetContentManagement(IContentManagement $cms) : void {
		self::$cms = $cms;
	}


	public function loadPage(string $requestedContent, array $params): void {
		try {
			$contentDir = Configuration::Get('ContentDir'); 
			if (empty($contentDir)) {
				$contentDir = "contents";
			}

			$rootDir = Configuration::GetRootDir();
			$contentDir = implode(DIRECTORY_SEPARATOR, [$rootDir, $contentDir]);
			$contentfilepath = $this->getContentFilePath($contentDir, $requestedContent);
			if (is_file($contentfilepath)) {
				Log::Info("rendering content file $contentfilepath");
				$this->renderPageFile($contentfilepath, $params);
			} else {
				if (!isset(self::$cms)) {
					Log::Warning("Content Management System is not defined!");
					// teruskan error ke user sebaga halaman tidak ditemukan
					throw new \Exception("requested content not found", 4040);
				}
				$content = self::$cms::GetContent($requestedContent);
				$title = $content->getTitle();
				$text = $content->getText();
				$this->setTitle($title);
				$this->renderContent($text);
			}
		} catch (\Exception $ex) {
			Log::Error($ex->getMessage());
			throw $ex;
		}
	}

}