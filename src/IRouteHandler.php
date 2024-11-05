<?php namespace AgungDhewe\Webservice;

interface IRouteHandler {	
	function __construct(string $urlreq);
	public function route(?array $param = []) : void;
}