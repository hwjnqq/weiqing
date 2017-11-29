<?php
/**
 * 云服务相关
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
if (in_array($action, array('upgrade', 'profile', 'diagnose', 'sms'))) {
	define('FRAME', 'site');
} else {
	define('FRAME', 'system');
}

if(in_array($action, array('profile', 'device', 'callback', 'appstore', 'sms'))) {
	$do = $action;
	$action = 'redirect';
}
if($action == 'touch') {
	exit('success');
}