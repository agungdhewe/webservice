<?php namespace AgungDhewe\Webservice\Routers;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\Webservice\IRouteHandler;
use AgungDhewe\Webservice\ServiceRoute;

class AssetRoute extends ServiceRoute implements IRouteHandler {
	const ALLOWED_EXTENSIONS = array(
		'js' => ['contenttype'=>'application/javascript'],
		'mjs' => ['contenttype'=>'application/javascript'],
		'css' => ['contenttype'=>'text/css'],
		'gif' => ['contenttype'=>'image/gif'],
		'bmp' => ['contenttype'=>'image/bmp'],
		'png' => ['contenttype'=>'image/png'],
		'jpg' => ['contenttype'=>'image/jpeg'],
		'svg' => ['contenttype'=>'image/svg+xml'],
		'pdf' => ['contenttype'=>'application/pdf'],
		'woff2' => ['contenttype'=>'font/woff2']
	);

	function __construct(string $urlreq) {
		parent::__construct($urlreq); // contruct dulu parentnya
	}

	function route(?array $param = []) : void {
		Log::info("Route Page $this->urlreq");
	}


	protected function sendAsset(string $assetDir, string $requestedFile) : void {
		$assetpath = implode('/', [$assetDir, $requestedFile]);

		try {
			$contenttype = $this->getContentType($requestedFile);
			if (empty($contenttype)) {
				$errmsg = log::error("Asset request of '$requestedFile' is not allowed");
				throw new \Exception($errmsg, 403);
			}
			header("Content-Type: $contenttype");

			if (!is_file($assetpath)) {
				$errmsg = log::error("Asset '$assetpath' is not found");
				throw new \Exception($errmsg, 404);
			}
			header("Content-Length: ".filesize($assetpath));

			$filename =basename($requestedFile);
			header("Content-Disposition: attachment; filename=\"$filename\"");
	
		
			echo fread(fopen($assetpath, "r"), filesize($assetpath));
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	protected function getContentType(string $file) : ?string {
		$extension = pathinfo($file, PATHINFO_EXTENSION);
		if (array_key_exists($extension, self::ALLOWED_EXTENSIONS)) {
			return self::ALLOWED_EXTENSIONS[$extension]['contenttype'];
		} else {
			return null;
		}
	}
}