<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;

class ContentPage extends WebPage implements IWebPage {
	protected static IContentManagement $cms;

	public static function setContentManagement(IContentManagement $cms) : void {
		self::$cms = $cms;
	}


	public function LoadPage(string $requestedContent, array $params): void {
		try {
			$contentDir = Configuration::Get('ContentDir'); 
			if (empty($contentDir)) {
				// Log::warning("ContentDir in Configuration is empty or not defined, will user 'contents' as directory.");
				$contentDir = "contents";
			}

			$rootDir = Configuration::GetRootDir();
			$contentDir = implode(DIRECTORY_SEPARATOR, [$rootDir, $contentDir]);
			$contentfilepath = $this->getContentFilePath($contentDir, $requestedContent);
			if (is_file($contentfilepath)) {
				Log::info("rendering content file $contentfilepath");
				$this->renderPageFile($contentfilepath, $params);
			} else {
				if (!isset(self::$cms)) {
					Log::warning("Content Management System is not defined!");
					// teruskan error ke user sebaga halaman tidak ditemukan
					throw new \Exception("requested content not found", 4040);
				}
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