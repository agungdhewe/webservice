<?php namespace AgungDhewe\Webservice;

abstract class ServiceRoute {
	protected $urlreq;


	function __construct(string $urlreq) {
		$this->urlreq = $urlreq;
	}


	public static function getRequestedParameter(string $prefix, string $urlreq) : string {
		// cek if prefix is page, then remove prefix
		if (str_starts_with($urlreq, $prefix)) {
			$requested = substr($urlreq, strlen($prefix));
		} else {
			$requested = $urlreq;
		}
		return $requested;
	}	

	public static function getModuleNamespace(string $text) : string {
		$parts = explode("\\", $text);
		$res = array_slice($parts, 0, 2);
		return implode('\\', $res);
	} 

	public static function getBaseUrl() : string {
		$headers = getallheaders(); 
		if (array_key_exists('BASE_HREF', $headers)) {
			return trim($headers['BASE_HREF'], '/');
		} else if (!empty($baseUrl=Configuration::Get('BaseUrl'))) {
			return $baseUrl;
		} else {
			return $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'];
		}
	}
}