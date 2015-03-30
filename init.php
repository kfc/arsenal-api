<?php

define('DRUPAL_ROOT', '../');
define('HOSTNAME', $_SERVER['HTTP_HOST']);
define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').HOSTNAME);

require_once DRUPAL_ROOT.'/includes/bootstrap.inc';
require_once DRUPAL_ROOT.'/includes/file.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_VARIABLES);

date_default_timezone_set('Europe/Moscow');