<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

// check_params();
mload()->model('activity');
mload()->model('card');
mload()->model('clerk');
mload()->model('order');
$uid = mc_openid2uid($_W['openid']);
$storeid = intval($_GPC['id']);
$type = !empty($_GPC['type']) ? intval($_GPC['type']) : 1;

if ($op == 'display') {
	$activity_list = pdo_getall('storex_goods_activity', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'type' => $type), array(), '', 'endtime DESC');
	if (!empty($activity_list) && is_array($activity_list)) {
		foreach ($activity_list as $key => $value) {
			if ($value['is_spec'] == 1) {
				$spec_ids[$key] = $value['specid'];
			} else {
				$not_spec_ids[$key] = $value['goodsid'];
			}
		}
	}
	$spec_goods = pdo_getall('storex_spec_goods', array('id' => $spec_ids), array('id', 'goodsid', 'title', 'goods_val', 'oprice', 'cprice', 'stock', 'thumb'), 'id');
	$not_spec_goods = pdo_getall('storex_goods', array('id' => $not_spec_ids), array('id', 'title', 'oprice', 'cprice', 'stock', 'thumb'), 'id');
	if (!empty($spec_goods) && is_array($spec_goods)) {
		foreach ($spec_goods as &$goods) {
			$goods['thumb'] = tomedia($goods['thumb']);
			$goods['goods_val'] = iunserializer($goods['goods_val']);
			$goods['title'] .= ' ' . implode(' ', $goods['goods_val']);
			$goods['is_spec'] = 1;
		}
		unset($goods);
	}
	if (!empty($not_spec_goods) && is_array($not_spec_goods)) {
		foreach ($not_spec_goods as &$val) {
			$val['thumb'] = tomedia($val['thumb']);
			$val['is_spec'] = 2;
		}
		unset($val);
	}
	if (!empty($activity_list) && is_array($activity_list)) {
		foreach ($activity_list as &$value) {
			$value['cprice'] = $value['price'];
			$value['starttime'] = date('Y-m-d H:i', $value['starttime']);
			$value['endtime'] = date('Y-m-d H:i', $value['endtime']);
			if ($value['is_spec'] == 1) {
				$value['oprice'] = $spec_goods[$value['specid']]['oprice'];
				$value['title'] = $spec_goods[$value['specid']]['title'];
				$value['thumb'] = $spec_goods[$value['specid']]['thumb'];
			} else {
				$value['oprice'] = $not_spec_goods[$value['goodsid']]['oprice'];
				$value['title'] = $not_spec_goods[$value['goodsid']]['title'];
				$value['thumb'] = $not_spec_goods[$value['goodsid']]['thumb'];
			}
		}
		unset($value);
	}
	wmessage(error(0, $activity_list), '', 'ajax');
}