<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if ($action != 'entry') {
	if (!empty($_W['account']) && $_W['account']['type'] == ACCOUNT_TYPE_WEBAPP_NORMAL) {
		checkwebapp();
	} else {
		checkaccount();
	}
}

if ($action == 'editor' && $_W['account']['type'] == ACCOUNT_TYPE_WEBAPP_NORMAL) {
	define('FRAME', 'webapp');
} else {
	define('FRAME', 'account');
}

if (!($action == 'multi' && $do == 'post')) {
	define('FRAME', 'account');
}