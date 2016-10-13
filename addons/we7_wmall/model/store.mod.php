<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

//get_store
function store_fetch($id, $field = array()) {
	global $_W;
	$field_str = '*';
	if(!empty($field)) {
		$field_str = implode(',', $field);
	}
	$data = pdo_fetch("SELECT {$field_str} FROM " . tablename('tiny_wmall_store') . ' WHERE uniacid = :uniacid AND id = :id', array(':uniacid' => $_W['uniacid'], ':id' => $id));
	$se_fileds = array('thumbs', 'sns', 'mobile_verify', 'payment', 'business_hours', 'remind_reply', 'comment_reply', 'wechat_qrcode');
	foreach($se_fileds as $se_filed) {
		if(isset($data[$se_filed])) {
			$data[$se_filed] = (array)iunserializer($data[$se_filed]);
		}
	}
	if(isset($data['business_hours'])) {
		$data['is_in_business_hours'] = store_is_in_business_hours($data['business_hours']);
	}
	if(isset($data['score'])) {
		$data['score_cn'] = round($data['score'] / 5, 2) * 100;
	}
	return $data;
}

//checkstore
function store_check() {
	global $_W, $_GPC;
	if(!defined('IN_MOBILE')) {
		$sid = intval($_GPC['__sid']);
	} else {
		$sid = intval($_GPC['sid']);
	}
	if(!defined('IN_MOBILE')) {
		if($_W['role'] != 'manager' && empty($_W['isfounder'])) {
			$account = store_account($_W['uid']);
			if(!in_array($sid, $account['store_ids'])) {
				message('您没有该门店的管理权限', '', 'error');
			}
		}
	}
	$store = pdo_fetch('SELECT id, title FROM ' . tablename('tiny_wmall_store') . ' WHERE uniacid = :aid AND id = :id', array(':aid' => $_W['uniacid'], ':id' => $sid));
	if(empty($store)) {
		if(!defined('IN_MOBILE')) {
			message('门店信息不存在或已删除', '', 'error');
		}
		exit();
	}
	return $store;
}

//store_fetchall_category
function store_fetchall_category() {
	global $_W;
	$data = pdo_fetchall('select * from ' . tablename('tiny_wmall_store_category') . ' where uniacid = :uniacid order by displayorder desc', array(':uniacid' => $_W['uniacid']), 'id');
	return $data;
}

//store_fetch_activity
function store_fetch_activity($sid, $field = array()) {
	global $_W;
	$field_str = '*';
	if(!empty($field)) {
		$field_str = implode(',', $field);
	}
	$data = pdo_fetch("SELECT {$field_str} FROM " . tablename('tiny_wmall_store_activity') . ' WHERE uniacid = :uniacid AND sid = :sid', array(':uniacid' => $_W['uniacid'], ':sid' => $sid));
	$se_fileds = array('first_order_data', 'discount_data', 'grant_data');
	foreach($se_fileds as $se_filed) {
		if(isset($data[$se_filed])) {
			$data[$se_filed] = (array)iunserializer($data[$se_filed]);
		}
	}
	return $data;
}

//is_in_business_hours
function store_is_in_business_hours($business_hours) {
	if(!is_array($business_hours)) {
		return true;
	}
	$now = TIMESTAMP;
	$start = strtotime($business_hours['start']);
	$end = strtotime($business_hours['end']);
	if($start <= $now && $now <= $end) {
		return true;
	}
	return false;
}

//get_goods_category
function store_fetchall_goods_category($store_id, $status = '-1') {
	global $_W;
	$condition = ' where uniacid = :uniacid and sid = :sid';
	$params = array(':uniacid' => $_W['uniacid'], ':sid' => $store_id);
	if($status >= 0) {
		$condition .= ' and status = :status';
		$params[':status'] = $status;
	}
	$data = pdo_fetchall('select * from ' . tablename('tiny_wmall_goods_category') . $condition . ' order by displayorder desc', $params, 'id');
	return $data;
}

//get_goods_one
function store_fetch_goods($id, $field = array('basic', 'options', 'comment')) {
	global $_W;
	$goods = pdo_get('tiny_wmall_goods', array('uniacid' => $_W['uniacid'], 'id' => $id));
	if(empty($goods)) {
		return error(-1, '商品不存在或已删除');
	}
	$goods['thumb_'] = tomedia($goods['thumb']);
	if(in_array('options', $field) && $goods['is_options']) {
		$goods['options'] = pdo_getall('tiny_wmall_goods_options', array('uniacid' => $_W['uniacid'], 'goods_id' => $id));
	}
	if(in_array('comment', $field)) {
		//$goods['options'] = pdo_getall('tiny_wmall_goods_options', array('uniacid' => $_W['uniacid'], 'goods_id' => $id));
	}
	return $goods;
}

function store_account($uid) {
	global $_W;
	$account = pdo_get('tiny_wmall_account', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
	if(!empty($account)) {
		if(!empty($account['store_ids'])) {
			$account['store_ids'] = explode(',', $account['store_ids']);
			$account['store_has'] = count($account['store_ids']);
		} else {
			$account['store_ids'] = array(0);
			$account['store_has'] = 0;
		}
	}
	return $account;
}

/*计算门店的评价*/
function store_comment_stat($sid, $update = true) {
	global $_W;
	$stat = array();
	$stat['goods_quality'] = round(pdo_fetchcolumn('select avg(goods_quality) from ' . tablename('tiny_wmall_order_comment') . ' where uniacid = :uniacid and sid = :sid and status = 1'), 1);
	$stat['delivery_service'] = round(pdo_fetchcolumn('select avg(delivery_service) from ' . tablename('tiny_wmall_order_comment') . ' where uniacid = :uniacid and sid = :sid and status = 1'), 1);
	$stat['score'] = round(($stat['goods_quality'] + $stat['delivery_service']) / 2, 1);
	if($update) {
		pdo_update('tiny_wmall_store', array('score' => $stat['score']), array('uniacid' => $_W['uniacid'], 'id' => $sid));
	}
	return $stat;
}




