<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
mload()->model('order');

$ops = array('order_list', 'order_detail', 'orderpay', 'cancel', 'confirm_goods', 'order_comment', 'refund', 'group_order', 'group_order_detail');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'error';

check_params();
$uid = mc_openid2uid($_W['openid']);

$logs = array(
	'table' => 'storex_order_logs',
	'time' => TIMESTAMP,
	'uid' => $uid,
	'clerk_type' => 1,
	'orderid' => intval($_GPC['id']),
);
if ($op == 'order_list') {
	$field = array('id', 'weid', 'hotelid', 'roomid', 'style', 'nums', 'sum_price', 'status', 'paystatus', 'paytype', 'mode_distribute', 'goods_status', 'action', 'track_number', 'express_name', 'is_package', 'cart');
	$condition = array('weid' => intval($_W['uniacid']), 'openid' => $_W['openid'], 'group_goodsid' => 0, 'group_id' => 0);
	if (pdo_fieldexists('storex_order', 'group_goodsid') && pdo_fieldexists('storex_order', 'group_id')) {
		$condition['group_goodsid'] = 0;
		$condition['group_id'] = 0;
	}
	$orders = pdo_getall('storex_order', $condition, $field, '', 'time DESC');
	$order_list = array(
		'over' => array(),
		'unfinish' => array(),
	);
	if (!empty($orders) && is_array($orders)) {
		$store_base = pdo_getall('storex_bases', array('weid' => intval($_W['uniacid'])), array('id', 'store_type'));
		if (!empty($store_base)) {
			$stores = array();
			foreach ($store_base as $val) {
				$stores[$val['id']] = $val['store_type'];
			}
			foreach ($orders as $k => $info) {
				if (isset($stores[$info['hotelid']])) {
					$orders[$k]['store_type'] = $stores[$info['hotelid']];
				}
			}
		}
		foreach ($orders as $k => $info) {
			if (isset($info['store_type'])) {
				if ($info['store_type'] == 1) {
					$goods_info = pdo_get('storex_room', array('weid' => intval($_W['uniacid']), 'id' => $info['roomid']), array('id', 'thumb'));
				} else {
					if (!empty($info['roomid'])) {
						if ($info['is_package'] == 2) {
							$goods_info = pdo_get('storex_sales_package', array('uniacid' => $_W['uniacid'], 'id' => $info['roomid']), array('title', 'sub_title', 'thumb', 'price', 'id'), 'id');
						} else {
							$goods_info = pdo_get('storex_goods', array('weid' => intval($_W['uniacid']), 'id' => $info['roomid']), array('id', 'thumb'));
						}
					} else {
						if (!empty($info['cart'])) {
							$info['cart'] = iunserializer($info['cart']);
							if ($info['cart'][0]['buyinfo'][2] == 3) {
								$goods_info = pdo_get('storex_sales_package', array('uniacid' => $_W['uniacid'], 'id' => $info['cart'][0]['buyinfo'][0]), array('title', 'sub_title', 'thumb', 'price', 'id'), 'id');
							} else {
								$goods_info = pdo_get('storex_goods', array('weid' => intval($_W['uniacid']), 'id' => $info['cart'][0]['good']['id']), array('id', 'thumb'));
							}
							$info['nums'] = $info['cart'][0]['good']['buynums'];
						}
					}
				}
				if (!empty($goods_info)) {
					$info['thumb'] = tomedia($goods_info['thumb']);
				} else {
					continue;
				}
			} else {
				continue;
			}
			$info = orders_check_status($info);
			if ($info['status'] == 3) {
				$order_list['over'][] = $info;
			} else {
				if ($info['mode_distribute'] != 2 && $info['status'] != -1 && $info['status'] != 2) {
					$info['consume_url'] = murl('entry', array('do' => 'clerk', 'orderid' => $info['id'], 'op' => 'order_consume', 'm' => 'wn_storex'), true, true);
				}
				$order_list['unfinish'][] = $info;
			}
		}
	}
	wmessage(error(0, $order_list), '', 'ajax');
}

