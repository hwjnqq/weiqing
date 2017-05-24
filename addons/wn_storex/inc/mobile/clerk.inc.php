<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');
mload()->model('card');

$ops = array('clerkindex', 'order', 'order_info', 'room', 'edit', 'permission_storex');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

check_params();

if ($op == 'clerkindex') {
	$id = intval($_GPC['id']);
	$clerk_info = get_clerk_permission($id);
	message(error(0, $clerk_info), '', 'ajax');
}

if ($op == 'permission_storex') {
	$type = $_GPC['type'];
	$manage_storex_ids = clerk_permission_storex($type);
	$manage_storex_lists = pdo_getall('storex_bases', array('weid' => intval($_W['uniacid']), 'id' => $manage_storex_ids), array('id', 'title'));
	message(error(0, $manage_storex_lists), '', 'ajax');
}

if ($op == 'order') {
	$manage_storex_ids = clerk_permission_storex($op);
	$manage_storex_lists = pdo_getall('storex_bases', array('weid' => intval($_W['uniacid']), 'id' => $manage_storex_ids), array('id', 'title', 'store_type'), 'id');
	pdo_query("UPDATE " . tablename('storex_order') . " SET status = '-1' WHERE time < :time AND weid = :uniacid AND paystatus = '0' AND status <> '1' AND status <> '3'", array(':time' => time() - 86400, ':uniacid' => $_W['uniacid']));
	$operation_status = array(0, 1, 4);
	$goods_status = array(0, 1);
	$order_lists = pdo_getall('storex_order', array('weid' => intval($_W['uniacid']), 'hotelid' => $manage_storex_ids, 'status' => $operation_status, 'goods_status' => $goods_status), array('id', 'weid', 'hotelid', 'roomid', 'style', 'status', 'goods_status'), '', 'id DESC');
	if (!empty($order_lists)) {
		foreach ($order_lists as &$info) {
			$info = clerk_order_operation($info);
			if (!empty($manage_storex_lists[$info['hotelid']])) {
				$table = get_goods_table($manage_storex_lists[$info['hotelid']]['store_type']);
			} else {
				continue;
			}
			$goods = pdo_get($table, array('id' => $info['roomid']), array('id', 'thumb'));
			$info['thumb'] = tomedia($goods['thumb']);
		}
	}
	$order_data = array();
	$order_data['order_lists'] = $order_lists;
	message(error(0, $order_data), '', 'ajax');
}

function clerk_order_operation ($item) {
	$status = array();
	if ($item['status'] == -1 || $item['status'] == 3 || $item['status'] == 2) {
		$status = array();
	} elseif ($item['status'] == 1) {
		if ($store_info['store_type'] == 1) {
			$status['status']['4'] = "已入住";
		} else {
			if ($item['mode_distribute'] == 2) {//配送
				if ($item['goods_status'] == 1 || empty($item['goods_status'])) {
					$status['goods_status']['2'] = '已发货';
				}
			}
		}
		$status['status']['3'] = "已完成";
	} elseif ($item['status'] == 4) {
		$status['status']['3'] = '已完成';
	} else {
		$status['status']['-1'] = '取消订单';
		$status['status']['1'] = '确认订单';
		$status['status']['2'] = '拒绝订单';
		$status['status']['3'] = '已完成';
	}
	//可以执行的操作
	$item['operate'] = $status;
	return $item;
}

if ($op == 'order_info') {
	$orderid = intval($_GPC['orderid']);
	if (!empty($orderid)) {
		$item = pdo_get('storex_order', array('id' => $orderid));
		if (!empty($item)) {
			check_clerk_permission($item['hotelid'], 'wn_storex_permission_order');
			$storex_info = pdo_get('storex_bases', array('id' => $item['hotelid']), array('id', 'store_type'));
			$table = get_goods_table($storex_info['store_type']);
			$goods = pdo_get($table, array('id' => $item['roomid']), array('id', 'thumb'));
			$item['thumb'] = tomedia($goods['thumb']);
			$item = clerk_order_operation($item);
			message(error(0, $item), '', 'ajax');
		}
	}
	message(error(-1, '抱歉，订单不存在或是已经删除！'), '', 'ajax');
}

