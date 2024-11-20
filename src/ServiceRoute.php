<?php declare(strict_types=1);
namespace AgungDhewe\Webservice;

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


}