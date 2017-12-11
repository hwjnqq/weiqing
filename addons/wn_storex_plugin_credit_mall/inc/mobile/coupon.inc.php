<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display', 'exchange');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

load()->model('mc');
require IA_ROOT . '/addons/wn_storex/model/card.mod.php';
$uid = mc_openid2uid($_W['openid']);
activity_get_coupon_type();

if ($op == 'display') {
	$ids = array();
	$storex_exchange = pdo_getall('storex_activity_exchange', array('uniacid' => intval($_W['uniacid']), 'status' => 1, 'type <>' => 3), array(), 'extra');
	if (!empty($storex_exchange)) {
		$ids = array_keys($storex_exchange);
	} else {
		message(error(0, array()), '', 'ajax');
	}
	$storex_coupon = pdo_getall('storex_coupon', array('uniacid' => intval($_W['uniacid']), 'id' => $ids, 'source' => COUPON_TYPE, 'status' => 3), array('id', 'type', 'logo_url', 'title', 'description', 'get_limit', 'date_info', 'sub_title', 'extra', 'quantity'), 'id');
	if (!empty($storex_coupon)) {
		foreach ($storex_coupon as &$value) {
			$value['extra'] = iunserializer($value['extra']);
			if ($value['type'] == COUPON_TYPE_DISCOUNT) {
				$value['discounts'] = $value['extra']['discount']/10;
			} elseif ($value['type'] == COUPON_TYPE_CASH) {
				$value['least_cost'] = $value['extra']['least_cost']/100;
				$value['reduce_cost'] = $value['extra']['reduce_cost']/100;
			}
			$value['date_info'] = iunserializer($value['date_info']);
			if ($value['date_info']['time_type'] == '1') {
				$value['extra_date_info'] = '有效期:' . $value['date_info']['time_limit_start'] . '-' . $value['date_info']['time_limit_end'];
			} else {
				$value['extra_date_info'] = '有效期:领取后' . ($coupon['date_info']['deadline'] == 0 ? '当' : $coupon['date_info']['deadline']) . '天可用，有效期' . $value['date_info']['limit'] . '天';
			}
			$value['logo_url'] = tomedia($value['logo_url']);
		}
		unset($value);
	}
	//总领取数量
	$coupon_total = pdo_fetchall("SELECT COUNT(*) AS total, couponid FROM " . tablename('storex_coupon_record') . " WHERE uniacid = :uniacid AND couponid IN (" . implode(',', $ids) . ") GROUP BY couponid", array(':uniacid' => intval($_W['uniacid'])), 'couponid');
	//我的领取的数量
	$mine_coupon_num = pdo_fetchall("SELECT COUNT(*) AS sum, couponid FROM " . tablename('storex_coupon_record') . " WHERE uid = :uid GROUP BY couponid", array(':uid' => $uid), 'couponid');
	if (!empty($storex_exchange)) {
		foreach ($storex_exchange as $id => $info) {
			if ($storex_coupon[$id]['quantity'] <= 0) {
				unset($storex_exchange[$id]);
				continue;
			}
			if (!empty($mine_coupon_num[$id]) && $mine_coupon_num[$id]['sum'] >= $info['pretotal']) {
				unset($storex_exchange[$id]);
				continue;
			}
			if ($info['starttime'] > TIMESTAMP || $info['endtime'] < TIMESTAMP) {
				unset($storex_exchange[$id]);
				continue;
			}
			$storex_exchange[$id]['received_total'] = !empty($coupon_total[$id]['total']) ? $coupon_total[$id]['total'] : 0;
			$storex_exchange[$id]['received_num'] = !empty($mine_coupon_num[$id]['sum']) ? $mine_coupon_num[$id]['sum'] : 0;
			$storex_exchange[$id]['coupon'] = $storex_coupon[$id];
		}
	}
	message(error(0, $storex_exchange), '', 'ajax');
}

if ($op == 'exchange') {
	$id = intval($_GPC['id']);
	$storex_exchange = pdo_get('storex_activity_exchange', array('uniacid' => $_W['uniacid'], 'extra' => $id));
	if (empty($storex_exchange)) {
		message(error(-1, '兑换券不存在'), '', 'ajax');
	}
	if ($storex_exchange['status'] != 1) {
		message(error(-1, '未开启兑换'), '', 'ajax');
	}
	$creditnames = array('credit1' => '积分', 'credit2' => '余额');
	$credit = mc_credit_fetch($uid, array($storex_exchange['credittype']));
	if (intval($credit[$storex_exchange['credittype']]) < $storex_exchange['credit']) {
		message(error(-1, $creditnames[$storex_exchange['credittype']] . '不足'), '', 'ajax');
	}
	$received_num = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_coupon_record') . " WHERE `uniacid` = :uniacid AND `uid` = :uid AND `couponid` = :id", array(':id' => $id, ':uid' => $uid, ':uniacid' => intval($_W['uniacid'])));
	if ($received_num >= $storex_exchange['pretotal']) {
		message(error(-1, '兑换次数不足'), '', 'ajax');
	}
	$coupon_info = activity_get_coupon_info($id);
	if ($storex_exchange['starttime'] > TIMESTAMP) {
		message(error(-1, '活动未开始'), '', 'ajax');
	}
	if ($storex_exchange['endtime'] < TIMESTAMP) {
		message(error(-1, '活动已结束'), '', 'ajax');
	}
	$status = activity_user_get_coupon($id, $_W['member']['uid']);
	if (is_error($status)) {
		message(error(-1, $status['message']), '', 'ajax');
	} else {
		mc_credit_update($_W['member']['uid'], $storex_exchange['credittype'], -1 * $storex_exchange['credit']);
		if ($storex_exchange['credittype'] == 'credit1') {
			mc_notice_credit1($_W['openid'], $_W['member']['uid'], -1 * $storex_exchange['credit'], '兑换卡券消耗积分');
		} elseif ($storex_exchange['credittype'] == 'credit2') {
			$card_info = card_setting_info();
			$recharges_set = $card_info['params']['cardRecharge'];
			if (empty($recharges_set['params']['recharge_type'])) {
				$grant_rate = $card_info['grant_rate'];
				mc_credit_update($_W['member']['uid'], 'credit1', $grant_rate * $storex_exchange['credit']);
			}
			mc_notice_credit2($_W['openid'], $_W['member']['uid'], -1 * $storex_exchange['credit'], $grant_rate * $storex_exchange['credit'], '兑换卡券消耗余额');
		}
		message(error(0, '兑换卡券成功!'), '', 'ajax');
	}
}
