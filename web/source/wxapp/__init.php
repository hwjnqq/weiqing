<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if ($action == 'post') {
	define('FRAME', 'system');
}

if (!in_array($action, array('display', 'post', 'manage', 'auth'))) {
	$account_api = WeAccount::createByUniacid($_W['uniacid']);
	if (is_error($account_api)) {
		itoast('', url('account/display', array('type' => WXAPP_TYPE_SIGN)));
	}
	$check_manange = $account_api->checkIntoManage();
	if (is_error($check_manange)) {
		$account_display_url = $account_api->accountDisplayUrl();
		itoast('', $account_display_url);
	}
	$account_type = $account_api->menuFrame;
	define('FRAME', $account_type);
}