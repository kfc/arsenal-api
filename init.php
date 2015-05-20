<?php
define('DRUPAL_ROOT', dirname(__FILE__).'/..');
define('HOSTNAME', $_SERVER['HTTP_HOST']);
define('PROTOCOL', (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http').'://');
define('BASE_URL', PROTOCOL.HOSTNAME);


require_once DRUPAL_ROOT.'/includes/bootstrap.inc';
require_once DRUPAL_ROOT.'/includes/common.inc';
require_once DRUPAL_ROOT.'/includes/path.inc';
require_once DRUPAL_ROOT.'/includes/file.inc';

drupal_bootstrap(DRUPAL_BOOTSTRAP_VARIABLES);
drupal_language_initialize();
global $base_url;
$base_url = BASE_URL;

date_default_timezone_set('Europe/Moscow');