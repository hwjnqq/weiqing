<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');
mload()->model('card');
mload()->model('order');
mload()->model('clerk');
mload()->model('activity');
mload()->model('log');
	
$ops = array('permission_storex', 'order', 'order_info', 'edit_order', 'room', 'room_info', 'edit_room', 'assign_room', 'goods', 'status', 'clerk_pay', 'order_consume', 'coupon_consume', 'couponcode', 'code_consume');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

$uid = mc_openid2uid($_W['openid']);
check_params();

if ($op == 'permission_storex') {
	$type = trim($_GPC['type']);
	$manage_storex_lists = clerk_permission_storex($type);
	wmessage(error(0, $manage_storex_lists), '', 'ajax');
}

if ($op == 'order') {
	$manage_storex_lists = clerk_permission_storex($op);
	$manage_storex_ids = array_keys($manage_storex_lists);
	pdo_query("UPDATE " . tablename('storex_order') . " SET status = -1, newuser = 0 WHERE time < :time AND weid = :uniacid AND paystatus = 0 AND status <> 1 AND status <> 3", array(':time' => TIMESTAMP - 86400, ':uniacid' => intval($_W['uniacid'])));
	$operation_status = array(ORDER_STATUS_CANCEL, ORDER_STATUS_NOT_SURE, ORDER_STATUS_SURE, ORDER_STATUS_REFUSE);
	$goods_status = array(0, GOODS_STATUS_NOT_SHIPPED, GOODS_STATUS_SHIPPED, GOODS_STATUS_NOT_CHECKED);
	$order_lists = pdo_getall('storex_order', array('weid' => intval($_W['uniacid']), 'hotelid' => $manage_storex_ids, 'status' => $operation_status, 'goods_status' => $goods_status), array('id', 'weid', 'hotelid', 'paystatus','roomid', 'style', 'btime', 'etime', 'roomitemid', 'status', 'goods_status', 'mode_distribute', 'nums', 'sum_price', 'day', 'is_package', 'cart'), '', 'id DESC');
	if (!empty($order_lists) && is_array($order_lists)) {
		$lists = array();
		$goods_ids = array('storex_room' => array(), 'storex_goods' => array());
		foreach ($order_lists as $k => &$info) {
			if ($info['is_package'] == 2) {
				$packageids[] = $info['roomid'];
			}
			if (!empty($manage_storex_lists[$info['hotelid']])) {
				$store_type = $manage_storex_lists[$info['hotelid']]['store_type'];
				$info = clerk_order_operation($info, $store_type);
				if (empty($info['operate'])) {
					continue;
				}
			} else {
				continue;
			}
			if (!empty($info['roomid'])) {
				if ($store_type == STORE_TYPE_HOTEL) {
					$goods_ids['storex_room'][] = $info['roomid'];
				} else {
					$goods_ids['storex_goods'][] = $info['roomid'];
				}
			} else {
				if (!empty($info['cart'])) {
					$info['cart'] = iunserializer($info['cart']);
					foreach ($info['cart'] as $g) {
						$goodinfo = pdo_get('storex_goods', array('id' => $g['good']['id']), array('id', 'thumb'));
						$info['thumb'] = tomedia($goodinfo['thumb']);
						$info['nums'] = $g['good']['buynums'];
						$info['style'] = $g['good']['title'];
						break;
					}
				}
			}
			$lists[] = $info;
		}
		unset($info);
		$goods_thumbs = array();
		foreach ($goods_ids as $t => $ids) {
			$goods_thumbs[$t] = pdo_getall($t, array('id' => $ids), array('id', 'thumb'), 'id');
		}
		$packageids = is_array($packageids) ? array_unique($packageids) : array();
		$sales_package = pdo_getall('storex_sales_package', array('uniacid' => $_W['uniacid'], 'id' => $packageids), array('title', 'sub_title', 'thumb', 'price', 'id'), 'id');
		foreach ($lists as $k => &$val) {
			if (!isset($val['thumb'])) {
				$val['thumb'] = '';
			}
			if ($val['is_package'] == 2) {
				$val['thumb'] = $sales_package[$val['roomid']]['thumb'];
			} else {
				$store_type = $manage_storex_lists[$val['hotelid']]['store_type'];
				$table = gettablebytype($store_type);
				if (!empty($goods_thumbs[$table][$val['roomid']])) {
					$val['thumb'] = tomedia($goods_thumbs[$table][$val['roomid']]['thumb']);
				}
			}
		}
		unset($val);
		$order_data = array();
		$order_data['order_lists'] = $lists;
		wmessage(error(0, $order_data), '', 'ajax');
	} else {
		wmessage(error(-1, '没有订单可操作！'), '', 'ajax');
	}
}

