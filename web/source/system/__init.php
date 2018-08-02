<?php
/**
 * 系统管理公共文件
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
if (in_array($action, array('site', 'menu', 'attachment', 'systeminfo', 'logs', 'filecheck', 'optimize',
	'database', 'scan', 'bom', 'ipwhitelist', 'workorder', 'sensitiveword', 'thirdlogin', 'oauth', 'usersetting'))) {
	define('FRAME', 'site');
} else {
	define('FRAME', 'system');
}