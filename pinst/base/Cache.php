<?php

namespace pinst\base;

abstract class Cache extends Object{
	
	public $keyPrefix;
	
	public $serializer;
	
	public function buildKey($key)
	{
		if (is_string($key)) {
			$key = ctype_alnum($key) && mb_strlen($key, '8bit') <= 32 ? $key : md5($key);
		} else {
			$key = md5(json_encode($key));
		}
	
		return $this->keyPrefix . $key;
	}
	
	
	public function get($key)
	{
		$key = $this->buildKey($key);
		$value = $this->getValue($key);
		
		if ($value === false || $this->serializer === false) {
			return $value;
		} elseif ($this->serializer === null) {
			$value = unserialize($value);
		} else {
			$value = call_user_func($this->serializer[1], $value);
		}
		if (!empty($value)) {
			return $value;
		} else {
			return false;
		}
	}
	
	public function exists($key)
	{
		$key = $this->buildKey($key);
		$value = $this->getValue($key);
	
		return $value !== false;
	}
	
	public function set($key, $value, $duration = 0)
	{
		if ($this->serializer === null) {
			$value = serialize($value);
		} elseif ($this->serializer !== false) {
			$value = call_user_func($this->serializer[0], $value);
		}
		$key = $this->buildKey($key);
		return $this->setValue($key, $value, $duration);
	}
	
	abstract protected function getValue($key);
	abstract protected function setValue($key, $value, $duration);
	
}

?>