if ($op == 'order_info') {
	$orderid = intval($_GPC['orderid']);
	if (!empty($orderid)) {
		$order = pdo_get('storex_order', array('id' => $orderid));
		if (!empty($order)) {
			$store_info = clerk_permission_storex('order', $order['hotelid']);
			if (!empty($store_info[$order['hotelid']])) {
				$table = gettablebytype($store_info[$order['hotelid']]['store_type']);
				$fields = array('id', 'thumb');
				$order['goods'] = array();
				if (!empty($order['roomid'])) {
					if ($store_info[$order['hotelid']]['store_type'] == STORE_TYPE_HOTEL) {
						$fields[] = 'is_house';
					}
					$goods = pdo_get($table, array('id' => $order['roomid']), $fields);
					$goods['title'] = $order['style'];
					$goods['oprice'] = $order['oprice'];
					$goods['cprice'] = $order['cprice'];
					$goods['nums'] = $order['nums'];
					if ($goods['is_house'] == 1) {
						$goods['day'] = $order['day'];
						$goods['btime'] = $order['btime'];
						$goods['etime'] = $order['etime'];
					}
					if (!empty($order['spec_info'])) {
						$order['spec_info'] = iunserializer($order['spec_info']);
						$goods['style'] = implode(' ', $order['spec_info']['goods_val']);
					}
					$goods['thumb'] = tomedia($goods['thumb']);
					if (isset($goods['is_house']) && $goods['is_house'] == 1) {
						$room_list = pdo_getall('storex_room_items', array('uniacid' => $_W['uniacid'], 'storeid' => $order['hotelid'], 'roomid' => $order['roomid']));
						if (!empty($room_list) && is_array($room_list)) {
							foreach ($room_list as $r => $val) {
								$show = check_room_assign($order, $val['id']);
								if (empty($show)) {
									unset($room_list[$r]);
								}
							}
							$goods['room_list'] = $room_list;
							$goods['rooms'] = array();
							if (!empty($order['roomitemid'])) {
								$room_item = pdo_getall('storex_room_items', array('uniacid' => $_W['uniacid'], 'storeid' => $order['hotelid'], 'id' => explode(',', $order['roomitemid'])), array('id', 'roomnumber'));
								if (!empty($room_item) && is_array($room_item)) {
									foreach ($room_item as $roomitem) {
										$goods['rooms'][] = $roomitem['roomnumber'];
									}
								}
							}
						}
					}
					$order['goods'][] = $goods;
				} else {
					$order['cart'] = iunserializer($order['cart']);
					if (!empty($order['cart']) && is_array($order['cart'])) {
						foreach ($order['cart'] as $good) {
							if ($good['buyinfo'][2] == 3) {
								$goods = pdo_get('storex_sales_package', array('uniacid' => $_W['uniacid'], 'id' => $good[0]['buyinfo'][0]), array('title', 'sub_title', 'thumb', 'price', 'id'));
							} elseif ($good['buyinfo'][2] == 2) {
								$goods = pdo_get('storex_goods', array('id' => $good['good']['id'], 'weid' => $order_info['weid']), array('id', 'thumb', 'oprice', 'cprice', 'title', 'sub_title'));
							} elseif ($good['buyinfo'][2] == 1) {
								$goods = pdo_get('storex_goods', array('id' => $good['good']['id'], 'weid' => $order_info['weid']), array('id', 'thumb', 'oprice', 'cprice', 'title', 'sub_title'));
								$goods['style'] = implode(' ', $good['good']['spec_info']['goods_val']);
							}
							$goods['oprice'] = $good['good']['oprice'];
							$goods['cprice'] = $good['good']['cprice'];
							$goods['nums'] = $good['good']['buynums'];
							$goods['title'] = $good['good']['title'];
							$goods['thumb'] = tomedia($goods['thumb']);
							$order['goods'][] = $goods;
						}
					}
					unset($order['cart']);
				}
				$order = clerk_order_operation($order, $store_info[$order['hotelid']]['store_type']);
				wmessage(error(0, $order), '', 'ajax');
			}
		}
	}
	wmessage(error(-1, '抱歉，订单不存在或是已经删除！'), '', 'ajax');
}

