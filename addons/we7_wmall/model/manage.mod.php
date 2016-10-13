<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

function checkstore() {
	global $_W, $_GPC;
	$sid = intval($_GPC['__mg_sid']);
	if(empty($sid)) {
		message('请先选择特定的门店', referer(), 'error');
	}
	$permiss = pdo_get('tiny_wmall_clerk', array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'openid' => $_W['openid']));
	if(empty($permiss)) {
		message('您没有该门店的管理权限', referer(), 'error');
	}
	$_W['we7_wmall']['store'] = pdo_get('tiny_wmall_store', array('uniacid' => $_W['uniacid'], 'id' => $sid), array('id', 'title', 'logo'));
	return true;
}