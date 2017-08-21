<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
 
defined('IN_IA') or exit('Access Denied');

class AccountTable extends Query {
	
	public function __construct() {
		$this->from('uni_account', 'a');
		return $this;
	}
	
	
	public function searchAccountList() {
		$this->leftjoin('account', 'b')->
				on(array('a.uniacid' => 'b.uniacid', 'a.default_acid' => 'b.acid'));
		return $this;
	}
	
	public function needKeyword($title) {
		$this->where('a.name LIKE', "%{$title}%");
		return $this;
	}
	
	public function getUniAccountList() {
		
		return $this;
	}
}

class WeixinAccountTable extends AccountTable {
	
}