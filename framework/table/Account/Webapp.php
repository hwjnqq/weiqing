<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Account;

class Webapp extends \We7Table {
	protected $tableName = 'account_webapp';
	protected $primaryKey = 'acid';
	protected $field = array(
		'uniacid',
		'name',

	);
	protected $default = array(
		'uniacid' => '',
		'name' => '',

	);
}