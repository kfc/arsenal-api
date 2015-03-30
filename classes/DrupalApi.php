<?php


class DrupalApi {

	const CACHE_TABLE = 'cache_custom_api';

	function __construct() {

	}

	function getCache($key) {
		$cache = cache_get($key, DrupalApi::CACHE_TABLE);
		if (!empty($cache) && $cache->data && (($cache->expire == CACHE_PERMANENT) || ( ($cache->expire != CACHE_PERMANENT) && ($cache->expire > time()) )  ) ) {
			return $cache->data;
		}
		return null;
	}

	function setCache($key, $data, $expired = 5)  {
		if(!empty($key)) {
			cache_set($key, $data, DrupalApi::CACHE_TABLE, ($expired != CACHE_PERMANENT ? (time()+ ($expired * 60)) : CACHE_PERMANENT ));
		}
	}

	function clearCache($key, $wildcard = false)  {
		if(!empty($key)) {
			cache_clear_all($key, DrupalApi::CACHE_TABLE, $wildcard);
		}
	}
	function jsonResonse($data){
		return json_encode(array(
			'retrievedAt' => date('c'),
			'data' => $data
		));
	}

	protected function getUser($uid, $uuid) {
		if(empty($uid) || empty($uuid)) {
			return null;
		}

		$name = db_select('users', 'u')
			->fields('u', array('name'))
			->condition('uid', $uid)
			->condition('uuid', $uuid)
			->range(0,1)
			->execute()
			->fetchField();

		if(empty($name)) {
			return false;
		} else {
			return $name;
		}

	}

} 
