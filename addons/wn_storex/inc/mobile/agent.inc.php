<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display', 'register', 'apply', 'apply_list');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

check_params();
load()->model('mc');
$storeid = intval($_GPC['storeid']);
$uid = mc_openid2uid($_W['openid']);

if ($op == 'display') {
	$register_info = pdo_get('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'uid' => $uid), array('id', 'storeid', 'income', 'outcome', 'alipay', 'level', 'status', 'realname', 'tel'));
	$register_info['status'] = !empty($register_info['status']) ? $register_info['status'] : 4;
	if ($register_info['status'] == 2) {
		$agent_level_default = pdo_get('storex_agent_level', array('isdefault' => 1, 'storeid' => $storeid), array('id', 'title'));
		if (!empty($register_info['level'])) {
			$agent_level = pdo_get('storex_agent_level', array('id' => $register_info['level']), array('id', 'title'));
			if (!empty($agent_level)) {
				$register_info['level_title'] = $agent_level['title'];
			} elseif (!empty($agent_level_default)) {
				$register_info['level_title'] = $agent_level_default['title'];
			}
		} else {
			if (!empty($agent_level_default)) {
				$register_info['level_title'] = $agent_level_default['title'];
			}
		}
		$agent_log = pdo_getall('storex_agent_log', array('uniacid' => intval($_W['uniacid']), 'storeid' => $storeid, 'agentid' => $register_info['id']), array('goodid', 'money', 'rate', 'sumprice', 'time'), '', 'time DESC');
		if (!empty($agent_log) && is_array($agent_log)) {
			$store = get_store_info($storeid);
			$table = gettablebytype($store['store_type']);
			$goodids = array();
			foreach ($agent_log as $info) {
				$goodids[] = $info['goodid'];
			}
			$goods = pdo_getall($table, array('id' => $goodids), array('id', 'title', 'thumb'), 'id');
			foreach ($agent_log as &$agent_info) {
				if (!empty($goods[$agent_info['goodid']])) {
					if (!empty($goods[$agent_info['goodid']]['thumb'])) {
						$goods[$agent_info['goodid']]['thumb'] = tomedia($goods[$agent_info['goodid']]['thumb']);
					}
					$agent_info['goodsinfo'] = $goods[$agent_info['goodid']];
				}
			}
			unset($agent_info);
		}
		$register_info['agent_log'] = $agent_log;
	}
	wmessage(error(0, $register_info), '', 'ajax');
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