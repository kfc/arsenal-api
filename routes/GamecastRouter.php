<?php
class GamecastRouter extends ApiRouter {
	function __construct($app){
		parent::__construct($app);
	}

	function init() {
		if($this->app == null){
			return false;
		}
		$app = $this->app;
		$cache_key = $this->cache_key;
		$api = new GamecastApi();
		$app->get('/gamecast/:match_nid', function ($match_nid) use ($app, $cache_key, $api) {
			$data = $api->getCache($cache_key);
			if($data == null) {
				$data = $api->getGamecast($match_nid);
				$api->setCache($cache_key, $data, CACHE_PERMANENT);
			}
			$app->response->body($data);
		});

	}

}