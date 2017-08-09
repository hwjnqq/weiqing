<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');
mload()->model('card');
mload()->model('order');
mload()->model('clerk');

$ops = array('permission_storex', 'order', 'order_info', 'edit_order', 'room', 'room_info', 'edit_room', 'assign_room', 'goods', 'status');
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
	$order_lists = pdo_getall('storex_order', array('weid' => intval($_W['uniacid']), 'hotelid' => $manage_storex_ids, 'status' => $operation_status, 'goods_status' => $goods_status), array('id', 'weid', 'hotelid', 'paystatus','roomid', 'style', 'btime', 'etime', 'roomitemid', 'status', 'goods_status', 'mode_distribute', 'nums', 'sum_price', 'day'), '', 'id DESC');
	if (!empty($order_lists) && is_array($order_lists)) {
		$rooms = array();
		$room_list = pdo_getall('storex_room_items', array('uniacid' => $_W['uniacid'], 'storeid' => $manage_storex_ids, 'status' => 1), array('id', 'storeid', 'roomid', 'roomnumber'));
		if (!empty($room_list) && is_array($room_list)) {
			foreach ($room_list as $room) {
				$rooms[$room['storeid']][$room['roomid']][] = $room;
			}
		}
		$lists = array();
		foreach ($order_lists as $k => &$info) {
			if (!empty($manage_storex_lists[$info['hotelid']])) {
				$store_type = $manage_storex_lists[$info['hotelid']]['store_type'];
				$info = clerk_order_operation($info, $store_type);
				$table = gettablebytype($store_type);
				if (empty($info['operate'])) {
					continue;
				}
				$info['operate']['is_assign'] = false;
				if (!empty($rooms[$info['hotelid']]) && !empty($rooms[$info['hotelid']][$info['roomid']]) && $info['paystatus'] == 1 && $info['status'] == 1) {
					$info['operate']['is_assign'] = true;
				}
			} else {
				continue;
			}
			$goods = pdo_get($table, array('id' => $info['roomid']), array('id', 'thumb'));
			$info['thumb'] = tomedia($goods['thumb']);
			$lists[] = $info;
		}
		unset($info);
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
				if ($table == 'storex_room') {
					$fields[] = 'is_house';
				}
				$goods = pdo_get($table, array('id' => $order['roomid']), $fields);
				$order['title'] = $store_info[$order['hotelid']]['title'];
				$order['thumb'] = tomedia($goods['thumb']);
				$order = clerk_order_operation($order, $store_info[$order['hotelid']]['store_type']);
				if (isset($goods['is_house']) && $goods['is_house'] == 1) {
					$room_list = pdo_getall('storex_room_items', array('uniacid' => $_W['uniacid'], 'storeid' => $order['hotelid'], 'roomid' => $order['roomid']));
					if (!empty($room_list) && is_array($room_list)) {
						foreach ($room_list as $r => $val) {
							$show = check_room_assign($order, $val['id']);
							if (empty($show)) {
								unset($room_list[$r]);
							}
						}
						$order['operate']['is_assign'] = true;
						$order['room_list'] = $room_list;
						$order['rooms'] = array();
						if (!empty($order['roomitemid'])) {
							$room_item = pdo_getall('storex_room_items', array('uniacid' => $_W['uniacid'], 'storeid' => $order['hotelid'], 'id' => explode(',', $order['roomitemid'])), array('id', 'roomnumber'));
							if (!empty($room_item) && is_array($room_item)) {
								foreach ($room_item as $roomitem) {
									$order['rooms'][] = $roomitem['roomnumber'];
								}
							}
						}
					}
				}
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
	$params = array();
	$params['room'] = $goods_info['title'];
	$params['store'] = $store_info[$item['hotelid']]['title'];
	$params['store_type'] = $store_info[$item['hotelid']]['store_type'];
	$params['openid'] = $item['openid'];
	$params['btime'] = $item['btime'];
	$params['tpl_status'] = false;
	$setting = pdo_get('storex_set', array('weid' => $_W['uniacid']));
	if (!empty($setting['template'])) {
		$params['tpl_status'] = true;
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
			$params['refuse_templateid'] = $setting['refuse_templateid'];
			order_refuse_notice($params);
		}
		//订单确认提醒
		if ($data['status'] == ORDER_STATUS_SURE) {
			if ($store_info[$item['hotelid']]['store_type'] == STORE_TYPE_HOTEL) {
				if (!empty($goods_info) && $goods_info['is_house'] == 1) {
					$data['goods_status'] = GOODS_STATUS_NOT_CHECKED;
				}
			} else {
				$data['goods_status'] = GOODS_STATUS_NOT_SHIPPED;
			}
			$params['ordersn'] = $item['ordersn'];
			$params['contact_name'] = $item['contact_name'];
			$params['sum_price'] = $item['sum_price'];
			$params['etime'] = $item['etime'];
			$params['nums'] = $item['nums'];
			$params['style'] = $item['style'];
			$params['templateid'] = $setting['templateid'];
			order_sure_notice($params);
			
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
			$params['finish_templateid'] = $setting['finish_templateid'];
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
			$params['check_in_templateid'] = $setting['check_in_templateid'];
			order_checked_notice($params);
		}
		//发货设置
		if ($data['goods_status'] == GOODS_STATUS_SHIPPED) {
			$info = '您在' . $store_info[$item['hotelid']]['title'] . '预订的' . $goods_info['title'] . "已发货,订单编号:" . $item['ordersn'];
			$status = send_custom_notice('text', array('content' => urlencode($info)), $item['openid']);
		}
	}
	$result = pdo_update('storex_order', $data, array('id' => $orderid));
	if (!empty($result)) {
		write_log($logs);
		if (in_array($data['status'], array(-1, 2))) {
			order_update_newuser($orderid);
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
	$room_list = pdo_getall('storex_room', array('hotelid' => $manage_storex_ids, 'weid' => intval($_W['uniacid']), 'is_house' => 1), array('id', 'hotelid', 'weid', 'title', 'thumb', 'oprice', 'cprice', 'service', 'store_type', 'is_house'));
	if (!empty($room_list) && is_array($room_list)) {
		foreach ($room_list as $k => $info) {
			if ($info['store_type'] != STORE_TYPE_HOTEL || $info['is_house'] != 1) {
				unset($room_list[$k]);
			}
			if (!empty($manage_storex_lists[$info['hotelid']])) {
				$room_list[$k]['store_title'] = $manage_storex_lists[$info['hotelid']]['title'];
			}
		}
	}
	wmessage(error(0, $room_list), '', 'ajax');
}

if ($op == 'room_info') {
	$room_id = intval($_GPC['room_id']);
	$room_info = pdo_get('storex_room', array('id' => $room_id), array('id', 'hotelid', 'weid', 'title', 'oprice', 'cprice', 'thumb'));
	if (empty($room_info)) {
		wmessage(error(-1, '不存在此房型！'), '', 'ajax');
	}
	$room_info['thumb'] = tomedia($room_info['thumb']);
	$manage_storex_lists = clerk_permission_storex('room', $room_info['hotelid']);
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
					$room_info['price_list'][$k]['hotelid'] = $room_info['hotelid'];
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
				$room_info['price_list'][$k]['hotelid'] = $room_info['hotelid'];
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
			$room_info['price_list'][$k]['hotelid'] = $room_info['hotelid'];
			$room_info['price_list'][$k]['oprice'] = $room_info['oprice'];
			$room_info['price_list'][$k]['cprice'] = $room_info['cprice'];
		}
	}
	wmessage(error(0, $room_info), '', 'ajax');
}

if ($op == 'edit_room') {
	$room_id = intval($_GPC['room_id']);
	$room_info = pdo_get('storex_room', array('id' => $room_id), array('id', 'hotelid', 'weid', 'title', 'oprice', 'cprice', 'thumb'));
	if (empty($room_info)) {
		wmessage(error(-1, '不存在此房型！'), '', 'ajax');
	}
	$room_info['thumb'] = tomedia($room_info['thumb']);
	$manage_storex_lists = clerk_permission_storex('room', $room_info['hotelid']);
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
			$roomprice = getRoomPrice($room_info['hotelid'], $room_id, $date);
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
			$fields = array('id', 'status', 'title', 'oprice', 'cprice', 'thumb');
			if ($table == 'storex_room') {
				$condition = array('hotelid' => $store['id']);
				$fields[] = 'hotelid';
			} else {
				$condition = array('store_base_id' => $store['id']);
				$fields[] = 'store_base_id';
			}
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