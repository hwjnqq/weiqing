<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/24
 * Time: 15:31
 */

class We7FounderGroup extends We7BaseUserGroup {

	protected $table = 'users_founder_group';
	protected $primaryKey = 'id';

	public function modules() {
		$package = $this->package();
		$modules = $package['modules'];

	}

	public function templates() {
		$package = $this->package();
		if($package) {
			$templates = $package['templates'];
		}

	}

	public function package() {
		static $package;
		if (!$package) {
			$package = unserialize($this->package);
		}
		return $package;
	}

	public function __get($keys) {

	}

}
