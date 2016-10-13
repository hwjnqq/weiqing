<?php
/**
 * 微外卖模块微站定义
 * @author strday
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$do = 'goods';
mload()->model('store');
mload()->model('order');
mload()->model('member');
checkauth();
$title = '提交订单';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'goods';
$sid = intval($_GPC['sid']);
$store = store_fetch($sid, array('title', 'payment', 'invoice_status', 'delivery_price', 'pack_price', 'delivery_within_days', 'delivery_reserve_days'));
if(empty($store)) {
	message('门店不存在', '', 'error');
}

if($op == 'goods') {
	$cart = order_insert_member_cart($sid);
	if(is_error($cart)) {
		message($cart['message'], '', 'error');
	}
	header('location:' . $this->createMobileUrl('submit', array('sid' => $sid, 'op' => 'index')));
	die;
}

if($op == 'index') {
	if(!$_GPC['r']) {
		$address = pdo_get('tiny_wmall_address', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'is_default' => 1));
		$address_id = $address['id'];
	} else {
		$address_id = intval($_GPC['address_id']);
		$address = member_fetch_address($address_id);
	}
	$cart = order_fetch_member_cart($sid);
	if(empty($cart)) {
		message('商品信息错误', referer(), 'error');
	}
	$pay_types = order_pay_types();
	//支付方式
	if(empty($store['payment'])) {
		message('店铺没有设置有效的支付方式', referer(), 'error');
	}

	//配送时间
	$days = array();
	$totaytime = strtotime(date('Y-m-d'));
	if($store['delivery_reserve_days'] > 0) {
		$days[] = date('m-d', $totaytime + $store['delivery_reserve_days'] * 86400);
	} elseif($store['delivery_within_days'] > 0) {
		for($i = 0; $i < $store['delivery_within_days']; $i++) {
			$days[] = date('m-d', $totaytime + $i * 86400);
		}
	} else {
		$days[] = date('m-d');
	}

	//配送时间段
	$times = pdo_getall('tiny_wmall_store_delivery_times', array('uniacid' => $_W['uniacid'], 'sid' => $sid));
	if(empty($times)) {
		$minut = date('i', TIMESTAMP);
		if($minut <= 30) {
			$minut = 30;
		} elseif($minut > 30 && $minut <= 60) {
			$minut = 60;
		}
		$now = mktime(date('H'), $minut);
		$now_limit = $now + 1000 * 60;
		for($now; $now <= $now_limit; $now += 30 * 60) {
			$times[] = array('start' => date('H:i', $now), 'end' => date('H:i', $now + 1800));
		}
	}

	$order_avtivitys = order_avtivitys();
	$activityed = order_count_activity($sid, $cart['price']);
}

if($op == 'submit') {
	if(!$_W['isajax']) {
		message(error(-1, '非法访问'), '', 'ajax');
	}
	$cart = order_fetch_member_cart($sid);
	if(empty($cart)) {
		message(error(-1, '商品信息错误'), '', 'ajax');
	}
	$address = member_fetch_address($_GPC['address_id']);
	if(empty($address)) {
		message(error(-1, '收货地址信息错误'), '', 'ajax');
	}
	//配送费
	$delivery_price = $store['delivery_price'];
	if($cart['price'] >= $delivery_price) {
		$delivery_price = 0;
	}
	$activityed = order_count_activity($sid, $cart['price']);

	$order = array(
		'uniacid' => $_W['uniacid'],
		'sid' => $sid,
		'uid' => $_W['member']['uid'],
		'ordersn' => date('Ymd') . random(6, true),
		'groupid' => $cart['groupid'],
		'openid' => $_W['openid'],
		'mobile' => $address['mobile'],
		'username' => $address['realname'],
		'sex' => $address['sex'],
		'address' => $address['address'],
		'delivery_day' => trim($_GPC['delivery_day']) ? (date('Y') .'-'. trim($_GPC['delivery_day'])) : date('Y-m-d'),
		'delivery_time' => trim($_GPC['delivery_time']) ? trim($_GPC['delivery_time']) : '尽快送出',
		'delivery_fee' => $delivery_price,
		'pack_fee' => $store['pack_price'],
		'pay_type' => trim($_GPC['pay_type']),
		'num' => $cart['num'],
		'price' => $cart['price'],
		'total_fee' => $cart['price'] + $delivery_price + $store['pack_price'],
		'discount_fee' => $activityed['total'],
		'final_fee' => $cart['price'] + $delivery_price + $store['pack_price'] - $activityed['total'],
		'status' => 1,
		'is_comment' => 0,
		'invoice' => trim($_GPC['invoice']),
		'addtime' => TIMESTAMP,
		'data' => iserializer($cart['data']),
	);
	pdo_insert('tiny_wmall_order', $order);
	$id = pdo_insertid();
	order_insert_discount($id, $sid, $activityed['list']);
	order_insert_status_log($id, $sid, 'place_order');
	order_update_goods_info($id, $sid);
	order_del_member_cart($sid);
	//插入会员下单统计数据
	$_W['member']['realname'] = $address['mobile'];
	$_W['member']['mobile'] = $address['mobile'];
	order_stat_member($sid);
	message(error(0, $id), '', 'ajax');
}
include $this->template('submit');
