<?php
require 'config.php';
require 'Slim/Slim.php';
require 'import.php';
require 'init.php';


global $config;

\Slim\Slim::registerAutoloader();

Session::start();

$app = new \Slim\Slim(array(
	'mode' => isset($config['mode']) ? $config['mode'] : 'development',
	'debug' => isset($config['debug']) ? $config['debug'] : true
	)
);

// Define log resource
$app->container->singleton('user', function () {
	return new User();
});

require 'middleware.php';


$app->response->headers->set('Content-Type', 'application/json; charset=utf-8');
$app->cache = new Cache();
//$app->user = User::getUserInfo($uid);
if(isset($config['useCache']) && $config['useCache'] == false) {
	$api = new DrupalApi();
	$app->cache->clearCache();
}



$newsApiRoutes = new NewsRouter($app);
$newsApiRoutes->init();

$commentsApiRoutes = new CommentsRouter($app);
$commentsApiRoutes->init();

$gamecastApiRoutes = new GamecastRouter($app);
$gamecastApiRoutes->init();

$userApiRoutes = new UserRouter($app);
$userApiRoutes->init();

if(!$app->request->isGet()) {
	// Add user check for all non-get routes
	$app->add(new CheckUserMiddleware());
}

// POST route
$app->post('/post',function () {});

// DELETE route
$app->delete('/delete',function () {echo 'This is a DELETE route'; });

// Set 5 minutes cache for GET requests
if ($app->request->isGet()) {
	$app->expires(time()+ (0 * 60));
}


/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();

