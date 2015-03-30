<?php


class DrupalApi {

	const CACHE_TABLE = 'cache_custom_api';
	private $memcached;

	function __construct() {
		$this->memcached = new Memcached();
		$this->memcached->addServer('127.0.0.1', 11211);
	}

	function getCache($key) {
		if(!empty($key)) {
			$cache = $this->memcached->get($key);
			if(!empty($cache)) {
				return $cache;
			}
			else return null;
		}
		return null;

		/*$cache = cache_get($key, DrupalApi::CACHE_TABLE);
		if (!empty($cache) && $cache->data && (($cache->expire == CACHE_PERMANENT) || ( ($cache->expire != CACHE_PERMANENT) && ($cache->expire > time()) )  ) ) {
			return $cache->data;
		}
		return null;*/
	}

	function setCache($key, $data, $expired = 5)  {
		if(!empty($key)) {
			$this->memcached->set($key, $data, $expired * 60);
		}
		/*if(!empty($key)) {

			cache_set($key, $data, DrupalApi::CACHE_TABLE, ($expired != CACHE_PERMANENT ? (time()+ ($expired * 60)) : CACHE_PERMANENT ));
		}*/
	}

	function clearCache($key = '', $wildcard = false)  {
		if(!empty($key)) {
			if($wildcard) {
				$keys_to_delete = array();
				$keys = $this->memcached->getAllKeys();
				foreach($keys as $_key) {
					if(strpos($_key, $key) !== false) {
						$keys_to_delete[] = $_key;
					}
				}
				if(!empty($keys_to_delete)) {
					$this->memcached->deleteMulti($keys_to_delete);
				}
			}
			//cache_clear_all($key, DrupalApi::CACHE_TABLE, $wildcard);
		}
		else {
			$this->memcached->deleteMulti($this->memcached->getAllKeys());
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
