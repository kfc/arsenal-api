<?php

namespace Cache;

abstract class AbstractCache implements CacheInterface {

	public abstract function get($key);
	public abstract function set($key, $value, $expiry);
	public abstract function clear($key, $wildcard);

}