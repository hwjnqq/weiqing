<?php
/**
 * 退出系统
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
isetcookie('__session', '', -10000);
isetcookie('__switch', '', -10000);

$forward = $_GPC['forward'];
if (empty($forward)) {
	$forward = './?refersh';
}

header('Location:' . $_W['siteroot']);