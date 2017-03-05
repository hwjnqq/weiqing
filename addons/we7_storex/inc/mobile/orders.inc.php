<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('order_list', 'order_detail', 'orderpay', 'cancel', 'confirm_goods');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'error';

check_params();
if ($op == 'order_list'){
	$field = array('id', 'weid', 'hotelid', 'roomid', 'style', 'nums', 'sum_price', 'status', 'paystatus', 'paytype', 'mode_distribute', 'goods_status', 'openid', 'action');
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
				if(isset($stores[$info['hotelid']])){
					$orders[$k]['store_type'] = $stores[$info['hotelid']];
				}
			}
		}
		foreach ($orders as $k => $info){
			if(isset($info['store_type'])){
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
			$info = orders_check_status($info);
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
	//时间戳转换
	$order_info['btime'] = date('Y-m-d', $order_info['btime']);
	$order_info['etime'] = date('Y-m-d', $order_info['etime']);
	$order_info['time'] = date('Y-m-d', $order_info['time']);
	if(!empty($order_info['mode_distribute'])){
		$order_info['order_time'] = date('Y-m-d', $order_info['order_time']);//自提或配送时间
	}
	$store_info = pdo_get('store_bases', array('weid' => intval($_W['uniacid']), 'id' => $order_info['hotelid']), array('id', 'title', 'store_type'));
	$order_info['store_info'] = $store_info;
	if (!empty($order_info['addressid'])){
		$order_info['address'] = pdo_getall('mc_member_address', array('uid' => $uid, 'uniacid' => intval($_W['uniacid']), 'id' => $order_info['addressid']));
	}
	//订单状态
	$order_info = orders_check_status($order_info);
	message(error(0, $order_info), '', 'ajax');
}

if ($op == 'orderpay'){
	$order_id = intval($_GPC['id']);
	$params = pay_info($order_id);
	$pay_info = $this->pay($params);
	message(error(0, $pay_info), '', 'ajax');
}

if ($op == 'cancel'){
	$id = intval($_GPC['id']);
	$order_info = pdo_get('hotel2_order', array('weid' => intval($_W['uniacid']), 'id' => $id));
	$order_info = orders_check_status($order_info);
	$setting = pdo_get('hotel2_set', array('weid' => intval($_W['uniacid'])));
	if ($setting['refund'] == 1){
		message(error(-1, '该店铺不能取消订单！'), '', 'ajax');
	}
	$order_info = orders_check_status($order_info);
	if ($order_info['is_cancle'] == 2 || $order_info['status'] == 3){
		message(error(-1, '该订单不能取消！'), '', 'ajax');
	}
	pdo_update('hotel2_order', array('status' => -1), array('id' => $id, 'weid' => $_W['uniacid']));
	message(error(0, '订单成功取消！'), '', 'ajax');
}

if ($op == 'confirm_goods'){
	$id = intval($_GPC['id']);
	$order_info = pdo_get('hotel2_order', array('weid' => intval($_W['uniacid']), 'id' => $id));
	$order_info = orders_check_status($order_info);
	if ($order_info['status'] == -1){
		message(error(-1, '该订单已经取消了！'), '', 'ajax');
	}
	if ($order_info['status'] == 3){
		message(error(-1, '该订单已经完成了！'), '', 'ajax');
	}
	if ($order_info['mode_distribute'] == 1){
		message(error(-1, '订单方式不是配送！'), '', 'ajax');
	}
	pdo_update('hotel2_order', array('goods_status' => 3), array('id' => $id, 'weid' => $_W['uniacid']));
	message(error(0, '订单收货成功！'), '', 'ajax');
}