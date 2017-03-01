<?php

defined('IN_IA') or exit('Access Denied');
include IA_ROOT . '/addons/we7_storex/function/function.php';
global $_W, $_GPC;
// paycenter_check_login();
$ops = array('display', 'post', 'delete', 'order_list', 'order_detail');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'display';

check_params($op);

if ($op == 'order_list'){
	$field = array('id', 'weid', 'hotelid', 'roomid', 'style', 'nums', 'sum_price');
	$orders = pdo_getall('hotel2_order', array('weid' => intval($_W['uniacid']), 'openid' => $_W['openid']), $field, '', 'time DESC');
	$order_list = array(
		'over' => array(),
		'unfinish' => array(),
	);
	if(!empty($orders)){
		$store_base = pdo_getall('store_bases', array('weid' => intval($_W['uniacid'])), array('id', 'store_type'));
		if(!empty($store_base)){
			$stores = array();
			foreach ($store_base as $val){
				$stores[$val['id']] = $val['store_type'];
			}
			foreach ($orders as $k => $info){
				if(!empty($stores[$info['hotelid']])){
					$orders[$k]['store_type'] = $stores[$info['hotelid']];
				}
			}
		}
		foreach ($orders as $k => $info){
			if(!empty($info['store_type'])){
				if($info['store_type'] == 1){
					$goods_info = pdo_get('hotel2_room', array('weid' => intval($_W['uniacid']), 'id' => $info['roomid']), array('id', 'thumb'));
				}else{
					$goods_info = pdo_get('store_goods', array('weid' => intval($_W['uniacid']), 'id' => $info['roomid']), array('id', 'thumb'));
				}
				if(!empty($goods_info)){
					$info['thumb'] = tomedia($goods_info['thumb']);
				}else{
					continue;
				}
			}else{
				continue;
			}
			if($info['status'] == 3){
				$order_list['over'][] = $info;
			}else{
				$order_list['unfinish'][] = $info;
			}
		}
	}
	message(error(0, $order_list), '', 'ajax');
}

if ($op == 'order_detail'){
	$id = intval($_GPC['id']);
	$order_info = pdo_get('hotel2_order', array('weid' => intval($_W['uniacid']), 'id' => $id));
	if(empty($order_info)){
		message(error(-1, '找不到该订单了'), '', 'ajax');
	}
	message(error(0, $order_info), '', 'ajax');
}