if ($op == 'edit_order') {
	$orderid = intval($_GPC['orderid']);
	if (empty($orderid)) {
		wmessage(error(-1, '参数错误！'), '', 'ajax');
	}
	$item = pdo_get('storex_order', array('id' => $orderid));
	if (empty($item)) {
		wmessage(error(-1, '抱歉，订单不存在或是已经删除'), '', 'ajax');
	}
	$store_info = clerk_permission_storex('order', $item['hotelid']);
	$table = gettablebytype($store_info[$item['hotelid']]['store_type']);
	$goodsid = intval($item['roomid']);
	$fields = array('id', 'title');
	if ($table == 'storex_room') {
		$fields = array('id', 'title', 'is_house');
	}
	$goods_info = pdo_get($table, array('id' => $goodsid), $fields);
	$actions_status = array(
		'is_cancel' => ORDER_STATUS_CANCEL,
		'is_confirm' => ORDER_STATUS_SURE,
		'is_refuse' => ORDER_STATUS_REFUSE,
		'is_over' => ORDER_STATUS_OVER,
	);
	$type = trim($_GPC['type']);
	$data = array(
		'status' => '',
		'msg' => $_GPC['msg'],
		'track_number' => $_GPC['track_number'],
		'goods_status' => '',
	);
	if (!empty($actions_status[$type])) {
		$data['status'] = $actions_status[$type];
	}
	if ($type == 'is_access') {
		$data['goods_status'] = GOODS_STATUS_CHECKED;
	}
	if ($type == 'is_send') {
		$data['goods_status'] = GOODS_STATUS_SHIPPED;
	}
	if (!empty($data['status'])) {
		if ($item['status'] == ORDER_STATUS_CANCEL) {
			wmessage(error(-1, '订单状态已经取消，不能操作！'), '', 'ajax');
		}
		if ($item['status'] == ORDER_STATUS_OVER) {
			wmessage(error(-1, '订单状态已经完成，不能操作！'), '', 'ajax');
		}
		if ($item['status'] == ORDER_STATUS_REFUSE) {
			wmessage(error(-1, '订单状态已拒绝，不能操作！'), '', 'ajax');
		}
		if ($data['status'] == $item['status']) {
			wmessage(error(-1, '订单状态已经是该状态了，不要重复操作！'), '', 'ajax');
		}
	}
	
	if (!empty($data['goods_status']) && ($data['goods_status'] == GOODS_STATUS_SHIPPED || $data['goods_status'] == GOODS_STATUS_CHECKED)) {
		if ($item['status'] != ORDER_STATUS_SURE) {
			wmessage(error(-1, '请先确认订单！'), '', 'ajax');
		}
		if ($item['goods_status'] == GOODS_STATUS_RECEIVED) {
			wmessage(error(-1, '已收货，不要再发了！'), '', 'ajax');
		}
		if ($item['goods_status'] == GOODS_STATUS_SHIPPED) {
			wmessage(error(-1, '已发货，不要重复操作！'), '', 'ajax');
		}
		if ($item['goods_status'] == GOODS_STATUS_CHECKED) {
			wmessage(error(-1, '已入住！'), '', 'ajax');
		}
		
	}
	if (empty($data['status']) && empty($data['goods_status'])) {
		wmessage(error(-1, '操作失败！'), '', 'ajax');
	}
	//订单取消
	if ($data['status'] == ORDER_STATUS_CANCEL || $data['status'] == ORDER_STATUS_REFUSE) {
		if ($store_info[$item['hotelid']]['store_type'] == STORE_TYPE_HOTEL) {
			$params = array();
			$sql = "SELECT id, roomdate, num FROM " . tablename('storex_room_price');
			$sql .= " WHERE 1 = 1";
			$sql .= " AND roomid = :roomid";
			$sql .= " AND roomdate >= :btime AND roomdate < :etime";
			$sql .= " AND status = 1";
			$params[':roomid'] = $item['roomid'];
			$params[':btime'] = $item['btime'];
			$params[':etime'] = $item['etime'];
			$room_date_list = pdo_fetchall($sql, $params);
			if ($room_date_list) {
				foreach ($room_date_list as $key => $value) {
					if ($value['num'] >= 0) {
						$now_num = $value['num'] + $item['nums'];
						pdo_update('storex_room_price', array('num' => $now_num), array('id' => $value['id']));
					}
				}
			}
		}
	}
	$logs = array(
		'time' => TIMESTAMP,
		'uid' => $uid,
		'clerk_type' => 3,
		'orderid' => $item['id'],
		'table' => 'storex_order_logs',
	);
	if (!empty($item['cart'])) {
		$item['cart'] = iunserializer($item['cart']);
		$item['style'] = $item['cart'][0]['good']['title'];
	}
	$params = array();
	$params['room'] = $goods_info['title'];
	$params['store'] = $store_info[$item['hotelid']]['title'];
	$params['store_type'] = $store_info[$item['hotelid']]['store_type'];
	$params['openid'] = $item['openid'];
	$params['btime'] = $item['btime'];
	$params['tpl_status'] = false;
	$setting = array();
	if (!empty($store_info[$item['hotelid']]['template'])) {
		$setting = iunserializer($store_info[$item['hotelid']]['template']);
		if (!empty($setting['template'])) {
			$params['tpl_status'] = true;
		}
	}
	if (!empty($data['status']) && $data['status'] != $item['status']) {
		$logs['type'] = 'status';
		$logs['before_change'] = $item['status'];
		$logs['after_change'] = $data['status'];
		//订单拒绝
		if ($data['status'] == ORDER_STATUS_REFUSE) {
			$params['ordersn'] = $item['ordersn'];
			$params['nums'] = $item['nums'];
			$params['sum_price'] = $item['sum_price'];
			$params['etime'] = $item['etime'];
			$params['refuse_templateid'] = isset($setting['refuse_templateid']) ? $setting['refuse_templateid'] : '';
			order_refuse_notice($params);
		}
		//订单确认提醒
		if ($data['status'] == ORDER_STATUS_SURE) {
			$params['ordersn'] = $item['ordersn'];
			$params['style'] = $item['style'];
			$params['sum_price'] = $item['sum_price'];
			if ($store_info[$item['hotelid']]['store_type'] == STORE_TYPE_HOTEL) {
				if (!empty($goods_info) && $goods_info['is_house'] == 1) {
					$data['goods_status'] = GOODS_STATUS_NOT_CHECKED;
				}
			} else {
				$data['goods_status'] = GOODS_STATUS_NOT_SHIPPED;
			}
			if ($store_info[$item['hotelid']]['store_type'] == STORE_TYPE_HOTEL && !empty($setting['templateid'])) {
				$params['contact_name'] = $item['contact_name'];
				$params['etime'] = $item['etime'];
				$params['nums'] = $item['nums'];
				$params['templateid'] = isset($setting['templateid']) ? $setting['templateid'] : '';
				order_sure_notice($params);
			} else {
				$params['paytext'] = get_paytext($item['paytype']);
				order_affirm_notice($params);
			}
			
			if (check_plugin_isopen('wn_storex_plugin_sms')) {
				mload()->model('sms');
				$content = array(
					'store' => $store_info[$item['hotelid']]['title'],
					'ordersn' => $item['ordersn'],
					'price' => $item['sum_price'],
				);
				sms_send($item['mobile'], $content, 'user');
			}
		}
	
		//订单完成提醒
		if ($data['status'] == ORDER_STATUS_OVER) {
			if (empty($item['status'])) {
				wmessage(error(-1, '请先确认订单再完成！'), '', 'ajax');
			}
			$uid = mc_openid2uid(trim($item['openid']));
			//订单完成后增加积分
			card_give_credit($uid, $item['sum_price']);
			//增加出售货物的数量
			add_sold_num($goods_info);
			
			$params['sum_price'] = $item['sum_price'];
			$params['etime'] = $item['etime'];
			$params['finish_templateid'] = isset($setting['finish_templateid']) ? $setting['finish_templateid'] : '';
			order_over_notice($params);
			
			mload()->model('sales');
			sales_update(array('storeid' => $item['hotelid'], 'sum_price' => $item['sum_price']));
		}
		if ($data['status'] == ORDER_STATUS_CANCEL) {
			$info = '您在' . $store_info[$item['hotelid']]['title'] . '预订的' . $goods_info['title'] . "订单" . $item['ordersn'] . "已取消，请联系管理员！";
			$status = send_custom_notice('text', array('content' => urlencode($info)), $item['openid']);
		}
	}
	
	if (!empty($data['goods_status'])) {
		$params['phone'] = $store_info[$item['hotelid']]['phone'];
		if ($data['goods_status'] == GOODS_STATUS_CHECKED || $data['goods_status'] == GOODS_STATUS_SHIPPED) {
			$logs['type'] = 'goods_status';
			$logs['before_change'] = $item['goods_status'];
			$logs['after_change'] = $data['goods_status'];
		}
		//已入住提醒
		if ($data['goods_status'] == GOODS_STATUS_CHECKED) {
			$params['check_in_templateid'] = isset($setting['check_in_templateid']) ? $setting['check_in_templateid'] : '';
			order_checked_notice($params);
		}
		//发货设置
		if ($data['goods_status'] == GOODS_STATUS_SHIPPED) {
			if (!empty($params['tpl_status']) && empty($setting['send_templateid'])) {
				$params['send_templateid'] = $setting['send_templateid'];
				$params['express_name'] = $item['express_name'];
				$params['track_number'] = $item['track_number'];
				$params['style'] = $item['style'];
				order_send_notice($params);
			} else {
				$info = '您在' . $store_info[$item['hotelid']]['title'] . '预订的' . $goods_info['title'] . "已发货,订单编号:" . $item['ordersn'];
				$status = send_custom_notice('text', array('content' => urlencode($info)), $item['openid']);
			}
		}
	}
	$result = pdo_update('storex_order', $data, array('id' => $orderid));
	if (!empty($result)) {
		log_write($logs);
		if (in_array($data['status'], array(-1, 2))) {
			order_update_newuser($orderid);
			delete_room_assign($item);
		}
		if ($data['status'] == ORDER_STATUS_OVER) {
			order_market_gift($id);
			order_salesman_income($id, ORDER_STATUS_OVER);
		}
		wmessage(error(0, '处理订单成功！'), '', 'ajax');
	} else {
		wmessage(error(-1, '处理订单失败！'), '', 'ajax');
	}
}

