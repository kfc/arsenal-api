<?php


class CheckUserMiddleware extends \Slim\Middleware
{
	public function call()
	{
		// Get reference to application
		$app = $this->app;
		if(empty($app->user) || !isset($app->user->uid) || $app->user->uid == 0) {
			$app->response->setStatus(403);
			$app->response->setBody('Access denied');
			return;
		}
		// Run inner middleware and application
		$this->next->call();


	}
}