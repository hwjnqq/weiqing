<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/28
 * Time: 14:18
 */

namespace We7\Core;


class We7Setting implements \ArrayAccess {

	private static $instance;
	private $loaded = false;
	private function __construct() {

	}
	public static function getInstance() {
		if(is_null(self::$instance)) {
			self::$instance = new We7Setting();
		}
		return self::$instance;
	}


	public function offsetExists($offset) {

	}


	public function offsetGet($offset) {

	}


	public function offsetSet($offset, $value) {

	}


	public function offsetUnset($offset) {

	}
}