if ($op == 'room') {
	$manage_storex_lists = clerk_permission_storex($op);
	$manage_storex_ids = array_keys($manage_storex_lists);
	$room_list = pdo_getall('storex_room', array('recycle' => 2, 'store_base_id' => $manage_storex_ids, 'weid' => intval($_W['uniacid']), 'is_house' => 1), array('id', 'store_base_id', 'weid', 'title', 'thumb', 'oprice', 'cprice', 'service', 'store_type', 'is_house'));
	if (!empty($room_list) && is_array($room_list)) {
		foreach ($room_list as $k => $info) {
			if ($info['store_type'] != STORE_TYPE_HOTEL || $info['is_house'] != 1) {
				unset($room_list[$k]);
			}
			if (!empty($manage_storex_lists[$info['store_base_id']])) {
				$room_list[$k]['store_title'] = $manage_storex_lists[$info['store_base_id']]['title'];
			}
		}
	}
	wmessage(error(0, $room_list), '', 'ajax');
}

if ($op == 'room_info') {
	$room_id = intval($_GPC['room_id']);
	$room_info = pdo_get('storex_room', array('recycle' => 2, 'id' => $room_id), array('id', 'store_base_id', 'weid', 'title', 'oprice', 'cprice', 'thumb'));
	if (empty($room_info)) {
		wmessage(error(-1, '不存在此房型！'), '', 'ajax');
	}
	$room_info['thumb'] = tomedia($room_info['thumb']);
	$manage_storex_lists = clerk_permission_storex('room', $room_info['store_base_id']);
	if (empty($manage_storex_lists)) {
		wmessage(error(-1, '你没有维护房型的权限'), '', 'ajax');
	}

	$start_time = $_GPC['start_time'] ? $_GPC['start_time'] : date('Y-m-d');
	$end_time = $_GPC['end_time']? $_GPC['end_time'] : date('Y-m-d');
	$days = intval((strtotime($end_time) - strtotime($start_time)) / 86400) + 1;
	$btime = strtotime($start_time);
	$etime = strtotime($end_time);
	$dates = get_dates($start_time, $days);
	$item = array();
	$sql = "SELECT * FROM " . tablename('storex_room_price');
	$sql .= " WHERE weid = :weid ";
	$sql .= " AND roomid = :roomid ";
	$sql .= " AND roomdate >= " . $btime;
	$sql .= " AND roomdate < " . ($etime + 86400);
	$item = pdo_fetchall($sql, array(':weid' => intval($_W['uniacid']), ':roomid' => $room_id));
	$flag = 0;
	if (!empty($item)) {
		$flag = 1;
	}
	$room_info['price_list'] = array();
	if ($flag == 1) {
		for ($i = 0; $i < $days; $i++) {
			$k = $dates[$i]['date'];
			foreach ($item as $p_key => $p_value) {
				if ($p_value['roomid'] != $room_info['id']) {
					continue;
				}
				//判断价格表中是否有当天的数据
				if ($p_value['thisdate'] == $k) {
					$room_info['price_list'][$k]['oprice'] = $p_value['oprice'];
					$room_info['price_list'][$k]['cprice'] = $p_value['cprice'];
					$room_info['price_list'][$k]['roomid'] = $room_info['id'];
					$room_info['price_list'][$k]['hotelid'] = $room_info['store_base_id'];
					$room_info['price_list'][$k]['status'] = $p_value['status'];
					if (empty($p_value['num'])) {
						$room_info['price_list'][$k]['num'] = "0";
						$room_info['price_list'][$k]['status'] = 0;
					} elseif ($p_value['num'] == -1) {
						$room_info['price_list'][$k]['num'] = "-1";
					} else {
						$room_info['price_list'][$k]['num'] = $p_value['num'];
					}
					break;
				}
			}
			//价格表中没有当天数据
			if (empty($room_info['price_list'][$k])) {
				$room_info['price_list'][$k]['num'] = "-1";
				$room_info['price_list'][$k]['status'] = 1;
				$room_info['price_list'][$k]['roomid'] = $room_info['id'];
				$room_info['price_list'][$k]['hotelid'] = $room_info['store_base_id'];
				$room_info['price_list'][$k]['oprice'] = $room_info['oprice'];
				$room_info['price_list'][$k]['cprice'] = $room_info['cprice'];
			}
		}
	} else {
		//价格表中没有数据
		for ($i = 0; $i < $days; $i++) {
			$k = $dates[$i]['date'];
			$room_info['price_list'][$k]['num'] = "-1";
			$room_info['price_list'][$k]['status'] = 1;
			$room_info['price_list'][$k]['roomid'] = $room_info['id'];
			$room_info['price_list'][$k]['hotelid'] = $room_info['store_base_id'];
			$room_info['price_list'][$k]['oprice'] = $room_info['oprice'];
			$room_info['price_list'][$k]['cprice'] = $room_info['cprice'];
		}
	}
	wmessage(error(0, $room_info), '', 'ajax');
}

