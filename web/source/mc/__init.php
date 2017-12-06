<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
if (!empty($_W['account']) && $_W['account']['type'] == ACCOUNT_TYPE_WEBAPP_NORMAL) {
	define('FRAME', 'webapp');
	checkwebapp();
} else {
	define('FRAME', 'account');
	checkaccount();
}