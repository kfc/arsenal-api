<?php

class User {

	var $user;

	function __construct() {
		$this->getCurrentUser();
	}

	function __get($name){
		if (array_key_exists($name, $this->user)) {
			return $this->user[$name];
		}
	}

	function __isset($name) {
		return array_key_exists($name, $this->user);
	}

	function getCurrentUser() {
		$user = null;
		if(Session::isUserLoggedIntoDrupal()) {
			$uid = $_COOKIE['DRUPAL_UID'];

			if(!empty($uid)) {
				$user = db_select('users','u')
					->fields('u', array('uid', 'name', 'uuid'))
					->condition('uid', (int)$uid)
					->execute()
					->fetch();
			}
		}

		if($user == null) {
			$user = drupal_anonymous_user();
		}
		$this->user=(array)$user;
	}

}