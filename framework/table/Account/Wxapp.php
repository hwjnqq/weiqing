<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Account;

class Wxapp extends \We7Table {
	protected $tableName = 'account_wxapp';
	protected $primaryKey = 'acid';
	protected $field = array(
		'uniacid',
		'token',
		'encodingaeskey',
		'level',
		'account',
		'original',
		'key',
		'secret',
		'name',
		'appdomain',
	);
	protected $default = array(
		'uniacid' => '',
		'token' => '',
		'encodingaeskey' => '',
		'level' => '',
		'account' => '',
		'original' => '',
		'key' => '',
		'secret' => '',
		'name' => '',
		'appdomain' => '',
	);

	public function wxappInfo($uniacid) {
		if (is_array($uniacid)) {
			return $this->query->where('uniacid', $uniacid)->getall('uniacid');
		} else {
			return $this->query->where('uniacid', $uniacid)->get();
		}
	}
}