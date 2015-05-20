<?php

class Session {

	static function start() {
		session_start();
	}

	static function destroy() {
		session_destroy();
	}

	static function isUserLoggedIntoDrupal() {
		$isLogged = false;

		if(isset($_COOKIE['DRUPAL_UID'])) {
			$sids = db_select('sessions', 's')
				->fields('s', array('sid'))
				->condition('uid', (int)$_COOKIE['DRUPAL_UID'])
				->execute()
				->fetchCol();
			$session_name = '';
			if(!empty($sids)) {
				foreach($_COOKIE as $_key => $_value) {
					if(in_array($_value, $sids)) {
						$isLogged = true;
						break;
					}
				}
			}
		}
		return $isLogged;
	}

}