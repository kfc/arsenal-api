<?php 
class CommentsRouter extends ApiRouter {
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
		$api = new CommentsApi();


		$app->get('/comments/:nid(/:page)', function ($nid, $page = 1) use ($app, $cache_key, $api) {
	        $data = $api->getCache($cache_key);
        	if($data == null) {
                	$data = $api->getNodeComments($nid, $page);
	                $api->setCache($cache_key, $data, CACHE_PERMANENT);
       		 }
	        $app->response->body($data);
		});



		// POST comment
		$app->post('/comments/:nid',function ($nid) use ($app, $cache_key, $api) {
			$data = $api->postComment($nid, $app->request);
			if($data != null) {
				$api->setCache($cache_key, $data, CACHE_PERMANENT);
			}
			$app->response->body($data);
		});


	}

}