if ($op == 'edit_order') {
	$orderid = $_GPC['orderid'];
	if (empty($orderid)) {
		message(error(-1, '参数错误！'), '', 'ajax');
	}
	$item = pdo_get('storex_order', array('id' => $orderid));
	check_clerk_permission($item['hotelid'], 'wn_storex_permission_order');
	if (empty($item)) {
		message(error(-1, '抱歉，订单不存在或是已经删除'), '', 'ajax');
	}
	$goodsid = intval($item['roomid']);
	$goods_info = pdo_get($table, array('id' => $goodsid), array('id', 'title'));
	$setting = pdo_get('storex_set', array('weid' => $_W['uniacid']));
	$data = array(
		'status' => intval($_GPC['status']),
		'msg' => $_GPC['msg'],
		'goods_status' => intval($_GPC['goods_status']),
	);
	if (!empty($data['status'])) {
		if ($item['status'] == -1) {
			message(error(-1, '订单状态已经取消，不能操做！'), '', 'ajax');
		}
		if ($item['status'] == 3) {
			message(error(-1, '订单状态已经完成，不能操做！'), '', 'ajax');
		}
		if ($item['status'] == 2) {
			message(error(-1, '订单状态已拒绝，不能操做！'), '', 'ajax');
		}
		if ($data['status'] == $item['status']) {
			message(error(-1, '订单状态已经是该状态了，不要重复操作！'), '', 'ajax');
		}
	}
	if (!empty($data['goods_status']) && $data['goods_status'] == 2 && $item['status'] != 1) {
		if ($item['goods_status'] == 3) {
			message(error(-1, '已收货，不要再发了！'), '', 'ajax');
		}
		if ($item['goods_status'] == 2) {
			message(error(-1, '已发货，不要重复操做！'), '', 'ajax');
		}
		if ($item['status'] != 1) {
			message(error(-1, '请先确认订单！'), '', 'ajax');
		}
	}
	//订单取消
	if ($data['status'] == -1 || $data['status'] == 2) {
		if ($store_info['store_type'] == 1) {
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
	if ($data['status'] != $item['status']) {
		//订单退款
		if ($data['status'] == 2) {
			$acc = WeAccount::create();
			$info = '您在' . $store_info['title'] . '预订的' . $goods_info['title'] . "已不足。已为您取消订单";
			$custom = array(
					'msgtype' => 'text',
					'text' => array('content' => urlencode($info)),
					'touser' => $item['openid'],
			);
			if (!empty($setting['template']) && !empty($setting['refuse_templateid'])) {
				$tplnotice = array(
						'first' => array('value' => '尊敬的宾客，非常抱歉的通知您，您的预订订单被拒绝。'),
						'keyword1' => array('value' => $item['ordersn']),
						'keyword3' => array('value' => $item['nums']),
						'keyword4' => array('value' => $item['sum_price']),
						'keyword5' => array('value' => '商品不足'),
				);
				if ($store_info['store_type'] == 1) {
					$tplnotice['keyword2'] = array('value' => date('Y.m.d', $item['btime']) . '-' . date('Y.m.d', $item['etime']));
				}
				$acc->sendTplNotice($item['openid'], $setting['refuse_templateid'], $tplnotice);
			} else {
				$status = $acc->sendCustomNotice($custom);
			}
		}
		//订单确认提醒
		if ($data['status'] == 1) {
			$acc = WeAccount::create();
			$info = '您在' . $store_info['title'] . '预订的' . $goods_info['title'] . "已预订成功";
			$custom = array(
					'msgtype' => 'text',
					'text' => array('content' => urlencode($info)),
					'touser' => $item['openid'],
			);
			//TM00217
			if (!empty($setting['template']) && !empty($setting['templateid'])) {
				$tplnotice = array(
						'first' => array('value' => '您好，您已成功预订' . $store_info['title'] . '！'),
						'order' => array('value' => $item['ordersn']),
						'Name' => array('value' => $item['name']),
						'datein' => array('value' => date('Y-m-d', $item['btime'])),
						'dateout' => array('value' => date('Y-m-d', $item['etime'])),
						'number' => array('value' => $item['nums']),
						'room type' => array('value' => $item['style']),
						'pay' => array('value' => $item['sum_price']),
						'remark' => array('value' => '预订成功')
				);
				$result = $acc->sendTplNotice($item['openid'], $setting['templateid'], $tplnotice);
			} else {
				$status = $acc->sendCustomNotice($custom);
			}
		}
		//已入住提醒
		if ($data['status'] == 4) {
			$acc = WeAccount::create();
			$info = '您已成功入住' . $store_info['title'] . '预订的' . $goods_info['title'];
			$custom = array(
					'msgtype' => 'text',
					'text' => array('content' => urlencode($info)),
					'touser' => $item['openid'],
			);
			//TM00058
			if (!empty($setting['template']) && !empty($setting['check_in_templateid'])) {
				$tplnotice = array(
						'first' =>array('value' =>'您好,您已入住' . $store_info['title'] . $goods_info['title']),
						'hotelName' => array('value' => $store_info['title']),
						'roomName' => array('value' => $goods_info['title']),
						'date' => array('value' => date('Y-m-d', $item['btime'])),
						'remark' => array('value' => '如有疑问，请咨询' . $store_info['phone'] . '。'),
				);
				$result = $acc->sendTplNotice($item['openid'], $setting['check_in_templateid'], $tplnotice);
			} else {
				$status = $acc->sendCustomNotice($custom);
			}
		}
	
		//订单完成提醒
		if ($data['status'] == 3) {
			$uid = mc_openid2uid(trim($item['openid']));
			//订单完成后增加积分
			card_give_credit($item['weid'], $uid, $item['sum_price'], $item['hotelid']);
			//增加出售货物的数量
			add_sold_num($goods_info);
			$acc = WeAccount::create();
			$info = '您在' . $store_info['title'] . '预订的' . $goods_info['title'] . "订单已完成,欢迎下次光临";
			$custom = array(
					'msgtype' => 'text',
					'text' => array('content' => urlencode($info)),
					'touser' => $item['openid'],
			);
			//OPENTM203173461
			if (!empty($setting['template']) && !empty($setting['finish_templateid']) && $store_info['store_type'] == 1) {
				$tplnotice = array(
						'first' => array('value' =>'您已成功办理离店手续，您本次入住酒店的详情为'),
						'keyword1' => array('value' => date('Y-m-d', $item['btime'])),
						'keyword2' => array('value' => date('Y-m-d', $item['etime'])),
						'keyword3' => array('value' => $item['sum_price']),
						'remark' => array('value' => '欢迎您的下次光临。')
				);
				$result = $acc->sendTplNotice($item['openid'], $setting['finish_templateid'], $tplnotice);
			} else {
				$status = $acc->sendCustomNotice($custom);
			}
		}
		//发货设置
		if (!empty($data['goods_status']) && $data['goods_status'] == 2) {
			$data['status'] = 1;
			$acc = WeAccount::create();
			$info = '您在' . $store_info['title'] . '预订的' . $goods_info['title'] . "已发货";
			$custom = array(
					'msgtype' => 'text',
					'text' => array('content' => urlencode($info)),
					'touser' => $item['openid'],
			);
			$status = $acc->sendCustomNotice($custom);
		}
		pdo_update('storex_order', $data, array('id' => $orderid));
		message(error(0, '订单信息处理完成！'), '', 'ajax');
	}
}

if ($op == 'orders') {
	$id = intval($_GPC['id']);
	$ac = $_GPC['ac'];
	if ($ac != 'list' && $ac != '') {
		check_clerk_permission($id, 'wn_storex_permission_' . $op);
		$store_info = get_store_info($id);
		$table = get_goods_table($store_info['store_type']);
	}

	if ($ac == 'list' || $ac == '') {
		$clerk_info = get_clerk_permission();
		$manage_storex_ids = array_keys($clerk_info);
		$manage_storex_lists = pdo_getall('storex_bases', array('weid' => intval($_W['uniacid']), 'id' => $manage_storex_ids), array('id', 'title'));
		pdo_query("UPDATE " . tablename('storex_order') . " SET status = '-1' WHERE time < :time AND weid = :uniacid AND paystatus = '0' AND status <> '1' AND status <> '3'", array(':time' => time() - 86400, ':uniacid' => $_W['uniacid']));
		$operation_status = array(0, 1, 4);
		$goods_status = array(0, 1);
		$order_lists = pdo_getall('storex_order', array('weid' => intval($_W['uniacid']), 'hotelid' => $manage_storex_ids, 'status' => $operation_status, 'goods_status' => $goods_status), array(), '', 'id DESC');
		$order_data = array();
		$order_data['manage_storex_lists'] = $manage_storex_lists;
		$order_data['order_lists'] = $order_lists;
		message(error(0, $order_data), '', 'ajax');
	} elseif ($ac == 'info') {
		$orderid = $_GPC['orderid'];
		if (!empty($orderid)) {
			$item = pdo_get('storex_order', array('id' => $orderid));
			if (!empty($item)) {
				$status = array();
				if ($item['status'] == -1 || $item['status'] == 3 || $item['status'] == 2) {
					$status = array();
				} elseif ($item['status'] == 1) {
					if ($store_info['store_type'] == 1) {
						$status['status']['4'] = "已入住";
					} else {
						if ($item['mode_distribute'] == 2) {//配送
							if ($item['goods_status'] == 1 || empty($item['goods_status'])) {
								$status['goods_status']['2'] = '已发货';
							}
						}
					}
					$status['status']['3'] = "已完成";
				} elseif ($item['status'] == 4) {
					$status['status']['3'] = '已完成';
				} else {
					$status['status']['-1'] = '取消订单';
					$status['status']['1'] = '确认订单';
					$status['status']['2'] = '拒绝订单';
					$status['status']['3'] = '已完成';
				}
				//可以执行的操作
				$item['operate'] = $status;
				message(error(0, $item), '', 'ajax');
			}
		}
		message(error(-1, '抱歉，订单不存在或是已经删除！'), '', 'ajax');
	} elseif ($ac == 'edit') {
		
	}
}
if ($op == 'room') {
	$id = intval($_GPC['id']);//酒店id
	check_clerk_permission($id, 'wn_storex_permission_' . $op);
	$store_info = get_store_info($id);
	if ($store_info['store_type'] != 1) {
		message(error(-1, '该店铺没有房型'), '', 'ajax');
	}
	$start_time = $_GPC['start_time'];
	$end_time = $_GPC['end_time'];
	$days = intval((strtotime($end_time) - strtotime($start_time)) / 86400) + 1;
	$btime = strtotime($start_time);
	$etime = strtotime($end_time);
	$list = pdo_getall('storex_room', array('hotelid' => $id, 'weid' => intval($_W['uniacid']), 'is_house' => 1));
	if (!empty($list) && is_array($list)) {
		$room_ids = '';
		foreach ($list as $val) {
			$room_ids .= $val['id'] . ',';
		}
		$room_ids = trim($room_ids, ',');
		$dates = get_dates($start_time, $days);
		$item = array();
		if (!empty($room_ids)) {
			$sql = "SELECT * FROM " . tablename('storex_room_price');
			$sql .= " WHERE weid = " . intval($_W['uniacid']);
			$sql .= " AND roomid IN ( " . $room_ids . " ) ";
			$sql .= " AND roomdate >= " . $btime;
			$sql .= " AND roomdate < " . ($etime + 86400);
			$item = pdo_fetchall($sql);
		}
		$flag = 0;
		if (!empty($item)) {
			$flag = 1;
		}
		foreach ($list as $key => $value) {
			$list[$key]['thumb'] = tomedia($value['thumb']);
			$list[$key]['price_list'] = array();
			if ($flag == 1) {
				for ($i = 0; $i < $days; $i++) {
					$k = $dates[$i]['date'];
					foreach ($item as $p_key => $p_value) {
						if ($p_value['roomid'] != $value['id']) {
							continue;
						}
						//判断价格表中是否有当天的数据
						if ($p_value['thisdate'] == $k) {
							$list[$key]['price_list'][$k]['oprice'] = $p_value['oprice'];
							$list[$key]['price_list'][$k]['cprice'] = $p_value['cprice'];
							$list[$key]['price_list'][$k]['roomid'] = $value['id'];
							$list[$key]['price_list'][$k]['hotelid'] = $id;
							$list[$key]['price_list'][$k]['status'] = $p_value['status'];
							if (empty($p_value['num'])) {
								$list[$key]['price_list'][$k]['num'] = "0";
								$list[$key]['price_list'][$k]['status'] = 0;
							} elseif ($p_value['num'] == -1) {
								$list[$key]['price_list'][$k]['num'] = "-1";
							} else {
								$list[$key]['price_list'][$k]['num'] = $p_value['num'];
							}
							break;
						}
					}
					//价格表中没有当天数据
					if (empty($list[$key]['price_list'][$k])) {
						$list[$key]['price_list'][$k]['num'] = "-1";
						$list[$key]['price_list'][$k]['status'] = 1;
						$list[$key]['price_list'][$k]['roomid'] = $value['id'];
						$list[$key]['price_list'][$k]['hotelid'] = $id;
						$list[$key]['price_list'][$k]['oprice'] = $value['oprice'];
						$list[$key]['price_list'][$k]['cprice'] = $value['cprice'];
					}
				}
			} else {
				//价格表中没有数据
				for ($i = 0; $i < $days; $i++) {
					$k = $dates[$i]['date'];
					$list[$key]['price_list'][$k]['num'] = "-1";
					$list[$key]['price_list'][$k]['status'] = 1;
					$list[$key]['price_list'][$k]['roomid'] = $value['id'];
					$list[$key]['price_list'][$k]['hotelid'] = $id;
					$list[$key]['price_list'][$k]['oprice'] = $value['oprice'];
					$list[$key]['price_list'][$k]['cprice'] = $value['cprice'];
				}
			}
		}
	}
	echo "<pre>";
	print_r($list);
	echo "</pre>";
	message(error(0, $list), '', 'ajax');
}

if ($op == 'edit') {
	$id = intval($_GPC['id']);//酒店id
	check_clerk_permission($id, 'wn_storex_permission_room');
	$store_info = get_store_info($id);
	if ($store_info['store_type'] != 1) {
		message(error(-1, '该店铺没有房型'), '', 'ajax');
	}
	
	$dates = explode(',', $_GPC['dates']);
	$status = empty($_GPC['status']) ? 0 : 1;
	$num = intval($_GPC['num']);
	$oprice = sprintf('%.2f', $_GPC['oprice']);
	$cprice = sprintf('%.2f', $_GPC['cprice']);
	if ($oprice <= 0 || $cprice <= 0) {
		message(error(-1, '价格不能小于等于0！'), '', 'ajax');
	}
	$room_id = intval($_GPC['room_id']);
	if (empty($room_id)) {
		message(error(-1, '请选择房间！'), '', 'ajax');
	}
	if (!empty($dates) && is_array($dates)) {
		foreach ($dates as $date) {
			$roomprice = getRoomPrice($id, $room_id, $date);
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
	message(error(0, '更新房态成功！'), '', 'ajax');
}