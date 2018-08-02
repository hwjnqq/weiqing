<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$account_api = WeAccount::createByUniacid($_W['uniacid']);
if ($action == 'manage' || $action == 'post-step') {
	define('FRAME', 'system');
} else {
	define('FRAME', $account_api->menuFrame);
}