<?php
/**
 * 帮助系统
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
global $_W;
load()->model('user');

$_W['page']['title'] = '帮助系统';
if ($_SERVER['https'] == 'on' || $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
	header("Content-Security-Policy: upgrade-insecure-requests");
}
template('help/display');