if ($op == 'order_detail') {
	$id = intval($_GPC['id']);
	$order_info = pdo_get('storex_order', array('weid' => intval($_W['uniacid']), 'id' => $id, 'openid' => $_W['openid']));
	if (empty($order_info)) {
		wmessage(error(-1, '找不到该订单了'), '', 'ajax');
	}
	unset($order_info['openid']);
	//时间戳转换
	$order_info['btime'] = date('Y-m-d', $order_info['btime']);
	$order_info['etime'] = date('Y-m-d', $order_info['etime']);
	$order_info['time'] = date('Y-m-d', $order_info['time']);
	if (!empty($order_info['mode_distribute'])) {
		$order_info['order_time'] = date('Y-m-d', $order_info['order_time']);//自提或配送时间
	}
	$store_info = pdo_get('storex_bases', array('weid' => intval($_W['uniacid']), 'id' => $order_info['hotelid']), array('id', 'title', 'store_type'));
	$order_info['store_info'] = $store_info;
	$order_info['store_type'] = $store_info['store_type'];
	$order_goods = array();
	if ($order_info['store_type'] == 1) {
		$goods_info = pdo_get('storex_room', array('id' => $order_info['roomid'], 'weid' => $order_info['weid']), array('id', 'is_house', 'thumb'));
		$order_info['is_house'] = $goods_info['is_house'];
		if ($goods_info['is_house'] == 1) {
			$goods_info['day'] = $order_info['day'];
			$goods_info['btime'] = $order_info['btime'];
			$goods_info['etime'] = $order_info['etime'];
		}
		$goods_info['title'] = $order_info['style'];
		$goods_info['thumb'] = tomedia($goods_info['thumb']);
		$goods_info['nums'] = $order_info['nums'];
		$goods_info['oprice'] = $order_info['oprice'];
		$goods_info['cprice'] = $order_info['cprice'];
		$order_goods[] = $goods_info;
	} else {
		if (!empty($order_info['roomid'])) {
			if ($order_info['is_package'] == 2) {
				$goods_info = pdo_get('storex_sales_package', array('uniacid' => $_W['uniacid'], 'id' => $info['roomid']), array('title', 'sub_title', 'thumb', 'price', 'id'));
				$goods_info['oprice'] = $goods_info['price'];
			} else {
				$goods_info = pdo_get('storex_goods', array('id' => $order_info['roomid'], 'weid' => $order_info['weid']), array('id', 'thumb', 'oprice', 'cprice', 'title', 'sub_title'));
				$goods_info['oprice'] = $order_info['oprice'];
			}
			$goods_info['nums'] = $order_info['nums'];
			$goods_info['cprice'] = $order_info['cprice'];
			$goods_info['thumb'] = tomedia($goods_info['thumb']);
			if (!empty($order_info['spec_info'])) {
				$order_info['spec_info'] = iunserializer($order_info['spec_info']);
				if (!empty($order_info['spec_info']['goods_val'])) {
					$goods_info['style'] .= implode(' ', $order_info['spec_info']['goods_val']);
				}
				unset($order_info['spec_info']);
			}
			$order_goods[] = $goods_info;
		} else {
			$order_info['cart'] = iunserializer($order_info['cart']);
			if (!empty($order_info['cart']) && is_array($order_info['cart'])) {
				foreach ($order_info['cart'] as $good) {
					if ($good['buyinfo'][2] == 3) {
						$goods_info = pdo_get('storex_sales_package', array('uniacid' => $_W['uniacid'], 'id' => $info['cart'][0]['buyinfo'][0]), array('title', 'sub_title', 'thumb', 'price', 'id'));
					} elseif ($good['buyinfo'][2] == 2) {
						$goods_info = pdo_get('storex_goods', array('id' => $good['good']['id'], 'weid' => $order_info['weid']), array('id', 'thumb', 'oprice', 'cprice', 'title', 'sub_title'));
					} elseif ($good['buyinfo'][2] == 1) {
						$goods_info = pdo_get('storex_goods', array('id' => $good['good']['id'], 'weid' => $order_info['weid']), array('id', 'thumb', 'oprice', 'cprice', 'title', 'sub_title'));
						$goods_info['style'] = implode(' ', $good['good']['spec_info']['goods_val']);
					}
					$goods_info['oprice'] = $good['good']['oprice'];
					$goods_info['cprice'] = $good['good']['cprice'];
					$goods_info['nums'] = $good['good']['buynums'];
					$goods_info['title'] = $good['good']['title'];
					$goods_info['thumb'] = tomedia($goods_info['thumb']);
					$order_goods[] = $goods_info;
				}
			}
			unset($order_info['cart']);
		}
	}
	$order_info['goods'] = $order_goods;
	if (!empty($order_info['addressid'])) {
		$order_address = pdo_get('mc_member_address', array('uid' => $uid, 'uniacid' => intval($_W['uniacid']), 'id' => $order_info['addressid']));
		if (!empty($order_address)) {
			$order_info['address'] = $order_address['province'] . $order_address['city'] . $order_address['district'] . $order_address['address'];
		}
	}
	//订单状态
	$order_info = orders_check_status($order_info);
	if ($order_info['mode_distribute'] != 2 && $order_info['status'] != -1 && $order_info['status'] != 2) {
		$order_info['consume_url'] = murl('entry', array('do' => 'clerk', 'orderid' => $order_info['id'], 'op' => 'order_consume', 'm' => 'wn_storex'), true, true);
	}
	wmessage(error(0, $order_info), '', 'ajax');
}

