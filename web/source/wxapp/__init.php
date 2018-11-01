<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('miniapp');
$version_id = intval($_GPC['version_id']);
if (!empty($version_id)) {
	$version_info = miniapp_version($version_id);
}

if ($action == 'post') {
	define('FRAME', 'system');
}
if ($action == 'version' && $do == 'display') {
	define('FRAME', '');
}
if (!in_array($action, array('post', 'manage', 'auth'))) {
	$account_api = WeAccount::createByUniacid();
	if (is_error($account_api)) {
		itoast('', url('account/display', array('type' => WXAPP_TYPE_SIGN)));
	}
	$check_manange = $account_api->checkIntoManage();
	if (is_error($check_manange)) {
		itoast('', $account_api->displayUrl);
	}
	$account_type = $account_api->menuFrame;
	define('FRAME', $account_type);
}