<?php 
class NewsRouter extends ApiRouter {
	function __construct($app){
		parent::__construct($app);
		//$this->init();
	}
	
	function init() {
		if($this->app == null){
			return false;
		}
		$app = $this->app;
		$cache_key = $this->cache_key;
		$api = new NewsApi();
		$app->get('/news(/:page)', function ($page = 1) use ($app, $cache_key, $api) {
        	$api = new NewsApi();
	        $data = $app->cache->get($cache_key);
        	if($data == null) {
            $data = $api->get($page);
						$app->cache->set($cache_key, $data, CACHE_PERMANENT);
       		 }
	        $app->response->body($data);
		});

		$app->get('/match-news/:nid(/:page)', function ($nid, $page = 1) use ($app, $cache_key, $api) {
	        $data = $app->cache->get($cache_key);
        	if($data == null) {
            $data = $api->getMatchNews($nid, $page);
						$app->cache->set($cache_key, $data, CACHE_PERMANENT );
        	}
	        $app->response->body($data);
		});
	}

}
