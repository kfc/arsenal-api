<?php

namespace Cache;

class ArrayCache extends AbstractCache {
	public function get($key) { }
	public function set($key, $value, $expiry) { }
	public function clear($key, $wildcard) { }
}