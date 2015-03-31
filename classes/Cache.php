<?php

class Cache {
	const CACHE_TABLE = 'cache_custom_api';

	function __construct() {
		$this->memcached = new Memcached();
		$this->memcached->addServer('127.0.0.1', 11211);
	}

	function getCache($key) {
		if(!empty($key)) {
			$cache = $this->memcached->get($key);
			if($cache !== false) {
				return $cache;
			}
			else return null;
		}
		return null;

		/*$cache = cache_get($key, self::CACHE_TABLE);
		if (!empty($cache) && $cache->data && (($cache->expire == 0) || ( ($cache->expire != 0) && ($cache->expire > time()) )  ) ) {
			return $cache->data;
		}
		return null;*/
	}

	function setCache($key, $data, $expired = 5)  {
		if(!empty($key)) {
			$this->memcached->set($key, $data, $expired * 60);
		}
		/*if(!empty($key)) {

			cache_set($key, $data, self::CACHE_TABLE, ($expired != 0 ? (time()+ ($expired * 60)) : 0 ));
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
			//cache_clear_all($key, self::CACHE_TABLE, $wildcard);
		}
		else {
			$this->memcached->flush();
		}
	}
}