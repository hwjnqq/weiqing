<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

//get_clerks
function clerk_fetchall($sid) {
	global $_W;
	$data = pdo_fetchall("SELECT * FROM " . tablename('tiny_wmall_clerk') . ' WHERE uniacid = :uniacid AND sid = :sid', array(':uniacid' => $_W['uniacid'], ':sid' => $sid));
	return $data;
}

//get_clerk
function clerk_fetch($id) {
	global $_W;
	$data = pdo_fetch("SELECT * FROM " . tablename('tiny_wmall_clerk') . ' WHERE uniacid = :uniacid AND id = :id', array(':uniacid' => $_W['uniacid'], ':id' => $id));
	return $data;
}

//check_clerk
function clerk_check($sid = 0) {
	global $_W;
	$data = array();
	if(!empty($_W['openid'])) {
		$where = array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid']);
		if($sid > 0) {
			$where['sid'] = $sid;
		}
		$data = pdo_get('tiny_wmall_clerk', $where);
	}
	if(empty($data)) {
		message('您没有管理店铺的权限', '', 'error');
	}
	return false;
}


