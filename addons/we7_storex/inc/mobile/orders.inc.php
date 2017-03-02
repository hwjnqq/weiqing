<?php

defined('IN_IA') or exit('Access Denied');

include IA_ROOT . '/addons/we7_storex/function/function.php';
include IA_ROOT . '/addons/we7_storex/inc/mobile/__init.php';
global $_W, $_GPC;

$ops = array('display', 'post', 'delete', 'order_list', 'order_detail', 'orderpay', 'pay');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'display';

check_params($op);

$_W['openid'] = 'oTKzFjpkpEKpqXibIshcJLsmeLVo';

if ($op == 'order_list'){
	$field = array('id', 'weid', 'hotelid', 'roomid', 'style', 'nums', 'sum_price', 'status', 'paystatus', 'paytype', 'mode_distribute', 'goods_status' ,'openid');
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
			$info = check_order_status($info);
			if($info['status'] == 3){
				$order_list['over'][] = $info;
			}else{
				$order_list['unfinish'][] = $info;
			}
		}
	}
	if($_GPC['debug'] ==1){
		echo "<pre>";
		print_r($order_list);
		echo "</pre>";
		exit;
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
	//订单状态
	$order_info = check_order_status($order_info);
	if($_GPC['debug'] ==1){
		echo "<pre>";
		print_r($order_info);
		echo "</pre>";
		exit;
	}
	message(error(0, $order_info), '', 'ajax');
}

if($op == 'orderpay'){
	$order_id = intval($_GPC['id']);
	$params = pay_info($order_id);
	$pay_info = $this->pay($params);
	if($_GPC['debug'] ==1){
		echo "<pre>";
		print_r($pay_info);
		echo "</pre>";
		exit;
	}
	message(error(0, $pay_info), '', 'ajax');
}

if($op == 'pay'){
	$url = trim($_GPC['url']);
	$pay_url = url($url);
// 	$params = array(
// 			'ordersn' => $order_info['ordersn'],
// 			'tid' => $order_info['id'],
// 			'title' => $_W['account']['name'] . "店铺订单{$order_info['ordersn']}",
// 			'fee' => $order_info['sum_price'],
// 			'user' => $_W['openid'],
// 			'module' => 'we7_storex',
// 	);
// 	$p = base64_encode(json_encode($params));
	$p = trim($_GPC['params']);
	$pay_url.= '&params='.$p;
	header("Location: $pay_url");
}
// echo "<pre>";
// print_r($pay_info);
// print_r(url('mc/cash/wechat'));
// echo "</pre>";
// exit;

