<?php namespace AgungDhewe\Webservice;

use AgungDhewe\PhpLogger\Log;

abstract class WebTemplate {

	private string $mainContent;
	private array $blocks;


	abstract public function GetTemplateFilepath() : string;
	abstract public function GetTemplateDir() : string;
	abstract public function GetAssetUrl() : string;

	// abstract public function Render(string $content) : void;


	protected function removeCommentBlocks(string $content) : string {
		$cleaned_content = preg_replace('/\{\*.*?\*\}/s', '', $content);
		return $cleaned_content;
	}

	protected function parseMainContent(string $content) : string {
		$pattern = '/\{block name="[^"]*".*?{\/block}/s';
		$content_without_blocks = preg_replace($pattern, '', $content);
		$content_without_blocks = trim($content_without_blocks);
		return $content_without_blocks;
	} 

	protected function parseBlocks(string $content) : array {
		$blocks = [];
		$pattern_block = '/\{block name="([^"]+)"\}(.*?)\{\/block\}/s';
		preg_match_all($pattern_block, $content, $matches);
		for ($i = 0; $i < count($matches[1]); $i++) {
			$blocks[$matches[1][$i]] = trim($matches[2][$i]);
		}
		return $blocks;
	}

	protected function getMainContent() : string {
		return $this->mainContent;
	}

	protected function getBlockContent(string $blokname) : string {
		if (array_key_exists($blokname, $this->blocks)) {
			return $this->blocks[$blokname];
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
			log::error("Directory '$dir' not found");
			throw new \Exception("Directory '$dir' not found", 500);
		}

		$filepath = implode('/', [$dir, $filename]);
		if (!is_file($filepath)) {
			log::error("File '$filepath' not found");
			throw new \Exception("File '$filepath' not found", 500);
		}

		require_once $filepath;
	}


	protected function getTitle() : string {
		if (array_key_exists('title', $this->blocks)) {
			return $this->blocks['title'];
		} else {
			return "title";
		}
	}

	public function Render(string $content) : void {
		$content = $this->removeCommentBlocks($content);
		$this->mainContent = $this->parseMainContent($content);
		$this->blocks = $this->parseBlocks($content);

		$templatedir = $this->GetTemplateDir();
		if (!is_dir($templatedir)) {
			throw new \Exception("Template directory '$templatedir' not found", 500);
		}

		$templatefile = $this->GetTemplateFilepath();
		if (!is_file($templatefile)) {
			throw new \Exception("Template file '$templatefile' not found", 500);
		}

		try {
			ob_start();
			require_once $templatefile;
			$html = ob_get_contents();
		} catch (\Exception $ex) {
			$html = $ex->getMessage();
		} finally {
			ob_end_clean();
			echo $html;
		}
	}
	
}