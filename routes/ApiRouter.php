<?php 
class ApiRouter {
	var $app = null;
	var $cache_key = '';
	function __construct($app) {
		$this->app = $app;
		$this->cache_key = str_replace('/','_',trim($this->app->request->getPathInfo(), '/'));
	}	

}
