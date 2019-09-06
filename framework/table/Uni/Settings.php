<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Uni;

class Settings extends \We7Table {
	protected $tableName = 'uni_settings';
	protected $primaryKey = 'uniacid';
	protected $field = array(
		'passport',
		'oauth',
		'jsauth_acid',
		'notify',
		'creditnames',
		'creditbehaviors',
		'welcome',
		'default',
		'default_message',
		'shortcuts',
		'payment',
		'stat',
		'default_site',
		'sync',
		'recharge',
		'tplnotice',
		'grouplevel',
		'mcplugin',
		'exchange_enable',
		'coupon_type',
		'statistics',
		'bind_domain',
		'comment_status',
		'reply_setting',
		'default_module',
		'attachment_limit',
		'attachment_siza',
		'sync_member',
		'remote'
	);
	protected $default = array(
		'passport' => '',
		'oauth' => '',
		'jsauth_acid' => '',
		'notify' => '',
		'creditnames' => '',
		'creditbehaviors' => '',
		'welcome' => '',
		'default' => '',
		'default_message' => '',
		'shortcuts' => '',
		'payment' => '',
		'stat' => '',
		'default_site' => '',
		'sync' => '',
		'recharge' => '',
		'tplnotice' => '',
		'grouplevel' => '',
		'mcplugin' => '',
		'exchange_enable' => '',
		'coupon_type' => '',
		'statistics' => '',
		'bind_domain' => '',
		'comment_status' => '',
		'reply_setting' => '',
		'default_module' => '',
		'attachment_limit' => '',
		'attachment_siza' => '',
		'sync_member' => '',
		'remote' => ''
	);

}