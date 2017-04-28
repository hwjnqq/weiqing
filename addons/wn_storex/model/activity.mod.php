<?php

/**
 * 返回卡券类型的中文标题和英文标识，此标识与微信文档同步
 * @param int $type
 */
function activity_get_coupon_label($type = '') {
	$types = array(
		COUPON_TYPE_DISCOUNT => array('title' => '折扣券', 'value' => 'discount'),
		COUPON_TYPE_CASH => array('title' => '代金券', 'value' => 'cash'),
		COUPON_TYPE_GIFT => array('title' => '礼品券', 'value' => 'gift'),
		COUPON_TYPE_GROUPON => array('title' => '团购券', 'value' => 'groupon'),
		COUPON_TYPE_GENERAL => array('title' => '优惠券', 'value' => 'general_coupon'),
	);
	return $types[$type] ? $types[$type] : $types;
}

function activity_get_coupon_colors() {
	$colors = array(
		'Color010' => '#55bd47',
		'Color020' => '#10ad61',
		'Color030' => '#35a4de',
		'Color040' => '#3d78da',
		'Color050' => '#9058cb',
		'Color060' => '#de9c33',
		'Color070' => '#ebac16',
		'Color080' => '#f9861f',
		'Color081' => '#f08500',
		'Color082' => '#a9d92d',
		'Color090' => '#e75735',
		'Color100' => '#d54036',
		'Color101' => '#cf3e36',
		'Color102' => '#5e6671',
	);
	return $colors;
}

/**
 * 获取某卡券信息
 * @param int $couponid 卡券ID
 * @param int $uniacid 公众号ID
 * @return array
 */
function activity_get_coupon_info($id) {
	global $_W;
	$id = intval($id);
	$coupon = pdo_get('storex_coupon', array('uniacid' => $_W['uniacid'], 'id' => $id));
	if (empty($coupon)) {
		return error(1, '卡券不存在或是已经被删除');
	}
	$coupon['date_info'] = iunserializer($coupon['date_info']);
	if ($coupon['date_info']['time_type'] == '1'){
		$coupon['extra_date_info'] = '有效期:' . $coupon['date_info']['time_limit_start'] . '-' . $coupon['date_info']['time_limit_end'];
	} else {
		$coupon['extra_date_info'] = '有效期:领取后' . ($coupon['date_info']['deadline'] == 0 ? '当' : $coupon['date_info']['deadline']) . '天可用，有效期' . $coupon['date_info']['limit'] . '天';
	}
	$coupon['extra'] = iunserializer($coupon['extra']);	
	$coupon['logo_url'] = tomedia($coupon['logo_url']);
	$coupon['description'] = htmlspecialchars_decode($coupon['description']);
	$coupon_stores = pdo_getall('storex_coupon_store', array('uniacid' => $_W['uniacid'], 'couponid' => $coupon['id']), array(), 'storeid');
	if (!empty($coupon_stores)) {
		$stores = pdo_getall('storex_bases', array('id' => array_keys($coupon_stores)), array('title', 'id'), 'id');
		$coupon['location_id_list'] = $stores;
	}
	return $coupon;
}

/**
 * 指定会员领取指定卡券
 * @param int $openid 会员ID或者openid
 * @param int $id 卡券自增id
 * @param int $granttype 获取方式 :1兑换 2扫码
 * @return mixed
 */
