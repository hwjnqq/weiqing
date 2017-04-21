<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('display', 'exchange', 'mine');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

check_params();
$uid = mc_openid2uid($_W['openid']);

if ($op == 'display') {
	$storex_exchange = pdo_getall('storex_activity_exchange', array('uniacid' => intval($_W['uniacid']), 'status' => 1), array(), 'extra');
	if (!empty($storex_exchange)) {
		$ids = array_keys($storex_exchange);
	}
	$storex_coupon = pdo_getall('storex_coupon', array('uniacid' => intval($_W['uniacid']), 'id' => $ids, 'source' => 1), array('id', 'logo_url', 'title', 'description', 'get_limit', 'date_info', 'sub_title'), 'id');
	if (!empty($storex_coupon)) {
		foreach ($storex_coupon as &$value) {
			$value['date_info'] = iunserializer($value['date_info']);
			if ($value['date_info']['time_type'] == '1'){
				$value['extra_date_info'] = '有效期:' . $value['date_info']['time_limit_start'] . '-' . $value['date_info']['time_limit_end'];
			} else {
				$value['extra_date_info'] = '有效期:领取后' . $value['date_info']['deadline'] . '天可用，有效期' . $value['date_info']['limit'] . '天';
			}
		}
	}
	//总领取数量
	$coupon_total = pdo_fetchall("SELECT COUNT(*) AS total, couponid FROM " . tablename('storex_coupon_record') . " WHERE uniacid = :uniacid AND couponid IN (" . implode(',', $ids) . ") GROUP BY couponid", array(':uniacid' => intval($_W['uniacid'])), 'couponid');
	//我的领取的数量
	$mine_coupon_num = pdo_fetchall("SELECT COUNT(*) AS sum, couponid FROM " . tablename('storex_coupon_record') . " WHERE uid = :uid GROUP BY couponid", array(':uid' => $uid), 'couponid');
	
	if (!empty($storex_exchange)) {
		foreach ($storex_exchange as $id => $info) {
			if (!empty($coupon_total[$id]) && $storex_coupon[$id]['quantity'] <= $coupon_total[$id]['total']) {
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
	$id = $_GPC['id'];
	
	message(error(-1, '领取会员卡失败!'), '', 'ajax');
}

if ($op == 'mine') {

	message(error(-1, '领取会员卡失败!'), '', 'ajax');
}




function doMobileActivity() {
	global $_W, $_GPC;
	checkauth();
	load()->model('activity');
	load()->model('mc');
	load()->classs('coupon');
	load()->func('tpl');
	$coupon_api = new coupon();
	//获取公众号积分策略
	$creditnames = array();
	$unisettings = uni_setting($uniacid, array('creditnames', 'coupon_type', 'exchange_enable'));
	if (!empty($unisettings) && !empty($unisettings['creditnames'])) {
		foreach ($unisettings['creditnames'] as $key=>$credit) {
			$creditnames[$key] = $credit['title'];
		}
	}
	/*获取当前公众号是否开启会员卡*/
	$cardstatus = pdo_get('mc_card', array('uniacid' => $_W['uniacid']), array('status'));
	$type_names = activity_coupon_type_label();
	activity_coupon_type_init();
	$colors = activity_coupon_colors();
	$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'display';
	$activity_type = trim($_GPC['activity_type']) ? trim($_GPC['activity_type']) : 'coupon';
	$cachekey = "modulesetting:{$_W['uniacid']}:we7_coupon";
	$we7_coupon_settings = cache_load($cachekey);
	if ($activity_type == 'coupon') {
		//兑换列表
		if($op == 'display') {
			if ($we7_coupon_settings['exchange_enable'] != '1') {
				message('未开启兑换功能');
			}
			$user = mc_fetch($_W['member']['uid'], array('groupid'));
			$fan = pdo_get('mc_mapping_fans', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid']));
			$groupid = $user['groupid'];
			$exchanges = pdo_fetchall("SELECT * FROM ". tablename('activity_exchange')." WHERE uniacid = :uniacid AND type = ".COUPON_TYPE." AND status = '1' AND starttime <= :time AND endtime >= :time", array(':uniacid' => $_W['uniacid'], ':time' => strtotime(date('Y-m-d'))), 'extra');
			foreach ($exchanges as $key => &$list) {
				$coupon_info = activity_coupon_info($list['extra']);
				$exchange_lists[$list['extra']] = $coupon_info;
				$person_total = pdo_fetchcolumn("SELECT COUNT(*) FROM ". tablename('coupon_record')."  WHERE uid = :uid AND uniacid = :uniacid AND couponid = :couponid", array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid'], ':couponid' => $list['extra']));
				if ($person_total >= $list['pretotal'] || $person_total >= $coupon_info['get_limit']){
					unset($exchange_lists[$list['extra']]);
					continue;
				}
				$stock = pdo_fetchcolumn("SELECT COUNT(*) FROM ". tablename('coupon_record')." WHERE uniacid = :uniacid AND couponid = :couponid",  array(':uniacid' => $_W['uniacid'], ':couponid' => $list['extra']));
				if ($stock > $coupon_info['quantity']) {
					unset($exchange_lists[$list['extra']]);
					continue;
				}
				$coupon_groups = pdo_getall('coupon_groups', array('uniacid' => $_W['uniacid'], 'couponid' => $list['extra']), array(), 'groupid');
				$coupon_groups = array_keys($coupon_groups);
				if (COUPON_TYPE == WECHAT_COUPON) {
					$fan_groups = explode(',', $fan['tag']);
					$group = array_intersect($coupon_groups, $fan_groups);
				} else {
					$group = pdo_get('coupon_groups', array('uniacid' => $_W['uniacid'], 'couponid' => $list['extra'], 'groupid' => $groupid));
				}
				if (empty($group) && !empty($coupon_groups)) {
					unset($exchange_lists[$list['extra']]);
					continue;
				}
				if (!empty($_W['current_module'])) {
					$coupon_modules = pdo_getall('coupon_modules', array('uniacid' => $_W['uniacid'], 'couponid' => $list['extra']), array(), 'module');
					if (!empty($coupon_modules) && empty($coupon_modules[$_W['current_module']['name']])) {
						unset($exchange_lists[$list['extra']]);
						continue;
					}
				}
				$exchange_lists[$list['extra']]['extra_href'] = $this->createMobileurl('activity', array('op' => 'exchange', 'activity_type' => 'coupon'));
				if (!empty($exchange_lists[$list['extra']])) {
					$exchange_lists[$list['extra']]['extra_func'] = $list;
					$exchange_lists[$list['extra']]['extra_func']['pic'] = 'resource/images/icon-signed.png';
				}
			}
		}
		//兑换过程
		if($op == 'exchange') {
			if ($we7_coupon_settings['exchange_enable'] != '1') {
				message(error(-1, '未开启兑换功能'), '', 'ajax');
			}
			$id = intval($_GPC['id']);
			$activity_exchange = pdo_get('activity_exchange', array('extra' => $id));
			$credit = mc_credit_fetch($_W['member']['uid'], array($activity_exchange['credittype']));
			if ($activity_exchange['credit'] < 0) {
				message(error(-1, '兑换' . $creditnames[$activity_exchange['credittype']] . '有误'), '', 'ajax');
			}
			if (intval($credit[$activity_exchange['credittype']]) < $activity_exchange['credit']) {
				message(error(-1, $creditnames[$activity_exchange['credittype']] . '不足'), '', 'ajax');
			}
			$pcount = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('coupon_record') . " WHERE `openid` = :openid AND `couponid` = :couponid", array(':couponid' => $coupon['id'], ':openid' => $_W['fans']['openid']));
			if ($pcount > $activity_exchange['pretotal']) {
				message(error(-1, '领取数量超限'), '', 'ajax');
			}
			if ($activity_exchange['starttime'] > strtotime(date('Y-m-d'))) {
				message(error(-1, '活动未开始'), '', 'ajax');
			}
			if ($activity_exchange['endtime'] < strtotime(date('Y-m-d'))) {
				message(error(-1, '活动已结束'), '', 'ajax');
			}
			$status = activity_coupon_grant($id, $_W['member']['uid']);
			if (is_error($status)) {
				message(error(-1, $status['message']), '', 'ajax');
			} else {
				mc_credit_update($_W['member']['uid'], $activity_exchange['credittype'], -1 * $activity_exchange['credit']);
				if ($activity_exchange['credittype'] == 'credit1') {
					mc_notice_credit1($_W['openid'], $_W['member']['uid'], -1 * $activity_exchange['credit'], '兑换卡券消耗积分');
				} else {
					$card_setting = pdo_get('mc_card', array('uniacid' => $_W['uniacid']));
					$recharges_set = card_params_setting('cardRecharge');
					if (empty($recharges_set['params']['recharge_type'])) {
						$grant_rate = $card_setting['grant_rate'];
						mc_credit_update($_W['member']['uid'], 'credit1', $grant_rate * $activity_exchange['credit']);
					}
					mc_notice_credit2($_W['openid'], $_W['member']['uid'], -1 * $activity_exchange['credit'], $grant_rate * $activity_exchange['credit'], '兑换卡券消耗余额');
				}
					
				message(error(0, '兑换成功'), $this->createMobileurl('activity', array('op' => 'mine', 'activity_type' => 'coupon')), 'ajax');
			}
		}
		//我的代金券
		if($op == 'mine') {
			$title = '我的卡券';
			activity_coupon_give();
			$coupon_records = activity_coupon_owned();
		}
		//使用代金券
		if($op == 'use') {
			$recid = intval($_GPC['recid']);
			$coupon_record = pdo_get('coupon_record', array('id' => $recid));
			$coupon_info = activity_coupon_info(trim($coupon_record['couponid']));
			$coupon_info['color'] = $colors[$coupon_info['color']] ? $colors[$coupon_info['color']] : '#63b359';
			if ($coupon_info['date_info']['time_type'] == '2') {
				$starttime = strtotime(date('Y-m-d', $coupon_record['addtime'])) + $coupon_info['date_info']['deadline'] * 86400;
				$endtime = $starttime + ($coupon_info['date_info']['limit'] - 1) * 86400;
				$coupon_info['extra_date_info'] = '有效期:' . date('Y.m.d', $starttime) . '-' . date('Y.m.d', $endtime);
			}
		}

		if ($op == 'detail') {
			$couponid = intval($_GPC['couponid']);
			$coupon_record = pdo_get('coupon_record', array('id' => intval($_GPC['recid'])));
			$coupon_info = activity_coupon_info($couponid);
			$coupon_info['description'] = $coupon_info['description'] ? $coupon_info['description'] : '暂无说明';
			if ($coupon_info['type'] == '1') {
				$coupon_info['discount_info'] = '凭此券消费打' . $coupon_info['extra']['discount'] * 0.1 . '折';
			} else {
				$coupon_info['discount_info'] = '价值' . $coupon_info['extra']['reduce_cost'] * 0.01 . '元代金券一张,消费满' . $coupon_info['extra']['least_cost'] * 0.01 . '元可使用';
			}
			if ($coupon_info['date_info']['time_type'] == '1') {
				$coupon_info['detail_date_info'] = $coupon_info['date_info']['time_limit_start'] . '-' . $coupon_info['date_info']['time_limit_end'];
			} else {
				$starttime = $coupon_record['addtime'] + $coupon_info['date_info']['deadline'] * 86400;
				$endtime = $starttime + ($coupon_info['date_info']['limit'] - 1) * 86400;
				$coupon_info['detail_date_info'] = date('Y.m.d', $starttime) . '-' . date('Y.m.d', $endtime);
			}
		}

		if ($op == 'qrcode') {
			$couponid = intval($_GPC['couponid']);
			$recid = intval($_GPC['recid']);

			$coupon_info = activity_coupon_info($couponid);
			$coupon_info['color'] = $colors[$coupon_info['color']] ? $colors[$coupon_info['color']] : '#63b359';
			$code_info = pdo_get('coupon_record', array('id' => $recid), array('code'));
			$coupon_info['code'] = $code_info['code'];
		}
		if ($op == 'opencard') {
			$id = intval($_GPC['id']);
			$code = trim($_GPC['code']);
			if($_W['isajax'] && $_W['ispost']) {
				$card = $coupon_api->BuildCardExt($id);
				if (is_error($card)) {
					message(error(1, $card['message']), '', 'ajax');
				} else {
					$openCard['card_id'] = $card['card_id'];
					$openCard['code'] = $code;
					message(error(0, $openCard), '', 'ajax');
				}
			}
		}
		if ($op == 'addcard') {
			$id = intval($_GPC['id']);
			if($_W['isajax'] && $_W['ispost']) {
				$card = $coupon_api->BuildCardExt($id);
				if (is_error($card)) {
					message(error(1, $card['message']), '', 'ajax');
				} else {
					message(error(0, $card), $this->createMobileurl('activity', array('activity_type' => 'coupon', 'op' => 'mine')), 'ajax');
				}
			}
		}
	}

	include $this->template('activitycoupon');
}