if ($op == 'orderpay') {
	$order_id = intval($_GPC['id']);
	$params = pay_info($order_id);
	$pay_info = $this->pay($params);
	wmessage(error(0, $pay_info), '', 'ajax');
}

if ($op == 'cancel') {
	$id = intval($_GPC['id']);
	$order_info = pdo_get('storex_order', array('weid' => intval($_W['uniacid']), 'id' => $id, 'openid' => $_W['openid']));
	$store_info = get_store_info($order_info['hotelid']);
	$order_info['store_type'] = $store_info['store_type'];
	$order_info = orders_check_status($order_info);
	if ($order_info['is_cancel'] == 2 || $order_info['status'] == 3) {
		wmessage(error(-1, '该订单不能取消！'), '', 'ajax');
	}
	$update_data = array('status' => -1);
	$result = pdo_update('storex_order', $update_data, array('id' => $id, 'weid' => $_W['uniacid']));
	if (!empty($order_info['coupon'])) {
		delete_room_assign($order_info);
		pdo_update('storex_coupon_record', array('status' => 1), array('id' => $order_info['coupon']));
	}
	if (!empty($result)) {
		$logs['before_change'] = $order_info['status'];
		$logs['after_change'] = -1;
		$logs['type'] = 'status';
		write_log($logs);
		order_update_newuser($id);
		wmessage(error(0, '订单成功取消！'), '', 'ajax');
	} else {
		wmessage(error(-1, '订单取消失败！'), '', 'ajax');
	}
}

if ($op == 'refund') {
	$id = intval($_GPC['id']);
	$order = pdo_get('storex_order', array('id' => $id), array('id', 'paytype', 'refund_status', 'hotelid'));
	$store_info = get_store_info($order['hotelid']);
	if ($store_info['refund'] == 1 && (check_ims_version() || $item['paytype'] == 'credit')) {
		$result = order_build_refund($id);
		if (is_error($result)) {
			wmessage($result, '', 'ajax');
		} else {
			$logs['before_change'] = $order['refund_status'];
			$logs['after_change'] = 1;
			$logs['type'] = 'refund_status';
			write_log($logs);
			wmessage(error(0, '退款申请成功'), '', 'ajax');
		}
	} else {
		wmessage(error(-1, '退款失败'), '', 'ajax');
	}
}

if ($op == 'confirm_goods') {
	$id = intval($_GPC['id']);
	$order_info = pdo_get('storex_order', array('weid' => intval($_W['uniacid']), 'id' => $id, 'openid' => $_W['openid']));
	$store_info = pdo_get('storex_bases', array('weid' => intval($_W['uniacid']), 'id' => $order_info['hotelid']), array('id', 'store_type'));
	$order_info['store_type'] = $store_info['store_type'];
	$order_info = orders_check_status($order_info);
	if ($order_info['status'] == -1) {
		wmessage(error(-1, '该订单已经取消了！'), '', 'ajax');
	}
	if ($order_info['status'] == 3) {
		wmessage(error(-1, '该订单已经完成了！'), '', 'ajax');
	}
	if ($order_info['mode_distribute'] == 1) {
		wmessage(error(-1, '订单方式不是配送！'), '', 'ajax');
	}
	$result = pdo_update('storex_order', array('goods_status' => 3), array('id' => $id, 'weid' => $_W['uniacid']));
	if (!empty($order_info['coupon'])) {
		pdo_update('storex_coupon_record', array('status' => 3), array('id' => $order_info['coupon']));
	}
	if (!empty($result)) {
		$logs['before_change'] = $order_info['goods_status'];
		$logs['after_change'] = 3;
		$logs['type'] = 'goods_status';
		write_log($logs);
		wmessage(error(0, '订单收货成功！'), '', 'ajax');
	} else {
		wmessage(error(-1, '订单收货失败！'), '', 'ajax');
	}
}

