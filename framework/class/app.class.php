<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/25
 * Time: 14:39
 */

class App implements ArrayAccess {

	private $attributes = array();
	public function __construct() {

	}

	public function offsetExists($offset) {
		return isset($this->attributes[$offset]);
	}


	public function offsetGet($offset) {
		return $this->offsetExists($offset) ? $this->attributes[$offset] : null;
	}


	public function offsetSet($offset, $value) {
		$this->attributes[$offset] = $value;
	}


	public function offsetUnset($offset) {
		unset($this->attributes[$offset]);
	}
}