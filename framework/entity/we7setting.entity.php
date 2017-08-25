<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/25
 * Time: 17:16
 */

/***
 * Class We7Setting
 * @property $register
 * @property mail
 * @property basic
 * @property authmode
 * @property cloudip
 * @property platform
 * @property
 */
class We7Setting extends We7Entity {

	protected $table = 'core_setting';
	public $incrementing = false;

	public function __get($key) {
		return isset($this->attributes[$key]) ? iunserializer($this->attributes['key']) : null;
	}

}