<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display', 'register', 'apply');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

check_params();
load()->model('mc');
$uid = mc_openid2uid($_W['openid']);

if ($op == 'display') {
	$register_info = pdo_get('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'storeid' => $_GPC['storeid'], 'uid' => $uid));
	$register_info['status'] = !empty($register_info['status']) ? $register_info['status'] : 4;
	wmessage(error(0, $register_info), '', 'ajax');
}

if ($op == 'register') {
	$register_data = array(
		'realname' => trim($_GPC['realname']),
		'tel' => trim($_GPC['tel']),
		'uniacid' => $_W['uniacid'],
		'storeid' =>  $_GPC['storeid'],
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
	$register_info = pdo_get('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'storeid' => $_GPC['storeid'], 'uid' => $uid));
	if (empty($register_info)) {
		pdo_insert('storex_agent_apply', $register_data);
		$result = pdo_insertid();
	} else {
		$result = fasle;
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
	
}