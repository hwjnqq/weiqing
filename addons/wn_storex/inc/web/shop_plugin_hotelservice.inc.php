<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('roommanage', 'confirm', 'telmanage', 'room_goods', 'goods_info', 'goods_delete', 'goods_status', 'foods_set', 'foods_lists', 'foods_edit', 'foods_delete', 'foods_deleteall', 'foods_showall', 'foods_status', 'foods_order', 'foods_deleteorder', 'foods_deleteorderall', 'foods_editorder');
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

if ($op == 'foods_set') {
	if (checksubmit('submit')) {
		$insert = array(
			'uniacid' => $_W['uniacid'],
			'storeid' => $storeid,
			'place' => iserializer($_GPC['place']),
			'foods_set' => iserializer($_GPC['time']),
		);
		if (!empty($_GPC['id'])) {
			pdo_update('storex_plugin_foods_set', $insert, array('id' => intval($_GPC['id'])));
		} else {
			pdo_insert('storex_plugin_foods_set', $insert);
		}
		itoast('更新餐品成功', $this->createWebUrl('shop_plugin_hotelservice', array('storeid' => $storeid, 'op' => 'foods_set')), 'success');
	} else {
		$foods_set = pdo_get('storex_plugin_foods_set', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
		$food_place = iunserializer($foods_set['place']);
		$food_times = iunserializer($foods_set['foods_set']);
	}
}

if ($op == 'foods_lists') {
	$foods = pdo_getall('storex_plugin_foods', array('weid' => $_W['uniacid'], 'storeid' => $storeid));
}

if ($op == 'foods_edit') {
	$id = intval($_GPC['id']);
	$food_times = pdo_get('storex_plugin_foods_set', array('storeid' => $storeid), array('foods_set'));
	if (!empty($food_times['foods_set'])) {
		$food_times = iunserializer($food_times['foods_set']);
	}
	if (!empty($id)) {
		$food = pdo_get('storex_plugin_foods', array('storeid' => $storeid, 'id' => $id));
		if (!empty($food['foods_set'])) {
			$food['foods_set'] = explode(',', $food['foods_set']);
		}
		if (!empty($food['thumbs'])) {
			$food['thumbs'] = iunserializer($food['thumbs']);
		}
	}
	if (checksubmit('submit')) {
		$data = array(
			'weid' => intval($_W['uniacid']),
			'title' => $_GPC['title'],
			'storeid' => $storeid,
			'price' => sprintf('%.2f', $_GPC['price']),
			'sold_num' => intval($_GPC['sold_num']),
			'thumbs' => iserializer($_GPC['thumbs']),
			'content' => htmlspecialchars_decode($_GPC['content']),
			'status' => intval($_GPC['status']),
		);
		if (!empty($_GPC['foods_set'])) {
			$data['foods_set'] = implode(',', $_GPC['foods_set']);
		}
		if (empty($data['title'])) {
			itoast('名称不能为空', '', 'error');
		}
		if (!empty($id)) {
			pdo_update('storex_plugin_foods', $data, array('id' => $id));
		} else {
			pdo_insert('storex_plugin_foods', $data);
		}
		itoast('更新餐品成功', $this->createWebUrl('shop_plugin_hotelservice', array('storeid' => $storeid, 'op' => 'foods_lists')), 'success');
	}
}

if ($op == 'foods_delete') {
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		pdo_delete('storex_plugin_foods', array('id' => $id));
		itoast('删除成功', $this->createWebUrl('foodmanage', array('storeid' => $storeid, 'op' => 'lists')), 'success');
	} else {
		itoast('参数错误', '', 'error');
	}
}

