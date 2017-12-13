<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if ($action == 'manage' && $do == 'createview') {
	define('FRAME', 'system');
}

$account_api = WeAccount::create();
$check_manange = $account_api->checkIntoManage();
if ($do != 'display') {
	if (is_error($check_manange)) {
		$account_display_url = $account_api->accountDisplayUrl();
		itoast('', $account_display_url);
	}
}

if ($action == 'manage' && $do == 'list' || $do != 'display') {
	define('FRAME', '');
} elseif ($do == 'display') {
	define('FRAME', 'webapp');
} else {
	$account_type = $account_api->accountType();
	define('FRAME', $account_type);
}