if ($op == 'edit_room') {
	$room_id = intval($_GPC['room_id']);
	$room_info = pdo_get('storex_room', array('recycle' => 2, 'id' => $room_id), array('id', 'store_base_id', 'weid', 'title', 'oprice', 'cprice', 'thumb'));
	if (empty($room_info)) {
		wmessage(error(-1, '不存在此房型！'), '', 'ajax');
	}
	$room_info['thumb'] = tomedia($room_info['thumb']);
	$manage_storex_lists = clerk_permission_storex('room', $room_info['store_base_id']);
	if (empty($manage_storex_lists)) {
		wmessage(error(-1, '你没有维护房型的权限'), '', 'ajax');
	}
	
	if (!empty($_GPC['dates'])) {
		$dates = explode(',', $_GPC['dates']);
	} else {
		$dates = array(date('Y-m-d'));
	}
	$num = intval($_GPC['num']);
	$status = 1;
	if ($num == 0) {
		$status = 0;
	}
	if ($num < -1) {
		wmessage(error(-1, '房间数量错误！'), '', 'ajax');
	}
	$oprice = sprintf('%.2f', $_GPC['oprice']);
	$cprice = sprintf('%.2f', $_GPC['cprice']);
	if ($oprice <= 0 || $cprice <= 0) {
		wmessage(error(-1, '价格不能小于等于0！'), '', 'ajax');
	}
	if ($oprice < $cprice) {
		wmessage(error(-1, '价格错误！'), '', 'ajax');
	}
	if (!empty($dates) && is_array($dates)) {
		foreach ($dates as $date) {
			$roomprice = getRoomPrice($room_info['store_base_id'], $room_id, $date);
			$roomprice['num'] = $num;
			$roomprice['status'] = $status;
			$roomprice['oprice'] = $oprice;
			$roomprice['cprice'] = $cprice;
			if (empty($roomprice['id'])) {
				pdo_insert("storex_room_price", $roomprice);
			} else {
				pdo_update("storex_room_price", $roomprice, array("id" => $roomprice['id']));
			}
		}
	}
	wmessage(error(0, '更新房态成功！'), '', 'ajax');
}

