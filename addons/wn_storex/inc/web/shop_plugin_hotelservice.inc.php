<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('roommanage', 'confirm', 'telmanage', 'room_goods', 'goods_info', 'goods_delete', 'goods_status');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'roommanage';

$store_info = $_W['wn_storex']['store_info'];
$storeid = intval($store_info['id']);
load()->model('module');
$hotel_service = module_fetch('wn_storex_plugin_hotel_service');
if (empty($hotel_service)) {
	itoast('未购买酒店服务插件', referer(), 'error');
}
if ($op == 'roommanage') {
	$room_list = pdo_getall('storex_plugin_room_item', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array(), '', 'time DESC');
	if (!empty($room_list) && is_array($room_list)) {
		foreach ($room_list as $key => $value) {
			$items[$value['id']] = iunserializer($value['items']);
			$items[$value['id']]['storeid'] = $value['storeid'];
			$items[$value['id']]['openid'] = $value['openid'];
			$items[$value['id']]['time'] = $value['time'];
			$items[$value['id']]['status'] = $value['status'];
		}
	}
	if (!empty($items) && is_array($items)) {
		foreach ($items as $key => $item) {
			$room_items[$key]['info'] = $item['room'] . '住户需要以下服务：【' . date('Y-m-d H:i:s', $item['time']) . '】牙刷牙膏' . $item['brush'] . '个，毛巾' . $item['towel'] . '个，卫生纸' . $item['paper'] . '卷。' . $item['other'];
			$room_items[$key]['time'] = $item['time'];
			$room_items[$key]['status'] = $item['status'];
		}
	}
}

if ($op == 'confirm') {
	$id = intval($_GPC['id']);
	$room_item = pdo_get('storex_plugin_room_item', array('id' => $id));
	if ($room_item['status'] == 2) {
		itoast('该预约已确认', referer(), 'error');
	}
	pdo_update('storex_plugin_room_item', array('status' => 2), array('id' => $id));
	$account_api = WeAccount::create();
	$message = array(
		'msgtype' => 'text',
		'text' => array('content' => urlencode('您的预约已确认，请耐心等待')),
		'touser' => $room_item['openid']
	);
	$account_api->sendCustomNotice($message);
	itoast('确认成功', referer(), 'success');
}

if ($op == 'telmanage') {
	$tel_info = pdo_get('storex_plugin_tel', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
	if (checksubmit()) {
		if (empty($tel_info)) {
			pdo_insert('storex_plugin_tel', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'tel' => $_GPC['tel']));
			$result = pdo_insertid();
		} else {
			$result = pdo_update('storex_plugin_tel', array('tel' => $_GPC['tel']), array('id' => $tel_info['id']));
		}
		if (!empty($result)) {
			itoast('编辑成功', '', 'success');
		} else {
			itoast('编辑失败', '', 'error');
		}
	}
}

if ($op == 'room_goods') {
	$goods_list = pdo_getall('storex_plugin_room_goods', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
	if ($_W['ispost'] && $_W['isajax']) {
		$id = intval($_GPC['id']);
		$title = trim($_GPC['title']);
		$price = trim($_GPC['price']);
		if (empty($title) || empty($price) || $price < 0) {
			message(error(-1, '编辑错误'), '', 'ajax');
		}
		$goods_info = pdo_get('storex_plugin_room_goods', array('id' => $id), array('id'));
		if (empty($goods_info)) {
			pdo_insert('storex_plugin_room_goods', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'title' => $title, 'price' => $price));
		} else {
			pdo_update('storex_plugin_room_goods', array('title' => $title, 'price' => $price), array('id' => $id));
		}
		message(error(0, ''), referer(), 'ajax');
	}
}

if ($op == 'goods_info') {
	if ($_W['ispost'] && $_W['isajax']) {
		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$goods_info = pdo_get('storex_plugin_room_goods', array('id' => $id), array('id', 'title', 'price'));
			$goods_info['title'] = !empty($goods_info['title']) ? $goods_info['title'] : '';
			$goods_info['price'] = !empty($goods_info['price']) ? $goods_info['price'] : '';
			message(error(0, $goods_info), '', 'ajax');
		}
	}
}

if ($op == 'goods_delete') {
	$id = intval($_GPC['id']);
	$goods_info = pdo_get('storex_plugin_room_goods', array('id' => $id, 'uniacid' => $_W['uniacid']), array('id'));
	if (empty($goods_info)) {
		itoast('商品信息错误', referer(), 'error');
	}
	pdo_delete('storex_plugin_room_goods', array('id' => $id, 'uniacid' => $_W['uniacid']));
	itoast('删除成功', referer(), 'success');
}

if ($op == 'goods_status') {
	$id = intval($_GPC['id']);
	$goods_info = pdo_get('storex_plugin_room_goods', array('id' => $id, 'uniacid' => $_W['uniacid']), array('id', 'status'));
	if (empty($goods_info)) {
		itoast('商品信息错误', referer(), 'error');
	}
	$result = pdo_update('storex_plugin_room_goods', array('status' => $_GPC['status']), array('id' => $id));
	if (empty($result)) {
		itoast('修改失败', referer(), 'error');
	} else {
		itoast('修改成功', referer(), 'success');
	}
}

include $this->template('store/shop_plugin_hotelservice');