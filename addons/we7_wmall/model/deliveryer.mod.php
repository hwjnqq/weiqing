<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

//get_clerks
function deliveryer_fetchall($sid) {
	global $_W;
	$data = pdo_fetchall("SELECT * FROM " . tablename('tiny_wmall_deliveryer') . ' WHERE uniacid = :uniacid AND sid = :sid', array(':uniacid' => $_W['uniacid'], ':sid' => $sid), 'id');
	return $data;
}

//get_clerk
function deliveryer_fetch($id) {
	global $_W;
	$data = pdo_fetch("SELECT * FROM " . tablename('tiny_wmall_deliveryer') . ' WHERE uniacid = :uniacid AND id = :id', array(':uniacid' => $_W['uniacid'], ':id' => $id));
	return $data;
}

//check_clerk
function deliveryer_check($sid = 0) {
	global $_W;
	$data = array();
	if(!empty($_W['openid'])) {
		$where = array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid']);
		if($sid > 0) {
			$where['sid'] = $sid;
		}
		$data = pdo_get('tiny_wmall_deliveryer', $where);
	}
	if(empty($data)) {
		message('您没有管理店铺的权限', '', 'error');
	}
	return false;
}

function deliveryer_order_stat($sid, $deliveryer_id) {
	global $_W;
	$stat = array();
	$today_starttime = strtotime(date('Y-m-d'));
	$yesterday_starttime = $today_starttime - 86400;
	$month_starttime = strtotime(date('Y-m'));
	$stat['yesterday_num'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('tiny_wmall_order') . ' where uniacid = :uniacid and sid = :sid and deliveryer_id = :deliveryer_id and status =5 and addtime >= :starttime and addtime <= :endtime', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':deliveryer_id' => $deliveryer_id, ':starttime' => $yesterday_starttime, ':endtime' => $today_starttime)));
	$stat['today_num'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('tiny_wmall_order') . ' where uniacid = :uniacid and sid = :sid and deliveryer_id = :deliveryer_id and status =5 and addtime >= :starttime', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':deliveryer_id' => $deliveryer_id, ':starttime' => $today_starttime)));
	$stat['month_num'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('tiny_wmall_order') . ' where uniacid = :uniacid and sid = :sid and deliveryer_id = :deliveryer_id and status =5 and addtime >= :starttime', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':deliveryer_id' => $deliveryer_id, ':starttime' => $month_starttime)));
	$stat['total_num'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('tiny_wmall_order') . ' where uniacid = :uniacid and sid = :sid and deliveryer_id = :deliveryer_id and status =5', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':deliveryer_id' => $deliveryer_id)));
	return $stat;
}

function checkdeliveryer() {
	global $_W;
	if(empty($_W['openid'])) {
		message('获取身份信息错误', '', 'error');
	}
	$deliveryer = pdo_get('tiny_wmall_deliveryer', array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid']));
	if(empty($deliveryer)) {
		message('您没有配送订单的权限', '', 'error');
	}
	return $deliveryer;
}

