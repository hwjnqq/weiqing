<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('display', 'post', 'cardstatus');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$setting = pdo_get('storex_mc_card', array('uniacid' => $_W['uniacid']));

if ($op == 'display') {
	$fields_temp = mc_acccount_fields();
	$fields = array();
	foreach($fields_temp as $key => $val) {
		$fields[$key] = array(
			'title' => $val,
			'bind' => $key
		);
	}
	$params = json_decode($setting['params'], true);
	$params['cardBasic']['params']['description'] = str_replace("<br/>", "\n", $params['cardBasic']['params']['description']);
	$discounts_params = $params['cardActivity']['params']['discounts'];
	$discounts_temp = array();
	if(!empty($discounts_params)) {
		foreach($discounts_params as $row) {
			$discounts_temp[$row['groupid']] = $row;
		}
	}
	$discounts = array();
	foreach($_W['account']['groups'] as $group) {
		$discounts[$group['groupid']] = array(
			'groupid' => $group['groupid'],
			'title' => $group['title'],
			'credit' => $group['credit'],
			'condition_1' => $discounts_temp[$group['groupid']]['condition_1'],
			'discount_1' => $discounts_temp[$group['groupid']]['discount_1'],
			'condition_2' => $discounts_temp[$group['groupid']]['condition_2'],
			'discount_2' => $discounts_temp[$group['groupid']]['discount_2'],
		);
	}
	$params['cardActivity']['params']['discounts'] = $discounts;
	$setting['params'] = json_encode($params);
	$setting['params'] = preg_replace('/\n/', '', $setting['params']);
}

