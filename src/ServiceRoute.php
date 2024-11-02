<?php namespace AgungDhewe\Webservice;

abstract class ServiceRoute {
	protected $urlreq;


	public function __construct(string $urlreq) {
		$this->urlreq = $urlreq;
	}


	protected function getRequestedParameter(string $prefix, string $urlreq) : string {
		// cek if prefix is page, then remove prefix
		if (str_starts_with($urlreq, $prefix)) {
			$requested = substr($urlreq, strlen($prefix));
		} else {
			$requested = $urlreq;
		}
		return $requested;
	}	
}