if ($op == 'assign_room') {
	$orderid = intval($_GPC['orderid']);
	$roomids= explode(',', $_GPC['roomids']);
	$order = pdo_get('storex_order', array('id' => $orderid));
	if (empty($order)) {
		wmessage(error(-1, '订单信息错误'), '', 'ajax');
	}
	if (count($roomids) != $order['nums']) {
		wmessage(error(-1, '所选房间数量跟订单房间数量不一致'), '', 'ajax');
	}
	if (!empty($order['roomitemid'])) {
		$assign_roomitemid = explode(',', $order['roomitemid']);
	}
	if (!check_room_assign($order, $roomids, true)) {
		wmessage(error(-1, '所选房间存在不空闲'), '', 'ajax');
	}
	$result = pdo_update('storex_order', array('roomitemid' => implode(',', $roomids)), array('id' => $orderid));
	if (!empty($result)) {
		if (!empty($assign_roomitemid) && is_array($assign_roomitemid)) {
			$order['roomitemid'] = '';
			foreach ($assign_roomitemid as $roomid) {
				delete_room_assign($order, $roomid);
			}
		}
		wmessage(error(0, '分配成功'), '', 'ajax');
	} else {
		wmessage(error(-1, '分配失败'), '', 'ajax');
	}
}

if ($op == 'goods') {
	$manage_storex_lists = clerk_permission_storex($op);
	$goods_lists = array();
	if (!empty($manage_storex_lists) && is_array($manage_storex_lists)) {
		foreach ($manage_storex_lists as $store) {
			$table = gettablebytype($store['store_type']);
			$fields = array('id', 'store_base_id', 'status', 'title', 'oprice', 'cprice', 'thumb');
			$condition = array('store_base_id' => $store['id']);
			$list = pdo_getall($table, $condition, $fields);
			if (!empty($list) && is_array($list)) {
				foreach ($list as &$val) {
					if (!empty($val['thumb'])) {
						$val['thumb'] = tomedia($val['thumb']);
					}
				}
			}
			$goods_lists[$store['id']] = $list;
		}
	}
	wmessage(error(0, $goods_lists), '', 'ajax');
}

