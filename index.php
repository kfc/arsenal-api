<?php
require 'config.php';
require 'Slim/Slim.php';
require 'import.php';
require 'init.php';


global $config;

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array(
	'mode' => isset($config['mode']) ? $config['mode'] : 'development',
	'debug' => isset($config['debug']) ? $config['debug'] : true
	)
);

if(isset($config['useCache']) && $config['useCache'] == false) {
	$api = new DrupalApi();
	$api->clearCache();
}

$app->response->headers->set('Content-Type', 'application/json; charset=utf-8');

$newsApiRoutes = new NewsRouter($app);
$newsApiRoutes->init();

$commentsApiRoutes = new CommentsRouter($app);
$commentsApiRoutes->init();

$gamecastApiRoutes = new GamecastRouter($app);
$gamecastApiRoutes->init();

// POST route
$app->post('/post',function () {});

// DELETE route
$app->delete('/delete',function () {echo 'This is a DELETE route'; });

// Set 5 minutes cache for GET requests
if ($app->request->isGet()) {
	$app->expires(time()+ (2 * 60));
}


/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();

