<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/24
 * Time: 18:42
 */
class We7BaseUserGroup extends We7Entity {


	/**
	 *  
	 */
	public function unigroups() {
		return We7UniGroup::query()->where('id', $this->package());
	}

	/**
	 *  获取所有的公众账号
	 */
	public function uniaccounts() {
		
	}

	

	public function package() {
		static $package;
		if (!$package) {
			$package = iunserializer($this->package);
		}
		return $package;
	}

	public function __get($key) {
		if($key == 'unigroups') {
			return $this->unigroups()->getall();
		}
		return parent::__get($key);
	}
}