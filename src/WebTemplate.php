<?php namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;



abstract class WebTemplate implements IWebTemplate {

	private string $_title = "default page title";
	private string $_mainContent;
	private array $_blocks;

	
	abstract public function GetName() : string;
	abstract public function GetTemplateDir() : string;
	
	public static function getTemplateObject(IWebTemplate $ifc) : IWebTemplate {
		return $ifc;
	}


	public static function removeCommentBlocks(string $content) : string {
		$cleaned_content = preg_replace('/\{\*.*?\*\}/s', '', $content);
		return $cleaned_content;
	}

	public static function parseMainContent(string $content) : string {
		$pattern = '/\{block name="[^"]*".*?{\/block}/s';
		$content_without_blocks = preg_replace($pattern, '', $content);
		$content_without_blocks = trim($content_without_blocks);
		return $content_without_blocks;
	} 

	public static function parseBlocks(string $content) : array {
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
		$templatedir = $this->GetTemplateDir();
		if ($dir === null) {
			$dir = $templatedir;
		} 
		
		if (!is_dir($dir)) {
			$errmsg = Log::error("Directory '$dir' not found");
			throw new \Exception($errmsg, 500);
		}

		$filepath = implode(DIRECTORY_SEPARATOR, [$dir, $filename]);
		if (!is_file($filepath)) {
			$errmsg = Log::error("File '$filepath' not found");
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
		$baseurl = ServiceRoute::getBaseUrl();
		return "$baseurl/";
	}

	public function getUrl(string $path) : string {
		$baseUrl = ServiceRoute::getBaseUrl();
		$url = implode('/', [$baseUrl, $path]);
		return $url;
	}

	public function getTemplateAssetUrl(string $path) : string {
		$baseUrl = ServiceRoute::getBaseUrl();
		$assetUrl = implode('/', [$baseUrl, 'template', $path]);
		return $assetUrl;
	}


	public function GetTemplateFilepath() : string {
		$name = $this->GetName();
		$templatedir = $this->GetTemplateDir();
		$templatefile = implode(DIRECTORY_SEPARATOR, [$templatedir, "$name.phtml"]);
		return $templatefile;
	}



	public function Render(string $content) : void {
		$content = self::removeCommentBlocks($content);
		$this->_mainContent = self::parseMainContent($content);
		$this->_blocks = self::parseBlocks($content);

		$templatedir = $this->GetTemplateDir();
		if (!is_dir($templatedir)) {
			$errmsg = Log::error("Template directory '$templatedir' not found");
			throw new \Exception($errmsg, 500);
		}

		$templatefile = $this->GetTemplateFilepath();
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
				Log::error("Error occured when rendering template file '$filename'");
			}
		}
	}
	


}