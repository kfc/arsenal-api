<?php

define('DRUPAL_ROOT', '../');
define('HOSTNAME', $_SERVER['HTTP_HOST']);
define('PROTOCOL', (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://');
define('BASE_URL', PROTOCOL.HOSTNAME);

require_once DRUPAL_ROOT.'/includes/bootstrap.inc';
require_once DRUPAL_ROOT.'/includes/file.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_VARIABLES);

date_default_timezone_set('Europe/Moscow');