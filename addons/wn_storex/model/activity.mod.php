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
	if ($coupon['type'] == COUPON_TYPE_DISCOUNT) {
		$coupon['extra'] = iunserializer($coupon['extra']);
		$coupon['extra_instruction'] = '凭此券消费打' . $coupon['extra']['discount'] * 0.1 . '折';
	} elseif ($coupon['type'] == COUPON_TYPE_CASH) {
		$coupon['extra'] = iunserializer($coupon['extra']);
		$coupon['extra_instruction'] = '消费满' . $coupon['extra']['least_cost'] * 0.01 . '元，减' . $coupon['extra']['reduce_cost'] * 0.01 . '元';
	} else {

	}
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
 * @return mixed
 */
function activity_user_get_coupon($id, $openid) {
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
	}
	elseif (strtotime(str_replace('.', '-', $coupon['date_info']['time_limit_end'])) < strtotime(date('Y-m-d')) && $coupon['date_info']['time_type'] != 2) {
		return error(-1, '活动已结束');
	}
	elseif ($coupon['quantity'] <= 0) {
		return error(-1, '卡券发放完毕');
	}
	elseif ($pcount >= $coupon['get_limit'] && !empty($coupon['get_limit'])) {
		return error(-1, '数量超限');
	}
	$give = $_W['activity_coupon_id'] ? true :false;
	$uid = !empty($_W['member']['uid']) ? $_W['member']['uid'] : $fan['uid'];
	$exchange = pdo_get('storex_activity_exchange', array('uniacid' => $_W['uniacid'], 'extra' => $id), array());
	$insert = array(
		'couponid' => $id,
		'uid' => $uid,
		'uniacid' => $_W['uniacid'],
		'openid' => $fan['openid'],
		'code' => $code,
		'grantmodule' => $give ? $_W['activity_coupon_id'] : $_W['current_module']['name'],
		'addtime' => TIMESTAMP,
		'status' => 1,
		'remark' => $give ? '系统赠送' : '用户使用' . $exchange['credit'] . $credit_names[$exchange['credittype']] . '兑换'
	);
	if ($coupon['source'] == 2) {
		$insert['card_id'] = $coupon['card_id'];
		$insert['code'] = '';
	}
	if (empty($insert['card_id'])) {
		$insert['card_id'] = $coupon['card_id'];
	}
	pdo_insert('storex_coupon_record', $insert);
	pdo_update('storex_coupon', array('quantity' => $coupon['quantity'] - 1, 'dosage' => $coupon['dosage'] +1), array('uniacid' => $_W['uniacid'],'id' => $coupon['id']));
	pdo_update('storex_activity_exchange', array('num' => ($exchange['num'] + 1)), array('id' => $exchange['id']));
	return true;
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
			$endtime = $starttime + ($coupon['date_info']['limit'] - 1) * 86400;
			if ($endtime < time()) {
				unset($coupon_record[$key]);
				pdo_delete('storex_coupon_record', array('id' => $record['id']));
				continue;
			} else {
				$coupon['extra_date_info'] = '有效期:' . date('Y.m.d', $starttime) . '-' . date('Y.m.d', $endtime);
			}
		}
		if (is_array($coupon['date_info']) && $coupon['date_info']['time_type'] == '1') {
			$endtime = str_replace('.', '-', $coupon['date_info']['time_limit_end']);
			$endtime = strtotime($endtime);
			if ($endtime < time()) {
				pdo_update('coupon_record', array('status' => 2), array('id' => $record['id']));
				// pdo_delete('coupon_record', array('id' => $record['id']));
				unset($coupon_record[$key]);
				continue;
			}

		}
		// if ($coupon['type'] == COUPON_TYPE_DISCOUNT) {
		// 	$coupon['icon'] = '<div class="price">' . $coupon['extra']['discount'] * 0.1 . '<span>折</span></div>';
		// }
		// elseif($coupon['type'] == COUPON_TYPE_CASH) {
		// 	$coupon['icon'] = '<div class="price">' . $coupon['extra']['reduce_cost'] * 0.01 . '<span>元</span></div><div class="condition">满' . $coupon['extra']['least_cost'] * 0.01 . '元可用</div>';
		// }
		// elseif($coupon['type'] == COUPON_TYPE_GIFT) {
		// 	$coupon['icon'] = '<img src="resource/images/wx_gift.png" alt="" />';
		// }
		// elseif($coupon['type'] == COUPON_TYPE_GROUPON) {
		// 	$coupon['icon'] = '<img src="resource/images/groupon.png" alt="" />';
		// }
		// elseif($coupon['type'] == COUPON_TYPE_GENERAL) {
		// 	$coupon['icon'] = '<img src="resource/images/general_coupon.png" alt="" />';
		// }
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