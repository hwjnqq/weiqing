<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
$_GPC['account_type'] = !empty($_GPC['account_type']) ? $_GPC['account_type'] : ACCOUNT_TYPE_OFFCIAL_NORMAL;

if ($_GPC['account_type'] == ACCOUNT_TYPE_OFFCIAL_NORMAL) {
	define('FRAME', 'account');
	checkaccount();
} elseif ($_GPC['account_type'] == ACCOUNT_TYPE_WEBAPP_NORMAL) {
	define('FRAME', 'webapp');
	checkwebapp();
}
