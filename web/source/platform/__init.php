<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$account_api = WeAccount::create();
$check_manange = $account_api->checkIntoManage();

if (!($action == 'material' && $do == 'delete') && empty($_GPC['version_id'])) {
	if (is_error($check_manange)) {
		$account_display_url = $account_api->accountDisplayUrl();
		itoast('', $account_display_url);
	}
}

if ($action != 'material-post' && $_GPC['uniacid'] != FILE_NO_UNIACID) {
	$account_type = $account_api->accountType();
	define('FRAME', $account_type);
}
if ($action == 'qr') {
	$platform_qr_permission = permission_check_account_user('platform_qr', false);
	if ($platform_qr_permission ===  false) {
		header("Location: ". url('platform/url2qr'));
	}
}

if ($action == 'url2qr') {
	define('ACTIVE_FRAME_URL', url('platform/qr'));
}