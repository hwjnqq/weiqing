<?php
/**
 * 帮助系统
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
global $_W;
load()->model('user');

$_W['page']['title'] = '帮助系统';
if ($_W['ishttps']) {
	header("Content-Security-Policy: upgrade-insecure-requests");
}
template('help/display');