if ($op == 'post') {
	if ($_W['isajax'] && $_W['ispost']) {
		$params = $_GPC['params'];
		if (empty($params)) {
			message(error(-1, '请您先设计手机端页面'), '', 'ajax');
		}
		$basic = $params['cardBasic']['params'];
		$activity = $params['cardActivity']['params'];
		$nums = $params['cardNums']['params'];
		$times = $params['cardTimes']['params'];
		$recharges = $params['cardRecharge']['params'];
		$title = trim($basic['title']);
		$format_type = 1;
		$format = trim($basic['format']);

		//基本设置
		if (empty($title)) {
			message(error(-1, '名称不能为空'), '', 'ajax');
		}
		$basic['description'] = str_replace(array("\r\n", "\n"), '<br/>', $basic['description']);
		if(!empty($basic['fields'])) {
			foreach($basic['fields'] as $field) {
				if(!empty($field['title']) && !empty($field['bind'])) {
					$fields[] = $field;
				}
			}
		}
		if($basic['background']['type'] == 'system') {
			$image = pathinfo($basic['background']['image']);
			$basic['background']['image'] = $image['filename'];
		}
		//充值设置
		if (!empty($recharges['recharges'])) {
			foreach ($recharges['recharges'] as $row) {
				if ($recharges['recharge_type'] == 1 && ($row['condition'] <= 0 || $row['back'] <= 0)) {
					message(error(-1, '充值优惠设置数值不能为负数或零'), '', 'ajax');
				}
			}
		}
		//消费设置
		if ($activity['grant_rate'] < 0) {
			message(error(-1, '付款返积分比率不能为负数'), '', 'ajax');
		}
		$update = array(
			'title' => $title,
			'format_type' => $basic['format_type'],
			'format' => $format,
			'color' => iserializer($basic['color']),
			'background' => iserializer(array(
				'background' => $basic['background']['type'],
				'image' => $basic['background']['image'],
			)),
			'logo' => $basic['logo'],
			'description' => trim($basic['description']),
			'grant_rate' => intval($activity['grant_rate']),
			'offset_rate' => intval($basic['offset_rate']),
			'offset_max' => intval($basic['offset_max']),
			'fields' => iserializer($fields),
			'grant' => iserializer(
				array(
					'credit1' => intval($basic['grant']['credit1']),
					'credit2' => intval($basic['grant']['credit2']),
					'coupon' => $basic['grant']['coupon'],
				)
			),
			'discount_type' => intval($activity['discount_type']),
			'nums_status' => intval($nums['nums_status']),
			'nums_text' => trim($nums['nums_text']),
			'times_status' => intval($times['times_status']),
			'times_text' => trim($times['times_text']),
			'params' => stripslashes(ijson_encode($params, JSON_UNESCAPED_UNICODE)),
		);
		$grant = iunserializer($update['grant']);
		if ($grant['credit1'] < 0 || $grant['credit2'] < 0) {
			message(error(-1, '领卡赠送积分或余额不能为负数'), '', 'ajax');
		}
		if ($update['offset_rate'] < 0 || $update['offset_max'] < 0) {
			message(error(-1, '抵现比率的数值不能为负数或零'), '', 'ajax');
		}
		if($update['discount_type'] != 0 && !empty($activity['discounts'])) {
			$update['discount'] = array();
			foreach($activity['discounts'] as $discount) {
				if ($update['discount_type'] == 1) {
					if (!empty($discount['condition_1']) || !empty($discount['discount_1'])) {
						if ($discount['condition_1'] < 0 || $discount['discount_1'] < 0) {
							message(error(-1, '消费优惠设置数值不能为负数'), '', 'ajax');
						}
					}
				} else {
					if (!empty($discount['condition_2']) || !empty($discount['discount_2'])) {
						if ($discount['condition_2'] < 0 || $discount['discount_2'] < 0) {
							message(error(-1, '消费优惠设置数值不能为负数'), '', 'ajax');
						}
					}
				}
				$groupid = intval($discount['groupid']);
				if($groupid <= 0) continue;
				$update['discount'][$groupid] = array(
					'condition_1' => trim($discount['condition_1']),
					'discount_1' => trim($discount['discount_1']),
					'condition_2' => trim($discount['condition_2']),
					'discount_2' => trim($discount['discount_2']),
				);
			}
			$update['discount'] = iserializer($update['discount']);
		}
		if($update['nums_status'] != 0 && !empty($nums['nums'])) {
			$update['nums'] = array();
			foreach($nums['nums'] as $row) {
				if ($row['num'] <= 0 || $row['recharge'] <= 0) {
					message(error(-1, '充值返次数设置不能为负数或零'), '', 'ajax');
				}
				$num = floatval($row['num']);
				$recharge = trim($row['recharge']);
				if($num <= 0 || $recharge <= 0) continue;
				$update['nums'][$recharge] = array(
					'recharge' => $recharge,
					'num' => $num
				);
			}
			$update['nums'] = iserializer($update['nums']);
		}
		if($update['times_status'] != 0 && !empty($times['times'])) {
			$update['times'] = array();
			foreach($times['times'] as $row) {
				if ($row['time'] <= 0 || $row['recharge'] <= 0) {
					message(error(-1, '充值返时长设置不能为负数或零'), '', 'ajax');
				}
				$time = intval($row['time']);
				$recharge = trim($row['recharge']);
				if($time <= 0 || $recharge <= 0) continue;
				$update['times'][$recharge] = array(
					'recharge' => $recharge,
					'time' => $time
				);
			}
			$update['times'] = iserializer($update['times']);
		}
		if (!empty($setting)) {
			pdo_update('storex_mc_card', $update, array('uniacid' => $_W['uniacid']));
		} else {
			$update['status'] = '1';
			$update['uniacid'] = $_W['uniacid'];
			pdo_insert('storex_mc_card', $update);
		}
		$cachekey = "wn_storex_mc_card_setting:{$_W['uniacid']}";
		cache_delete($cachekey);
		get_card_setting();
		message(error(0, ''), $this->createWebUrl('membercard'), 'ajax');
	}
}

if ($op == 'cardstatus') {
	if(empty($setting)) {
		$open = array(
			'uniacid' => $_W['uniacid'],
			'title' => '我的会员卡',
			'format_type' => 1,
			'fields' => iserializer(array(
				array('title' => '姓名', 'require' => 1, 'bind' => 'realname'),
				array('title' => '手机', 'require' => 1, 'bind' => 'mobile'),
			)),
			'status' => 1,
		);
		pdo_insert('storex_mc_card', $open);
	}
	if (false === pdo_update('storex_mc_card', array('status' => intval($_GPC['status'])), array('uniacid' => $_W['uniacid']))) {
		message(error(-1, ''), '', 'ajax');
	}
	$extend_switch = extend_switch_fetch();
	$extend_switch['card'] = intval($_GPC['status']);
	$switch = iserializer($extend_switch);
	pdo_update('storex_set', array('extend_switch' => $switch), array('weid' => $_W['uniacid']));
	$cachekey = "wn_storex_switch:{$_W['uniacid']}";
	cache_delete($cachekey);
	message(error(0, ''), '', 'ajax');
}

include $this->template('membercard');