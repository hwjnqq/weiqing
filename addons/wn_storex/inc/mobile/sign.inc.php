<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('sign_info', 'sign', 'remedy_sign', 'sign_record');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';
check_params();
$uid = mc_openid2uid($_W['openid']);

$sign_set = pdo_get('storex_sign_set', array('uniacid' => $_W['uniacid']));
if (empty($sign_set) || $sign_set['status'] != 1) {
	message(error(-1, '没有开启签到！'), '', 'ajax');
}
$sign_max_day = pdo_get('storex_sign_record', array('uid' => $uid, 'year' => date('Y'), 'month' => date('n')));
if ($op == 'sign_info') {
	$sign_data = get_sign_info($sign_max_day['day']);
	message(error(0, $sign_data), '', 'ajax');
}

if ($op == 'sign') {
	$sign_data = get_sign_info($sign_max_day['day']);
	$sign_day = intval($_GPC['day']);
	if ($sign_day != date('j')) {
		message(error(-1, '参数错误！'), '', 'ajax');
	}
	if (!empty($sign_data['signs'][$sign_day])) {
		$sign_info = $sign_data['signs'][$sign_day];
		sign_operation($sign_info, $sign_day);
	} else {
		message(error(-1, '参数错误！'), '', 'ajax');
	}
}

if ($op == 'remedy_sign') {
	$remedy_day = intval($_GPC['day']);
	$sign_data = get_sign_info($sign_max_day['day']);
	if ($sign_data['sign']['remedy'] == 1) {
		$cost = array(
			'remedy' => $sign_data['sign']['remedy'],
			'remedy_cost' => $sign_data['sign']['remedy_cost'],
			'remedy_cost_type' => $sign_data['sign']['remedy_cost_type'],
		);
	} else {
		message(error(-1, '未开启补签功能！'), '', 'ajax');
	}
	if ($remedy_day >= date('j')) {
		message(error(-1, '参数错误！'), '', 'ajax');
	}
	if (!empty($sign_data['signs'][$remedy_day])) {
		$sign_info = $sign_data['signs'][$remedy_day];
		sign_operation($sign_info, $remedy_day, $cost, 'remedy');
	}
}

if ($op == 'sign_record') {
	$condition = array(
		'uid' => $uid,
		'year' => date('Y'),
	);
	if (!empty($_GPC['month'])) {
		if (intval($_GPC['month']) > 12 || intval($_GPC['month']) <= 0) {
			message(error(-1, '参数错误！'), '', 'ajax');
		} else {
			$condition['month'] = intval($_GPC['month']);
		}
	}
	$sign_record = pdo_getall('storex_sign_record', $condition, array(), '', 'day ASC');
	message(error(0, $sign_record), '', 'ajax');
}

function sign_operation($sign_info, $sign_day, $cost = array(), $type = ''){
	global $_W, $_GPC;
	$uid = mc_openid2uid($_W['openid']);
	if ($sign_info['status'] == 1) {
		message(error(-1, '已经签过了，明天再来吧！'), '', 'ajax');
	} else {
		$insert_record = array(
			'uniacid' => intval($_W['uniacid']),
			'uid' => $uid,
			'credit' => $sign_info['credit'],
			'addtime' => TIMESTAMP,
			'year' => date('Y'),
			'month' => date('n'),
			'day' => $sign_day,
		);
		if ($type == 'remedy') {
			$insert_record['remedy'] = 1;
			if (!empty($cost)) {
				$tips = "消费".$cost['remedy_cost']."余额，补签第".$sign_day."天";
				$return = mc_credit_update($uid, $cost['remedy_cost_type'], -$cost['remedy_cost'], array('0', $tips, 'wn_storex', 0, 0, 1));
				if (is_array($return)) {
					message(error(-1, "积分不足，补签失败！"), '', 'ajax');
				}
			}
			$tip1 = "补签获得积分".$sign_info['credit'];
			$tip2 = "补签成功，获得".$sign_info['credit']."积分";
			$tip3 = "补签失败！";
		} else {
			$tip1 = "签到获得积分".$sign_info['credit'];
			$tip2 = "签到成功，获得".$sign_info['credit']."积分";
			$tip3 = "签到失败！";
		}
		pdo_insert('storex_sign_record', $insert_record);
		$insert_id = pdo_insertid();
		if (!empty($insert_id)) {
			mc_credit_update($uid, 'credit1', $sign_info['credit'], array('0', $tip1, 'wn_storex', 0, 0, 1));
			message(error(0, $tip2), '', 'ajax');
		} else {
			message(error(-1, $tip3), '', 'ajax');
		}
	}
}

function get_sign_info($sign_max_day){
	global $_W, $_GPC;
	$uid = mc_openid2uid($_W['openid']);
	$sign_set = pdo_get('storex_sign_set', array('uniacid' => intval($_W['uniacid'])));
	$sign = iunserializer($sign_set['sign']);
	$sign_data = array();
	$sign_data['sign'] = $sign;
	$sign_data['days'] = date('t');
	$sign_data['month'] = date('n');
	$sign_data['day'] = date('j');
	$sign_data['content'] = $sign_set['content'];
	$sign_data['signs'] = array();
	$sign_record = pdo_getall('storex_sign_record', array('uid' => $uid, 'year' => date('Y'), 'month' => date('n')), array(),'day','day ASC');
	
	$sign_days = count($sign_record);
	$no_sign = date('t') - $sign_max_day;
	$group = array();
	if (($sign_days + $no_sign) > $sign['first_group_day']) {
		$group['first_group_day'] = $sign['first_group_num'];
	}
	if (($sign_days + $no_sign) > $sign['second_group_day']) {
		$group['second_group_day'] = $sign['second_group_num'];
	}
	if (($sign_days + $no_sign) > $sign['third_group_day']) {
		$group['third_group_day'] = $sign['third_group_num'];
	}
	if (($sign_days + $no_sign) == date('t')) {
		$group['full_sign_num'] = $sign['full_sign_num'];
	}
	for ($i = 1; $i <= $sign_data['days']; $i++) {
		$sign_data['signs'][$i] = array(
				'credit' => $sign['everydaynum'],
		);
		if (!empty($sign_record[$i])) {
			$sign_data['signs'][$i] = array(
					'credit' => $sign_record[$i]['credit'],
					'status' => 1,//已签到
			);
		} else {
			$sign_data['signs'][$i]['status'] = 2;//未签到
			if ($i > $sign_max_day) {
				foreach ($group as $k => $val) {
					if (($i-$sign_max_day+$sign_days) == $sign[$k] || (($i-$sign_max_day+$sign_days) == date('t') && $k=='full_sign_num')) {
						$sign_data['signs'][$i]['credit'] += $val;
						continue;
					}
				}
			}
		}
	}
	return $sign_data;
}