function activity_user_get_coupon($id, $openid, $granttype = 1) {
	global $_W, $_GPC;
	if (empty($openid)) {
		$openid = $_W['openid'];
		if(empty($openid)) {
			$openid = $_W['member']['uid'];
		}
		if (empty($openid)) {
			return error(-1, '没有找到指定会员');
		}
	}
	$fan = mc_fansinfo($openid, '', $_W['uniacid']);
	$openid = $fan['openid'];
	if (empty($openid)) {
		return error(-1, '兑换失败');
	}
	$code = base_convert(md5(uniqid() . random(4)), 16, 10);
	$code = substr($code, 1, 16);
	$user = mc_fetch($fan['uid'], array('groupid'));
	$credit_names = array('credit1' => '积分', 'credit2' => '余额');
	$coupon = activity_get_coupon_info($id);
	$pcount = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('storex_coupon_record') . " WHERE `openid` = :openid AND `couponid` = :couponid", array(':couponid' => $id, ':openid' => $openid));
	if (empty($coupon)) {
		return error(-1, '未找到指定卡券');
	} elseif (strtotime(str_replace('.', '-', $coupon['date_info']['time_limit_end'])) < strtotime(date('Y-m-d')) && $coupon['date_info']['time_type'] != 2) {
		return error(-1, '活动已结束');
	} elseif (strtotime(str_replace('.', '-', $coupon['date_info']['time_limit_start'])) > strtotime(date('Y-m-d')) && $coupon['date_info']['time_type'] != 2) {
		return error(-1, '活动未开始');
	} elseif ($coupon['quantity'] <= 0) {
		return error(-1, '卡券发放完毕');
	} elseif ($pcount >= $coupon['get_limit'] && !empty($coupon['get_limit'])) {
		return error(-1, '领取次数不足!');
	}
	$give = $_W['activity_coupon_id'] ? true :false;
	$uid = !empty($_W['member']['uid']) ? $_W['member']['uid'] : $fan['uid'];
	$insert = array(
		'couponid' => $id,
		'uid' => $uid,
		'uniacid' => $_W['uniacid'],
		'openid' => $fan['openid'],
		'code' => $code,
		'grantmodule' => $give ? $_W['activity_coupon_id'] : $_W['current_module']['name'],
		'addtime' => TIMESTAMP,
		'status' => 1,
		'granttype' => $granttype,
	);
	if ($granttype == 1) {
		$exchange = pdo_get('storex_activity_exchange', array('uniacid' => $_W['uniacid'], 'extra' => $id), array());
		$insert['remark'] = $give ? '系统赠送' : '用户使用' . $exchange['credit'] . $credit_names[$exchange['credittype']] . '兑换';
	} else {
		$insert['remark'] = "扫码获取";
	}
	if ($coupon['source'] == 2) {
		$insert['card_id'] = $coupon['card_id'];
		$insert['code'] = '';
	}
	if (empty($insert['card_id'])) {
		$insert['card_id'] = $coupon['card_id'];
	}
	pdo_insert('storex_coupon_record', $insert);
	$insert_id = pdo_insertid();
	pdo_update('storex_coupon', array('quantity' => $coupon['quantity'] - 1, 'dosage' => $coupon['dosage'] +1), array('uniacid' => $_W['uniacid'],'id' => $coupon['id']));
	if ($granttype == 1) {
		pdo_update('storex_activity_exchange', array('num' => ($exchange['num'] + 1)), array('id' => $exchange['id']));
	}
	return $insert_id;
}

/**
 * 获取当前会员当前已有卡券及使用情况
 * @return array
 */
