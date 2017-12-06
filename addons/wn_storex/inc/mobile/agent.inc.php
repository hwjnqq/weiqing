<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display', 'register', 'agent_team', 'apply', 'apply_list');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

check_params();
load()->model('mc');
$storeid = intval($_GPC['storeid']);
$uid = mc_openid2uid($_W['openid']);

if ($op == 'display') {
	$register_info = pdo_get('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'uid' => $uid), array('id', 'storeid', 'income', 'outcome', 'alipay', 'level', 'status', 'realname', 'tel', 'reason'));
	$register_info['status'] = !empty($register_info['status']) ? $register_info['status'] : 4;
	$store_info = get_store_info($storeid);
	if ($register_info['status'] == 2) {
		//自己分销的订单
		$table = gettablebytype($store_info['store_type']);
		$self_orders = pdo_getall('storex_order', array('agentid' => $register_info['id'], 'status' => array(0, 1, 3)), array('id', 'roomid', 'sum_price', 'time', 'nums', 'is_package', 'cart', 'agentid', 'cprice', 'status'), '', 'time DESC');
		$agent_log = order_goods_infos($self_orders, $table, 1);
		$two_agent = pdo_getall('storex_agent_apply', array('pid' => $register_info['id']), array('id'));
		if (!empty($two_agent)) {
			//二级分销的订单
			$two_order = pdo_getall('storex_order', array('agentid' => $two_agent, 'status' => array(0, 1, 3)), array('id', 'roomid', 'sum_price', 'time', 'nums', 'is_package', 'cart', 'agentid', 'cprice', 'status'), '', 'time DESC');
			if (!empty($two_order)) {
				$two_order_info = order_goods_infos($two_order, $table, 2);
				$agent_log = array_merge($agent_log, $two_order_info);
			}
			$three_agent = pdo_getall('storex_agent_apply', array('pid' => $two_agent), array('id'));
			if ($three_agent) {
				//三级分销的订单
				$three_order = pdo_getall('storex_order', array('agentid' => $three_agent, 'status' => array(0, 1, 3)), array('id', 'roomid', 'sum_price', 'time', 'nums', 'is_package', 'cart', 'agentid', 'cprice', 'status'), '', 'time DESC');
				if (!empty($three_order)) {
					$three_order_info = order_goods_infos($three_order, $table, 3);
					$agent_log = array_merge($agent_log, $three_order_info);
				}
			}
		}
		$register_info['agent_log'] = $agent_log;
	}
	wmessage(error(0, $register_info), '', 'ajax');
}

function order_goods_infos($orders, $table, $level) {
	$agent_logs = array();
	if (!empty($orders) && is_array($orders)) {
		$goods = array();
		$good_package_ids = array();
		foreach ($orders as &$order) {
			if (!empty($order['cart'])) {
				$order['cart'] = iunserializer($order['cart']);
				foreach ($order['cart'] as $g) {
					$goods[$g['good']['id']] = array(
						'gid' => $g['good']['id'],
						'type' => $g['good']['is_package'],
						'cprice' => $g['good']['cprice'],
						'nums' => $g['good']['buynums'],
					);
				}
			} elseif (!empty($order['roomid']) && $order['is_package'] == 1) {
				$goods[$order['roomid']] = array('gid' => $order['roomid'], 'type' => 1, 'cprice' => $order['cprice'], 'nums' => $order['nums']);
			} elseif (!empty($order['roomid']) && $order['is_package'] == 2) {
				$good_package_ids[$order['roomid']] = array('gid' => $order['roomid'], 'type' => 2, 'cprice' => $order['cprice'], 'nums' => $order['nums']);
			}
		}
		unset($order);
		$good_package_infos = pdo_getall('storex_sales_package', array('id' => array_keys($good_package_ids)), array('id', 'agent_ratio', 'thumb', 'title'), 'id');//套餐返给销售员的比例
		$goods_infos = pdo_getall($table, array('id' => array_keys($goods)), array('id', 'agent_ratio', 'thumb', 'title'), 'id');//返给销售员的比例
		foreach ($orders as $order) {
			if (!empty($order['cart'])) {
				foreach ($order['cart'] as $g) {
					if (!empty($goods_infos[$g['good']['id']])) {
						$agent_ratio = iunserializer($goods_infos[$g['good']['id']]['agent_ratio']);
						if (empty($agent_ratio[$level]) || $agent_ratio[$level] == 0.00) {
							continue;
						}
						$agent_logs[] = array(
							'goodid' => $g['good']['id'],
							'money' => sprintf('%.2f', $g['good']['cprice'] * $g['good']['buynums'] * $agent_ratio[$level] * 0.01),
							'rate' => $agent_ratio[$level],
							'sumprice' => $order['sum_price'],
							'time' => $order['time'],
							'status' => $order['status'],
							'goodsinfo' => array(
								'id' =>	$g['good']['id'],
								'title' => $goods_infos[$g['good']['id']]['title'],
								'thumb'	=> tomedia($goods_infos[$g['good']['id']]['thumb']),
							),
						);
					}
				}
			} elseif (!empty($order['roomid']) && $order['is_package'] == 1) {
				if (!empty($goods_infos[$order['roomid']])) {
					$agent_ratio = iunserializer($goods_infos[$order['roomid']]['agent_ratio']);
					if (empty($agent_ratio[$level]) || $agent_ratio[$level] == 0.00) {
						continue;
					}
					if ($table == 'storex_room') {
						$money = sprintf('%.2f', $order['sum_price'] * $agent_ratio[$level] * 0.01);
					} else {
						$money = sprintf('%.2f', $order['cprice'] * $order['nums'] * $agent_ratio[$level] * 0.01);
					}
					$agent_logs[] = array(
						'goodid' => $order['roomid'],
						'money' => $money,
						'rate' => $agent_ratio[$level],
						'sumprice' => $order['sum_price'],
						'time' => $order['time'],
						'status' => $order['status'],
						'goodsinfo' => array(
							'id' =>	$order['roomid'],
							'title' => $goods_infos[$order['roomid']]['title'],
							'thumb'	=> tomedia($goods_infos[$order['roomid']]['thumb']),
						),
					);
				}
			} elseif (!empty($order['roomid']) && $order['is_package'] == 2) {
				if (!empty($good_package_infos[$order['roomid']])) {
					$agent_ratio = iunserializer($good_package_infos[$order['roomid']]['agent_ratio']);
					if (empty($agent_ratio[$level]) || $agent_ratio[$level] == 0.00) {
						continue;
					}
					$agent_logs[] = array(
						'goodid' => $order['roomid'],
						'money' => sprintf('%.2f', $order['cprice'] * $order['nums'] * $agent_ratio[$level] * 0.01),
						'rate' => $agent_ratio[$level],
						'sumprice' => $order['sum_price'],
						'time' => $order['time'],
						'status' => $order['status'],
						'goodsinfo' => array(
							'id' =>	$order['roomid'],
							'title' => $good_package_infos[$order['roomid']]['title'],
							'thumb'	=> tomedia($good_package_infos[$order['roomid']]['thumb']),
						),
					);
				}
			}
		}
	}
	return $agent_logs;
}

if ($op == 'register') {
	$register_data = array(
		'realname' => trim($_GPC['realname']),
		'tel' => trim($_GPC['tel']),
		'uniacid' => $_W['uniacid'],
		'storeid' => $storeid,
		'openid' => $_W['openid'],
		'uid' => $uid,
		'status' => 1,
		'applytime' => TIMESTAMP,
		'alipay' => trim($_GPC['alipay']),
	);
	foreach ($register_data as $register) {
		if (empty($register)) {
			wmessage(error(-1, '资料不全'), '', 'ajax');
		}
	}
	$member_agent = pdo_get('storex_member_agent', array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'storeid' => $storeid), array('id', 'openid', 'agentid'));
	$agent_pid = !empty($member_agent) ? $member_agent['agentid'] : 0;
	$register_data['pid'] = $agent_pid;
	$register_info = pdo_get('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'uid' => $uid));
	if (empty($register_info)) {
		pdo_insert('storex_agent_apply', $register_data);
		$result = pdo_insertid();
	} else {
		$result = false;
		if ($register_info['status'] == 3) {
			pdo_update('storex_agent_apply', array('realname' => $_GPC['realname'], 'tel' => $_GPC['tel'], 'applytime' => TIMESTAMP, 'alipay' => $_GPC['alipay'], 'refusetime' => '', 'status' => 1), array('id' => $register_info['id']));
			$result = true;
		}
	}
	if (!empty($result)) {
		wmessage(error(0, '申请成功'), '', 'ajax');
	} else {
		wmessage(error(-1, '申请失败'), '', 'ajax');
	}
}

//获取该分销员下的二级三级分销员
if ($op == 'agent_team') {
	$register_info = pdo_get('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'uid' => $uid), array('id', 'storeid', 'income', 'outcome', 'alipay', 'level', 'status', 'realname', 'tel', 'reason'));
	$agents = array(
		'sub_agents' => array(),
		'third_agents' => array(),
	);
	$sub_agents = pdo_getall('storex_agent_apply', array('status' => 2, 'uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'pid' => $register_info['id']), array('id', 'alipay', 'status', 'realname', 'tel', 'applytime', 'uid'), 'id', 'applytime DESC');
	if (!empty($sub_agents)) {
		$third_pid = array_keys($sub_agents);
		sort($sub_agents);
		$uids = array();
		foreach ($sub_agents as &$sub_info) {
			$sub_info['applytime'] = date('Y-m-d H:i', $sub_info['applytime']);
			$uids[] = $sub_info['uid'];
		}
		unset($sub_info);
		$third_agents = pdo_getall('storex_agent_apply', array('status' => 2, 'uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'pid' => $third_pid), array('id', 'alipay', 'status', 'realname', 'tel', 'applytime', 'uid'), '', 'applytime DESC');
		if (!empty($third_agents)) {
			foreach ($third_agents as &$third_info) {
				$third_info['applytime'] = date('Y-m-d H:i', $third_info['applytime']);
				$uids[] = $third_info['uid'];
			}
		}
		unset($third_info);
		if (!empty($uids) && is_array($uids)) {
			$agent_thumbs = array();
			foreach ($uids as $a_uid) {
				$a_userinfo = mc_fansinfo($a_uid);
				$agent_thumbs[$a_uid] = $a_userinfo['avatar'];
			}
		}
		if (!empty($sub_agents)) {
			foreach ($sub_agents as &$val) {
				$val['avatar'] = '';
				if (!empty($agent_thumbs[$val['uid']])) {
					$val['avatar'] = $agent_thumbs[$val['uid']];
				}
			}
			unset($val);
		}
		if (!empty($third_agents)) {
			foreach ($third_agents as &$val) {
				$val['avatar'] = '';
				if (!empty($agent_thumbs[$val['uid']])) {
					$val['avatar'] = $agent_thumbs[$val['uid']];
				}
			}
			unset($val);
		}
		$agents['sub_agents'] = $sub_agents;
		$agents['third_agents'] = $third_agents;
	}
	wmessage(error(0, $agents), '', 'ajax');
}

if ($op == 'apply') {
	$register_info = pdo_get('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'uid' => $uid, 'status' => 2), array('id', 'outcome', 'income'));
	if (!empty($register_info)) {
		if (!empty($_GPC['money'])) {
			if ($register_info['outcome'] == 0) {
				wmessage(error(-1, '没有钱可以提现'), '', 'ajax');
			}
			$money = sprintf('%.2f', $_GPC['money']);
			if ($money <= 0 || $money > $register_info['outcome']) {
				wmessage(error(-1, '申请提现金额错误'), '', 'ajax');
			}
			$apply_log = array(
				'uniacid' => $_W['uniacid'],
				'uid' => $uid,
				'ordersn' => date('md') . $uid . random(4, 1),
				'agentid' => $register_info['id'],
				'storeid' => $storeid,
				'money' => $money,
				'time' => TIMESTAMP,
				'status' => 0,
			);
			pdo_insert('storex_agent_apply_log', $apply_log);
			$apply_log_id = pdo_insertid();
			if (!empty($apply_log_id)) {
				$result = pdo_update('storex_agent_apply', array('outcome -=' => $money), array('id' => $register_info['id'], 'uid' => $uid, 'storeid' => $storeid));
				if (!empty($result)) {
					wmessage(error(0, '申请提现成功'), '', 'ajax');
				} else {
					pdo_delete('storex_agent_apply_log', array('id' => $apply_log_id));
					wmessage(error(-1, '申请提现失败'), '', 'ajax');
				}
			} else {
				wmessage(error(-1, '申请提现失败'), '', 'ajax');
			}
		} else {
			wmessage(error(0, $register_info), '', 'ajax');
		}
	}
	wmessage(error(-1, '你还不是销售员'), '', 'ajax');
}

if ($op == 'apply_list') {
	$register_info = pdo_get('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'uid' => $uid, 'status' => 2));
	$apply_log = array();
	if (!empty($register_info)) {
		$apply_log = pdo_getall('storex_agent_apply_log', array('agentid' => $register_info['id'], 'storeid' => $storeid, 'uid' => $uid), array(), '', 'time DESC');
	}
	wmessage(error(0, $apply_log), '', 'ajax');
}