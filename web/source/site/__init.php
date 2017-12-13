<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');


$account_api = WeAccount::create();
$check_manange = $account_api->checkIntoManage();

if (is_error($check_manange)) {
	$account_display_url = $account_api->accountDisplayUrl();
	itoast('', $account_display_url);
}
$account_type = $account_api->accountType();
define('FRAME', $account_type);