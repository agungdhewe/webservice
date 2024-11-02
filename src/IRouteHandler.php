<?php namespace AgungDhewe\Webservice;

interface IRouteHandler {	
	function __construct(string $urlreq);
	function route(?array $param = []) : void;
}