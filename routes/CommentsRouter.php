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
	        $data = $app->cache->getCache($cache_key);
        	if($data == null) {
            $data = $api->getNodeComments($nid, $page);
						$app->cache->setCache($cache_key, $data, 0);
       		 }
	        $app->response->body($data);
		});



		// POST comment
		$app->post('/comments/:nid',function ($nid) use ($app, $cache_key, $api) {
			$data = $api->postComment($nid, $app);
			if($data != null) {
				$app->cache->setCache($cache_key, $data, 0);
				$app->response->body($data);
			}
			else {
				$app->response->setStatus(400);
				$app->response->body('Error');
			}

		});


	}

}