if ($op == 'foods_deleteall') {
	if (!empty($_GPC['idArr'])) {
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			pdo_delete('storex_plugin_foods', array('id' => $id, 'weid' => $_W['uniacid']));
		}
		message(error(0, '删除成功！'), $this->createWebUrl('shop_plugin_hotelservice', array('storeid' => $storeid, 'op' => 'foods_lists')), 'ajax');
	} else {
		message(error(-1, '删除失败！'), '', 'ajax');
	}
}
if ($op == 'foods_showall') {
	if ($_GPC['show_name'] == 'foods_showall') {
		$show_status = 1;
	} else {
		$show_status = 2;
	}
	if (!empty($_GPC['idArr'])) {
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			if (!empty($id)) {
				pdo_update('storex_plugin_foods', array('status' => $show_status), array('id' => $id));
			}
		}
		message(error(0, '操作成功！'), $this->createWebUrl('shop_plugin_hotelservice', array('storeid' => $storeid, 'op' => 'foods_lists')), 'ajax');
	} else {
		message(error(-1, '操作失败！'), '', 'ajax');
	}
}
if ($op == 'foods_status') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		message('参数错误！', referer(), 'error');
	}
	$status = pdo_update('storex_plugin_foods', array('status' => $_GPC['status']), array('id' => $id));
	if (!empty($status)) {
		message('设置成功！', referer(), 'success');
	} else {
		message('操作失败！', referer(), 'error');
	}
}

if ($op == 'foods_order') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 3;
	$orders = pdo_getall('storex_plugin_foods_order', array('storeid' => $storeid), array(), '', 'time DESC', array($pindex, $psize));
	$total = count(pdo_getall('storex_plugin_foods_order', array('storeid' => $storeid)));
	if (!empty($orders) && is_array($orders)) {
		foreach ($orders as &$info) {
			$info['foods'] = iunserializer($info['foods']);
		}
		unset($info);
	}
	$pager = pagination($total, $pindex, $psize);
}

if ($op == 'foods_deleteorder') {
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		pdo_delete('storex_plugin_foods_order', array('id' => $id));
		itoast('删除成功', $this->createWebUrl('shop_plugin_hotelservice', array('storeid' => $storeid, 'op' => 'foods_order')), 'success');
	} else {
		itoast('参数错误', '', 'error');
	}
}

if ($op == 'foods_deleteorderall') {
	if (!empty($_GPC['idArr'])) {
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			pdo_delete('storex_plugin_foods_order', array('id' => $id, 'weid' => $_W['uniacid']));
		}
		message(error(0, '删除成功！'), $this->createWebUrl('shop_plugin_hotelservice', array('storeid' => $storeid, 'op' => 'foods_order')), 'ajax');
	} else {
		message(error(-1, '删除失败！'), '', 'ajax');
	}
}

if ($op == 'foods_editorder') {
	$orderid = intval($_GPC['id']);
	$order = pdo_get('storex_plugin_foods_order', array('id' => $orderid));
	if (!empty($order)) {
		$order['foods'] = iunserializer($order['foods']);
	}
	if ($_W['isajax'] && $_W['ispost']) {
		$status = $_GPC['status'];
		$store = pdo_get('storex_bases', array('id' => $order['storeid']), array('id', 'title'));
		if ($order['status'] == $status) {
			message(error(-1, '编辑失败'), '', 'ajax');
		}
		if ($order['status'] == 1 && $status == -1) {
			message(error(-1, '确认订单不能取消'), '', 'ajax');
		}
		$update = array('status' => $status);
		if ($status == -1) {
			$info = '您在' . $store['title'] . '下的订单，订单号:' . $order['ordersn'] . '被拒绝了,请联系管理员！';
		}
		if ($status == 1) {
			$info = '您在' . $store['title'] . '下的订单，订单号:' . $order['ordersn'] . '已被酒店确认,请联系管理员！';
		}
		if ($status == 2) {
			$info = '您在' . $store['title'] . '下的订单，订单号:' . $order['ordersn'] . '已经完成,请联系管理员！';
			foreach ($order['foods'] as $info) {
				pdo_update('storex_plugin_foods', array('sold_num +=' => $info['num']), array('id' => $info['id']));
			}
		}
		send_custom_notice('text', array('content' => urlencode($info)), $_W['openid']);
		pdo_update('storex_plugin_foods_order', array('status' => $status), array('id' => $orderid));
		message(error(0, '编辑成功！'), $this->createWebUrl('shop_plugin_hotelservice', array('storeid' => $storeid, 'op' => 'foods_editorder', 'id' => $orderid)), 'ajax');
	}
}
include $this->template('store/shop_plugin_hotelservice');