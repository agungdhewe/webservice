<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;



abstract class WebTemplate {

	private string $_title = "default page title";
	private string $_mainContent;
	private array $_blocks;

	
	abstract static function GetObject(object $tpl);
	abstract function getName() : string;
	abstract function getTemplateDir() : string;

	public static function RemoveCommentBlocks(string $content) : string {
		$cleaned_content = preg_replace('/\{\*.*?\*\}/s', '', $content);
		return $cleaned_content;
	}

	public static function ParseMainContent(string $content) : string {
		$pattern = '/\{block name="[^"]*".*?{\/block}/s';
		$content_without_blocks = preg_replace($pattern, '', $content);
		$content_without_blocks = trim($content_without_blocks);
		return $content_without_blocks;
	} 

	public static function ParseBlocks(string $content) : array {
		$blocks = [];
		$pattern_block = '/\{block name="([^"]+)"\}(.*?)\{\/block\}/s';
		preg_match_all($pattern_block, $content, $matches);
		for ($i = 0; $i < count($matches[1]); $i++) {
			$blocks[$matches[1][$i]] = trim($matches[2][$i]);
		}
		return $blocks;
	}


	public function getMainContent() : string {
		return $this->_mainContent;
	}

	public function getBlockContent(string $blokname) : string {
		if (array_key_exists($blokname, $this->_blocks)) {
			return $this->_blocks[$blokname];
		} else {
			return '';
		}
	}

	protected function include(string $filename, ?string $dir = null) : void {
		$templatedir = $this->getTemplateDir();
		if ($dir === null) {
			$dir = $templatedir;
		} 
		
		if (!is_dir($dir)) {
			$errmsg = Log::Error("Directory '$dir' not found");
			throw new \Exception($errmsg, 500);
		}

		$filepath = implode(DIRECTORY_SEPARATOR, [$dir, $filename]);
		if (!is_file($filepath)) {
			$errmsg = Log::Error("File '$filepath' not found");
			throw new \Exception($errmsg, 500);
		}

		require_once $filepath;
	}


	public function setTitle(string $title) : void {
		$this->_title = $title;
	}

	public function getTitle() : string {
		if (!empty($this->_title)) {
			return $this->_title;
		} else if (array_key_exists('title', $this->_blocks)) {
			return $this->_blocks['title'];
		} else {
			return "";
		}
	}


	public function getBaseHref() : string {
		$baseurl = Service::GetBaseUrl();
		return "$baseurl/";
	}

	public function getUrl(string $path) : string {
		$baseUrl = Service::GetBaseUrl();
		$url = implode('/', [$baseUrl, $path]);
		return $url;
	}

	public function getTemplateAssetUrl(string $path) : string {
		$baseUrl = Service::GetBaseUrl();
		$assetUrl = implode('/', [$baseUrl, 'template', $path]);
		return $assetUrl;
	}


	public function getTemplateFilepath() : string {
		$name = $this->getName();
		$templatedir = $this->getTemplateDir();
		$templatefile = implode(DIRECTORY_SEPARATOR, [$templatedir, "$name.phtml"]);
		return $templatefile;
	}



	public function render(string $content) : void {
		$content = self::RemoveCommentBlocks($content);
		$this->_mainContent = self::ParseMainContent($content);
		$this->_blocks = self::ParseBlocks($content);

		$templatedir = $this->GetTemplateDir();
		if (!is_dir($templatedir)) {
			$errmsg = Log::Error("Template directory '$templatedir' not found");
			throw new \Exception($errmsg, 500);
		}

		$templatefile = $this->getTemplateFilepath();
		if (!is_file($templatefile)) {
			$errmsg = Log::error("Template file '$templatefile' not found");
			throw new \Exception($errmsg, 500);
		}



		try {
			ob_start();
			include_once $templatefile; 
			$html = ob_get_contents();
		} catch (\Exception $ex) {
			$html = $ex->getMessage();
		} finally {
			ob_end_clean();
			if (!empty($html)) {
				echo $html;
			} else {
				$filename =  basename($templatefile);
				Log::Error("Error occured when rendering template file '$filename'");
			}
		}
	}
	


}