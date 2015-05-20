<?php

class UserRouter extends ApiRouter {
	function __construct($app){
		parent::__construct($app);
	}

	function init() {
		if($this->app == null){
			return false;
		}
		$app = $this->app;
		$api = new UserApi();

		$app->get('/user', function () use ($app, $api) {
			$app->response->body($api->jsonResonse($app->user));
		});

	}

}
