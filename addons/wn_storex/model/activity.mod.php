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
		$coupon['extra_date_info'] = '有效期:领取后' . $coupon['date_info']['deadline'] . '天可用，有效期' . $coupon['date_info']['limit'] . '天';
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