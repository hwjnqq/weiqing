<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('new', 'cut', 'gift', 'pickup');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'new';

$storeid = intval($_W['wn_storex']['store_info']['id']);
$store_info = $_W['wn_storex']['store_info'];
$market_list = pdo_getall('storex_market', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), '', 'type');
if (!empty($market_list) && is_array($market_list)) {
	foreach ($market_list as $key => &$value) {
		if ($key == 'cut' || $key == 'gift' || $key == 'pickup') {
			$value['items'] = iunserializer($value['items']);
		}
	}
}
$new_list = !empty($market_list['new']) ? $market_list['new'] : array();
$cut_list = !empty($market_list['cut']) ? $market_list['cut'] : $market_list['gift'];
$cut_list = !empty($cut_list) ? $cut_list : array();
$pickup_list = !empty($market_list['pickup']) ? $market_list['pickup'] : array();
$new_list['starttime'] = !empty($new_list['starttime']) ? $new_list['starttime'] : time();
$new_list['endtime'] = !empty($new_list['endtime']) ? $new_list['endtime'] : time() + 30 * 86400;
$cut_list['starttime'] = !empty($cut_list['starttime']) ? $cut_list['starttime'] : time();
$cut_list['endtime'] = !empty($cut_list['endtime']) ? $cut_list['endtime'] : time() + 30 * 86400;
$pickup_list['starttime'] = !empty($pickup_list['starttime']) ? $pickup_list['starttime'] : time();
$pickup_list['endtime'] = !empty($pickup_list['endtime']) ? $pickup_list['endtime'] : time() + 30 * 86400;
if (checksubmit()) {
	$time_limit = $_GPC['time_limit'];
	if ($_GPC['type'] == 'new') {
		$new_info = array(
			'starttime' => strtotime($time_limit['start']),
			'endtime' => strtotime($time_limit['end']),
			'type' => 'new',
			'items' => $_GPC['back'],
			'status' => intval($_GPC['status']),
		);
		if (empty($market_list['new'])) {
			$new_info['uniacid'] = $_W['uniacid'];
			$new_info['storeid'] = $storeid;
			pdo_insert('storex_market', $new_info);
		} else {
			pdo_update('storex_market', $new_info, array('id' => $market_list['new']['id']));
		}
	} elseif ($_GPC['type'] == 'cut' || $_GPC['type'] == 'gift') {
		$types = array('cut', 'gift');
		$discounts['condition'] = !empty($_GPC['condition']) ? $_GPC['condition'] : 0;
		$discounts['back'] = !empty($_GPC['back']) ? $_GPC['back'] : 0;
		$cut_info = array(
			'starttime' => strtotime($time_limit['start']),
			'endtime' => strtotime($time_limit['end']),
			'type' => $_GPC['type'],
			'items' => iserializer($discounts),
			'uniacid' => $_W['uniacid'],
			'storeid' => $storeid,
			'status' => intval($_GPC['status']),
		);
		pdo_delete('storex_market', array('uniacid' => $_W['uniacid'], 'type' => $types, 'storeid' => $storeid));
		pdo_insert('storex_market', $cut_info);
	} elseif ($_GPC['type'] == 'pickup') {
		$discounts['condition'] = !empty($_GPC['condition']) ? $_GPC['condition'] : 0;
		$discounts['back'] = !empty($_GPC['back']) ? $_GPC['back'] : 0;
		$pickup_info = array(
			'starttime' => strtotime($time_limit['start']),
			'endtime' => strtotime($time_limit['end']),
			'type' => 'pickup',
			'items' => iserializer($discounts),
			'status' => intval($_GPC['status']),
		);
		if (empty($market_list['pickup'])) {
			$pickup_info['uniacid'] = $_W['uniacid'];
			$pickup_info['storeid'] = $storeid;
			pdo_insert('storex_market', $pickup_info);
		} else {
			pdo_update('storex_market', $pickup_info, array('id' => $market_list['pickup']['id']));
		}
	}
	message('编辑成功', referer(), 'success');
}

include $this->template('store/shop_market');