if ($op == 'status') {
	$goodsid = intval($_GPC['goodsid']);
	$storeid = intval($_GPC['storeid']);
	$status = intval($_GPC['status']);
	if (!empty($goodsid) && !empty($storeid)) {
		$manage_storex_lists = clerk_permission_storex('goods');
		if (!empty($manage_storex_lists[$storeid])) {
			$table = gettablebytype($manage_storex_lists[$storeid]['store_type']);
			if ($status != 1) {
				$status = 0;
			}
			pdo_update($table, array('status' => $status), array('id' => $goodsid));
			wmessage(error(0, '设置成功'), '', 'ajax');
		} else {
			wmessage(error(-1, '没有权限'), '', 'ajax');
		}
	} else {
		wmessage(error(-1, '参数错误'), '', 'ajax');
	}
}

if ($op == 'clerk_pay') {
	$type = $_GPC['type'];
	$money = $_GPC['money'];
	$types = array('credit1', 'credit2');
	$storeid = intval($_GPC['storeid']);
	if (empty($storeid)) {
		wmessage(error(-1, '店铺错误'), '', 'ajax');
	}
	$clerk = pdo_get('storex_clerk', array('from_user' => $_W['openid'], 'storeid' => $storeid, 'weid' => $_W['uniacid']), array('id'));
	if (empty($clerk)) {
		wmessage(error(-1, '店员不存在'), '', 'ajax');
	}
	if (!in_array($type, $types)) {
		wmessage(error(-1, '收款类型错误'), '', 'ajax');
	}
	if ($money <= 0) {
		wmessage(error(-1, '收款金额错误'), '', 'ajax');
	}
	$data = array(
		'do' => 'usercenter',
		'op' => 'credit_pay',
		'type' => $type,
		'money' => sprintf('%.2f', $money),
		'clerkid' => $clerk['id'],
		'm' => 'wn_storex',
	);
	$url = murl('entry', $data, true, true);
	wmessage(error(0, $url), '', 'ajax');
}

