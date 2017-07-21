<?php
/**
 * 小程序列表
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('wxapp');
load()->model('account');

$_W['page']['title'] = '小程序列表';

$dos = array('home');
$do = in_array($do, $dos) ? $do : 'home';

if ($do == 'home') {
	$url = url('wxapp/display');
	if (empty($_W['uniacid'])) {
		itoast('', $url, 'info');
	}
	$permission = uni_permission($_W['uid'], $_W['uniacid']);
	if (empty($permission)) {
		itoast('', $url, 'info');
	}
	$last_version = wxapp_fetch($_W['uniacid']);
	if (!empty($last_version)) {
		$url = url('wxapp/version/home', array('version_id' => $last_version['version']['id']));
	}
	itoast('', $url, 'info');
}