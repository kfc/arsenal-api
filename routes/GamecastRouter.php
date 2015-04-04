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
			$data = $app->cache->getCache($cache_key);
			if($data == null) {
				$data = $api->getGamecast($match_nid);
				$app->cache->setCache($cache_key, $data, 0);
			}
			$app->response->body($data);
		});

		$app->get('/match-events/:match_nid', function ($match_nid) use ($app, $cache_key, $api) {
			$data = $app->cache->getCache($cache_key);
			if($data == null) {
				$data = $api->getMatchEvents($match_nid);
				$app->cache->setCache($cache_key, $data, 0);
			}
			$app->response->body($data);
		});

		$app->get('/match-info/:match_nid', function ($match_nid) use ($app, $cache_key, $api) {
			$data = $app->cache->getCache($cache_key);
			if($data == null) {
				$data = $api->getMatchInfo($match_nid);
				$app->cache->setCache($cache_key, $data, 0);
			}
			$app->response->body($data);
		});

		$app->get('/match-squads/:match_nid', function ($match_nid) use ($app, $cache_key, $api) {
			$data = $app->cache->getCache($cache_key);
			if($data == null) {
				$data = $api->getMatchSquads($match_nid);
				$app->cache->setCache($cache_key, $data, 0);
			}
			$app->response->body($data);
		});

	}

}