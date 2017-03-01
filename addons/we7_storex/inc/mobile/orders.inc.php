<?php

defined('IN_IA') or exit('Access Denied');
include IA_ROOT . '/addons/we7_storex/function/function.php';
global $_W, $_GPC;
// paycenter_check_login();
$ops = array('display', 'post', 'delete', 'order_list', 'order_detail');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'display';

check_params($op);

if ($op == 'order_list'){
	$field = array('id', 'weid', 'hotelid', 'roomid', 'style', 'nums', 'sum_price', 'status', 'paystatus', 'paytype', 'mode_distribute', 'goods_status');
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
			$info = check_order_status($info);
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
	//订单状态
	$order_info = check_order_status($order_info);
	message(error(0, $order_info), '', 'ajax');
}

function check_order_status($item){
	if(!empty($item['mode_distribute'])){
		if($item['mode_distribute'] == 2){//配送
			if($item['goods_status'] == 1){
				$item['goods_status'] = '未发货';
			}elseif($item['goods_status'] == 2){
				$item['goods_status'] = '已发货';
			}elseif($item['goods_status'] == 3){
				$item['goods_status'] = '已收货';
			}
		}
	}
	if ($item['status'] == 0){
		if ($item['paystatus']== 0){
			$status = '待付款';
		}else{
			$status = '等待店铺确认';
		}
	}else if ($item['status'] == -1){
		if ($item['paystatus']== 0){
			$status = '订单已取消';
		}else{
			if ($item['paytype'] == 3){
				$status = '订单已取消';
			}else{
				$status = '正在退款中';
			}
		}
	}else if ($item['status'] == 1){
		if ($item['paystatus']== 0){
			$status = '待入住';
		}else{
			$status = '待入住';
		}
	}else if ($item['status'] == 2){
		if ($item['paystatus']== 0){
			$status = '店铺已拒绝';
		}else{
			$status = '已退款';
		}
	}else if ($item['status'] == 4){
		$status = '已入住';
	}else if ($item['status'] == 3){
		$status = '已完成';
	}
	$item['order_status'] = $status;
	return $item;
}