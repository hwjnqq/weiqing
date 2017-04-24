<?php 
//签到操作
function sign_operation($sign_data, $sign_day, $cost = array(), $type = ''){
	global $_W, $_GPC;
	$sign_info = $sign_data['signs'][$sign_day];
	$uid = mc_openid2uid($_W['openid']);
	if ($sign_info['status'] == 1) {
		message(error(-1, '已经签过了，明天再来吧！'), '', 'ajax');
	} else {
		$insert_record = $insert_extra = array(
				'uniacid' => intval($_W['uniacid']),
				'uid' => $uid,
				'credit' => $sign_data['sign']['everydaynum'],
				'addtime' => TIMESTAMP,
				'year' => date('Y'),
				'month' => date('n'),
				'day' => $sign_day,
		);
		if (!empty($sign_data['group'])) {
			foreach ($sign_data['group'] as $k => $val) {
				if (($sign_data['sign_days']+1) == $sign_data['sign'][$k] || (($sign_data['sign_days']+1) == date('t') && $k=='full_sign_num')) {
					$insert_extra['remedy'] = 2;
					$tipx = "满签".$sign_data['sign'][$k]."天送".$val."积分";
					mc_credit_update($uid, 'credit1', $val, array('0', $tipx, 'wn_storex', 0, 0, 1));
					pdo_insert('storex_sign_record', $insert_extra);
					continue;
				}
			}
		}
		if ($type == 'remedy') {
			$insert_record['remedy'] = 1;
			if (!empty($cost)) {
				$tips = "消费".$cost['remedy_cost']."余额，补签第".$sign_day."天";
				$return = mc_credit_update($uid, $cost['remedy_cost_type'], -$cost['remedy_cost'], array('0', $tips, 'wn_storex', 0, 0, 1));
				if (is_array($return)) {
					message(error(-1, "积分不足，补签失败！"), '', 'ajax');
				}
			}
			$tip1 = "补签获得积分".$sign_data['sign']['everydaynum'];
			$tip2 = "补签成功，获得".$sign_data['sign']['everydaynum']."积分";
			$tip3 = "补签失败！";
		} else {
			$tip1 = "签到获得积分".$sign_data['sign']['everydaynum'];
			$tip2 = "签到成功，获得".$sign_data['sign']['everydaynum']."积分";
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

//获取签到信息
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
	$sign_record = pdo_getall('storex_sign_record', array('uid' => $uid, 'year' => date('Y'), 'month' => date('n'), 'remedy !=' => 2), array(),'day','day ASC');

	$sign_days = count($sign_record);
	$no_sign = date('t') - $sign_max_day;
	$group = array();
	if (($sign_days + $no_sign) >= $sign['first_group_day']) {
		$group['first_group_day'] = $sign['first_group_num'];
	}
	if (($sign_days + $no_sign) >= $sign['second_group_day']) {
		$group['second_group_day'] = $sign['second_group_num'];
	}
	if (($sign_days + $no_sign) >= $sign['third_group_day']) {
		$group['third_group_day'] = $sign['third_group_num'];
	}
	if (($sign_days + $no_sign) == date('t')) {
		$group['full_sign_num'] = $sign['full_sign_num'];
	}
	$sign_data['group'] = $group;
	$sign_data['sign_days'] = $sign_days;
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