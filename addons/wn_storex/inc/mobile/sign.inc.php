<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('sign_info', 'sign', 'remedy_sign');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';
check_params();
$uid = mc_openid2uid($_W['openid']);

$setting = pdo_get('mc_card', array('uniacid' => $_W['uniacid']));
if (empty($setting) || $setting['sign_status'] != 1) {
	message(error(-1, '没有开启签到！'), '', 'ajax');
}
$sign_max_day = pdo_get('storex_sign_record', array('uid' => $uid, 'year' => date('Y'), 'month' => date('n')));

if ($op == 'sign_info') {
	$sign_data = get_sign_info($sign_max_day);
	message(error(0, $sign_data), '', 'ajax');
}

if ($op == 'sign') {
	$sign_data = get_sign_info($sign_max_day);
	$sign_day = intval($_GPC['day']);
	if ($sign_day != date('j')) {
		message(error(-1, '参数错误！'), '', 'ajax');
	}
	if (!empty($sign_data['signs'][$sign_day])) {
		$sign_info = $sign_data['signs'][$sign_day];
		if ($sign_info['sign_status'] == 1) {
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
			$tip = "签到获得积分".$sign_info['credit'];
			pdo_insert('storex_sign_record', $insert_record);
			$insert_id = pdo_insertid();
			if (!empty($insert_id)) {
				mc_credit_update(trim($_W['openid']), 'credit1', $sign_info['credit'], array('0', $tip, 'wn_storex', 0, 0, 3));
				message(error(0, '签到成功，获得'.$sign_info['credit']."积分"), '', 'ajax');
			} else {
				message(error(-1, '签到失败！'), '', 'ajax');
			}
		}
	} else {
		message(error(-1, '参数错误！'), '', 'ajax');
	}
}

if ($op == 'remedy_sign') {
	$remedy_day = intval($_GPC['day']);
	$sign_data = get_sign_info($sign_max_day);
	if ($remedy_day >= date('j')) {
		message(error(-1, '参数错误！'), '', 'ajax');
	}
	if (!empty($sign_data['signs'][$remedy_day])) {
		$sign_info = $sign_data['signs'][$remedy_day];
		if ($sign_info['sign_status'] == 1) {
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
			$tip = "签到获得积分".$sign_info['credit'];
			pdo_insert('storex_sign_record', $insert_record);
			$insert_id = pdo_insertid();
			if (!empty($insert_id)) {
				mc_credit_update(trim($_W['openid']), 'credit1', $sign_info['credit'], array('0', $tip, 'wn_storex', 0, 0, 3));
				message(error(0, '签到成功，获得'.$sign_info['credit']."积分"), '', 'ajax');
			} else {
				message(error(-1, '签到失败！'), '', 'ajax');
			}
		}
	}
}

function sign_operation($sign_info){
	
}

function get_sign_info($sign_max_day){
	global $_W, $_GPC;
	$set = pdo_get('storex_sign_set', array('uniacid' => intval($_W['uniacid'])));
	$sign = iunserializer($set['sign']);
	$sign_data = array();
	$sign_data['sign'] = $sign;
	$sign_data['days'] = date('t');
	$sign_data['month'] = date('n');
	$sign_data['day'] = date('j');
	$sign_data['content'] = $set['content'];
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
