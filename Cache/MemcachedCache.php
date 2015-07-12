<?php

namespace Cache;

class MemcachedCache extends AbstractCache {

	private $host = '127.0.0.1';
	private $port = 11211;

	private $memcached;

	function __construct($host = null, $port = null) {
		$this->memcached = new \Memcached();

		if(null !== $host) {
			$this->host = $host;
		}

		if(null !== $port) {
			$this->port = $port;
		}

		$this->memcached->addServer($this->host, $this->port);
	}

	public function get($key) {
		if(!empty($key)) {
			$cache = $this->memcached->get($key);
			if($cache !== false) {
				return $cache;
			}
			else return null;
		}
		return null;
	}

	public function set($key, $value, $expired = 5)  {
		if(!empty($key)) {
			$this->memcached->set($key, $value, $expired * 60);
		}
	}

	function clear($key = '', $wildcard = false)  {
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
			else{
				$this->memcached->delete($key);
			}
		}
		else {
			$this->memcached->flush();
		}
	}
}