<?php
namespace Cache;

interface CacheInterface {

	function get($key);

	function set($key, $value, $expire);

	function clear($key, $wildcard);


}