function activity_get_user_couponlist() {
	global $_W, $_GPC;
	$uid = $_W['member']['uid'];
	$coupon_record = pdo_getall('storex_coupon_record', array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'status' => 1));
	foreach ($coupon_record as $key => $record) {
		$coupon = activity_get_coupon_info($record['couponid']);
		if ($coupon['source'] != 1) {
			unset($coupon_record[$key]);
			continue;
		}
		if ($coupon['status'] != '3') {
			pdo_delete('storex_coupon_record', array('id' => $record['id']));
			unset($coupon_record[$key]);
			continue;
		}
		if (is_error($coupon)) {
			unset($coupon_record[$key]);
			continue;
		}
		if (is_array($coupon['date_info']) && $coupon['date_info']['time_type'] == '2') {
			$starttime = $record['addtime'] + $coupon['date_info']['deadline'] * 86400;
			$endtime = $starttime + ($coupon['date_info']['limit']) * 86400;
			if ($endtime < time()) {
				unset($coupon_record[$key]);
				pdo_update('storex_coupon_record', array('status' => 2), array('id' => $record['id']));
				continue;
			} else {
				$coupon['extra_date_info'] = '有效期:' . date('Y.m.d', $starttime) . '-' . date('Y.m.d', $endtime);
			}
		}
		if (is_array($coupon['date_info']) && $coupon['date_info']['time_type'] == '1') {
			$endtime = str_replace('.', '-', $coupon['date_info']['time_limit_end']);
			$endtime = strtotime($endtime);
			if ($endtime < time()) {
				pdo_update('storex_coupon_record', array('status' => 2), array('id' => $record['id']));
				// pdo_delete('coupon_record', array('id' => $record['id']));
				unset($coupon_record[$key]);
				continue;
			}

		}
		$coupon_record[$key] = $coupon;
		$coupon_record[$key]['recid'] = $record['id'];
		$coupon_record[$key]['code'] = $record['code'];
		if ($coupon['source'] == '2') {
			if (empty($coupon_record[$key]['code'])) {
				$coupon_record[$key]['extra_ajax'] = url('entry', array('m' => 'we7_coupon', 'do' => 'activity', 'type' => 'coupon', 'op' => 'addcard'));
			} else {
				$coupon_record[$key]['extra_ajax'] = url('entry', array('m' => 'we7_coupon', 'do' => 'activity', 'type' => 'coupon', 'op' => 'opencard'));
			}
		}
	}
	return $coupon_record;
}

function activity_paycenter_get_coupon() {
	$coupon_owned = activity_get_user_couponlist();
	foreach ($coupon_owned as $key => &$val) {
		if (empty($val['code'])) {
			unset($val);
		}
		if ($val['type'] == '1' || $val['type'] == '2') {
			$coupon_available[] = $val;
		}
	}
	return $coupon_available;
}

function activity_coupon_consume($couponid, $recid, $store_id) {
	global $_W, $_GPC;
	$clerk_name = $_W['user']['name'];
	$clerk_id = $_W['user']['clerk_id'];
	$clerk_type = $_W['user']['type'];
	$coupon_record = pdo_get('storex_coupon_record', array('id' => $recid, 'status' => '1'));
	if (empty($coupon_record)) {
		return error(-1, '没有可使用的卡券');
	}
	$coupon_info = activity_get_coupon_info($couponid);
	if (empty($coupon_info)) {
		return error(-1, '没有指定的卡券信息');
	}
	$uid = $coupon_record['uid'];
	$location_id_list = $coupon_info['location_id_list'];
	if (!empty($location_id_list)) {
		if (!in_array($store_id, array_keys($location_id_list))) {
			return error(-1, '该门店无法使用');
		}
	}
	$date_info = iunserializer($coupon_info['date_info']);
	if ($date_info['time_type'] == '1') {
		if (strtotime(str_replace('.', '-', $date_info['time_limit_start'])) > strtotime(date('Y-m-d'))) {
			return error(-1, '卡券活动尚未开始');
		} elseif (strtotime(str_replace('.', '-', $date_info['time_limit_end'])) < strtotime(date('Y-m-d'))) {
			return error(-1, '卡券活动已经结束');
		}
	} else {
		$starttime = strtotime(date('Y-m-d', $coupon_record['addtime'])) + $date_info['deadline'] * 86400;
		$endtime = $starttime + $date_info['limit'] * 86400;
		if ($starttime > strtotime(date('Y-m-d'))) {
			return error(-1, '卡券活动尚未开始');
		} elseif ($endtime < strtotime(date('Y-m-d'))) {
			return error(-1, '卡券活动已经结束');
		}
	}
	if ($coupon_info['source'] == '2') {
		load()->classs('coupon');
		$coupon_api = new coupon($_W['acid']);
		$status = $coupon_api->ConsumeCode(array('code' => $coupon_record['code']));
		if(is_error($status)) {
			return error(-1, $status['message']);
		}
	}
	$update = array(
		'status' => 2 ,
		'usetime' => TIMESTAMP,
		'clerk_name' => $clerk_name,
		'clerk_id' => intval($clerk_id),
		'clerk_type' => $clerk_type,
		'store_id' => $store_id
	);
	pdo_update('storex_coupon_record', $update, array('id' => $coupon_record['id']));
	return true;
}