if ($op == 'order_comment') {
	$id = intval($_GPC['id']);
	$comment_level = intval($_GPC['comment_level']);
	$comment = trim($_GPC['comment']);
	if (empty($comment)) {
		wmessage(error(-1, '评价不能为空！'), '', 'ajax');
	}
	$order_info = pdo_get('storex_order', array('weid' => intval($_W['uniacid']), 'id' => $id, 'openid' => $_W['openid']));
	if (empty($order_info)) {
		wmessage(error(-1, '找不到该订单了！'), '', 'ajax');
	}
	if ($comment_level > 5 || $comment_level < 1 || empty($comment_level)) {
		$comment_level = 5;
	}
	if ($order_info['status'] == 3 && $order_info['comment'] == 0) {
		$fans_info = mc_fansinfo($uid);
		$comment_info = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $uid,
			'createtime' => time(),
			'comment' => $comment,
			'hotelid' => $order_info['hotelid'],
			'goodsid' => $order_info['roomid'], 
			'comment_level' => $comment_level,
			'type' => 1,
			'cid' => 0,
			'nickname' => $fans_info['nickname'],
			'thumb' => $fans_info['headimgurl']
		);
		pdo_insert('storex_comment', $comment_info);
		pdo_update('storex_order', array('comment' => 1), array('weid' => $_W['uniacid'], 'id' => $id));
		wmessage(error(0, '评论成功！'), '', 'ajax');
	} else {
		wmessage(error(-1, '订单已经评价过了！'), '', 'ajax');
	}
}

if ($op == 'group_order') {
	$fields = array('id', 'weid', 'hotelid', 'roomid', 'style', 'nums', 'sum_price', 'status', 'paystatus', 'paytype', 'mode_distribute', 'goods_status', 'track_number', 'express_name', 'is_package', 'group_goodsid', 'group_id');
	$orders = pdo_getall('storex_order', array('openid' => $_W['openid'], 'group_goodsid !=' => 0, 'group_id !=' => 0), $fields, '', 'time DESC');
	if (!empty($orders)) {
		$storex_bases = array();
		$group_goodsids = array();
		$goodsids = array();
		$group_ids = array();
		foreach ($orders as $order) {
			$storex_bases_ids[] = $order['hotelid'];
			$group_goodsids[] = $order['group_goodsid'];
			$group_ids[] = $order['group_id'];
			$goodsids[] = $order['roomid'];
		}
		$storex_bases = pdo_getall('storex_bases', array('id' => $storex_bases_ids), array('id', 'title'), 'id');

		$group_goods = pdo_getall('storex_plugin_activity_goods', array('id' => $group_goodsids), array(), 'id');
		
		$activity_group_ids = array();
		foreach ($group_goods as $info) {
			$activity_group_ids[] = $info['group_activity'];
		}
		$activity_group = pdo_getall('storex_plugin_group_activity', array('id' => $activity_group_ids), array(), 'id');

		$groups = pdo_getall('storex_plugin_group', array('id' => $group_ids), array(), 'id');

		$goods = pdo_getall('storex_goods', array('id' => $goodsids), array('id', 'title', 'thumb'), 'id');
		foreach ($orders as &$order) {
			$group_id = $order['group_id'];
			if (!empty($goods[$order['roomid']])) {
				$order['title'] = $goods[$order['roomid']]['title'];
				$order['thumb'] = tomedia($goods[$order['roomid']]['title']);
			}
			$order = orders_check_status($order);
			//店铺标题
			$order['store_title'] = $storex_bases[$order['hotelid']]['title'];
			$number = $group_goods[$order['group_goodsid']]['number'];
			$member = iunserializer($groups[$group_id]['member']);
			if (!empty($member) && is_array($member)) {
				$order['need_member'] = $number - (count($member) + 1);
			}  else {
				$order['need_member'] = $number - 1;
			}
			$order['over'] = $groups[$group_id]['over']; //1完成2未完成3已退款
			$order['endtime'] = date('Y/m/d H:i:s', $activity_group[$groups[$group_id]['group_activity_id']]['endtime']);
			$order['is_cancel'] = 2;
			$order['is_refund'] = 2;
		}
		unset($order);
	}
	wmessage(error(0, $orders), '', 'ajax');
}