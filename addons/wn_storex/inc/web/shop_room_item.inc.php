<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'post', 'delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$room_info = pdo_get('storex_room', array('id' => $_GPC['roomid']), array('id', 'title'));
if ($op == 'display') {
	$room_list = pdo_getall('storex_room_items', array('uniacid' => $_W['uniacid'], 'storeid' => $_GPC['storeid'], 'roomid' => $_GPC['roomid']));
}

if ($op == 'post') {
	$room_item = pdo_get('storex_room_items', array('id' => $_GPC['id']), array('id', 'roomnumber', 'status'));
	if (checksubmit()) {
		$room_item = pdo_get('storex_room_items', array('id' => $_GPC['id']));
		$insert_room_data = array(
			'roomnumber' => $_GPC['roomnumber'],
			'status' => intval($_GPC['status'])
		);
		if (empty($room_item)) {
			$insert_room_data['uniacid'] = intval($_W['uniacid']);
			$insert_room_data['storeid'] = intval($_GPC['storeid']);
			$insert_room_data['roomid'] = intval($_GPC['roomid']);
			pdo_insert('storex_room_items', $insert_room_data);
		} else {
			pdo_update('storex_room_items', $insert_room_data, array('id' => $room_item['id']));
		}
		message('编辑成功', referer(), 'success');
	}
}

if ($op == 'delete') {
	$room_item = pdo_get('storex_room_items', array('id' => $_GPC['id']), array('id', 'roomnumber', 'status'));
	if (empty($room_item)) {
		message('参数错误', '', 'error');
	}
	pdo_delete('storex_room_items', array('id' => $room_item['id']));
	message('删除成功', referer(), 'success');
}

include $this->template('store/shop_room_item');