if ($op == 'order_consume') {
	if (empty($_W['openid'])) {
		message('请先关注公众号！', '', 'error');
	}
	$manage_storex_lists = clerk_permission_storex('order');
	if (empty($manage_storex_lists)) {
		message('没有核销订单的权限', '', 'error');
	}
	$orderid = intval($_GPC['orderid']);
	$order = pdo_get('storex_order', array('status' => array('0', '1'), 'id' => $orderid, 'mode_distribute !=' => 2), array('id', 'hotelid', 'status', 'roomid', 'spec_info', 'nums', 'sum_price', 'cart'));
	if (empty($order)) {
		message('已核销或没有该订单', '', 'error');
	}
	if (empty($manage_storex_lists[$order['hotelid']])) {
		message('没有该店铺的订单管理权限', '', 'error');
	}
	if (!empty($_GPC['consume'])) {
		$result = pdo_update('storex_order', array('status' => 3), array('id' => $orderid));
		if (!is_error($result)) {
			$logs = array(
				'table' => 'storex_order_logs',
				'time' => TIMESTAMP,
				'before_change' => $order['status'],
				'after_change' => 3,
				'type' => 'status',
				'uid' => $uid,
				'clerk_id' => $manage_storex_lists[$order['hotelid']]['id'],
				'clerk_type' => 3,
				'orderid' => $orderid,
				'remark' => '店员核销',
			);
			log_write($logs);
			message(error(0, '核销成功'), $url, 'ajax');
		} else {
			message(error(-1, '核销失败'), '', 'ajax');
		}
	} else {
		$store = get_store_info($order['hotelid']);
		$table = gettablebytype($store['store_type']);
		$order['goods'] = array();
		$order['link'] = murl('entry', array('do' => 'clerk', 'orderid' => $orderid, 'op' => 'order_consume', 'm' => 'wn_storex', 'consume' => 1), true, true);
		//单个订单和购物车区分
		if (!empty($order['roomid'])) {
			$good = pdo_get($table, array('id' => $order['roomid']), array('id', 'title', 'sub_title', 'thumb'));
			if (!empty($good)) {
				$good['thumb'] = tomedia($good['thumb']);
				if (!empty($order['spec_info'])) {
					$order['spec_info'] = iunserializer($order['spec_info']);
					$good['spec'] = implode(' ', $order['spec_info']['goods_val']);
				}
				$good['cprice'] = $order['cprice'];
				$good['nums'] = $order['nums'];
				$order['goods'][] = $good;
			}
		} else {
			if (!empty($order['cart'])) {
				$order['cart'] = iunserializer($order['cart']);
				foreach ($order['cart'] as $g) {
					$good['title'] = $g['good']['title'];
					$good['nums'] = $g['good']['buynums'];
					$good['cprice'] = $g['good']['cprice'];
					$good['spec'] = implode(' ', $g['good']['spec_info']['goods_val']);
					$order['goods'][] = $good;
				}
			}
		}
		include $this->template('orderconsume');
	}
}
if ($op == 'couponcode') {
	$code = $_GPC['code'];
	include $this->template('couponcode');
}
if ($op == 'coupon_consume') {
	$colors = activity_get_coupon_colors();
	$source = trim($_GPC['source']);
	$card_id = trim($_GPC['card_id']);
	$encrypt_code = trim($_GPC['encrypt_code']);
	$openid = trim($_GPC['openid']);
	if (empty($card_id) || empty($encrypt_code)) {
		message('卡券签名参数错误');
	}
	if ($source == '1') {
		$card = pdo_get('storex_coupon', array('uniacid' => $_W['uniacid'], 'id' => $card_id));
	} else {
		$card = pdo_get('storex_coupon', array('uniacid' => $_W['uniacid'], 'card_id' => $card_id));
	}
	if (empty($card)) {
		message('卡券不存在或已删除');
	}
	$card['date_info'] = iunserializer($card['date_info']);
	$card['logo_url'] = tomedia($card['logo_url']);
	$error_code = 0;
	if ($source == '1') {
		$code = $encrypt_code;
	} else {
		load()->classs('coupon');
		$coupon = new coupon($_W['acid']);
		if (is_null($coupon)) {
			message('系统错误');
		}
		$code = $coupon->DecryptCode(array('encrypt_code' => $encrypt_code));
		$code = $code['code'];
	}
	
	if (is_error($code)) {
		$error_code = 1;
	}
	if (checksubmit()) {
		$password = trim($_GPC['password']);
		$clerk = pdo_get('storex_clerk', array('weid' => $_W['uniacid'], 'password' => $password));
		$_W['user']['name'] = $clerk['name'];
		$_W['user']['clerk_id'] = $clerk['id'];
		$_W['user']['clerk_type'] = 3;
		$_W['user']['store_id'] = $clerk['storeid'];
		if (empty($clerk)) {
			message('店员密码错误', referer(), 'error');
		}
		if (!$code) {
			message('code码错误', referer(), 'error');
		}
		$record = pdo_get('storex_coupon_record', array('code' => $code));
		$status = activity_coupon_consume($record['couponid'], $record['id'], $clerk['storeid']);
		if (is_error($status)) {
			message($status['message'], referer(), 'error');
		}
		message('核销卡券成功', referer(), 'success');
	}
	include $this->template('couponconsume');
}

if ($op == 'code_consume') {
	$code = trim($_GPC['code']);
	$record = pdo_get('storex_coupon_record', array('code' => $code));
	if (empty($record)) {
		wmessage(error(-1, '卡券记录不存在'), '', 'ajax');
	}
	$clerk = pdo_get('storex_clerk', array('from_user' => $_W['openid'], 'weid' => $_W['uniacid']), array('id'));
	$status = activity_coupon_consume($record['couponid'], $record['id'], $clerk['id']);
	if (!is_error($status)) {
		wmessage(error(0, ''), '', 'ajax');
	} else {
		wmessage(error(-1, $status['message']),'' , 'ajax');
	}
}