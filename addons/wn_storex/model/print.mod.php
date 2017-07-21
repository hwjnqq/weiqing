<?php
load()->func('communication');
/*
 * $printerid 打印机ID
 * $orderid 订单ID
 * $storeid 店铺ID
 * */

function print_order($printerid, $orderid, $storeid) {
	global $_W, $_GPC;
	$printer_info = pdo_get('storex_plugin_printer', array('id' => $printerid));
	
	if (empty($printer_info)) {
		print_insert_log(array('status' => 1, 'storeid' => $storeid, 'message' => '打印机信息错误'));
		return error(-1, '打印机信息错误');
	}
	if ($printer_info['status'] != 2) {
		print_insert_log(array('status' => 1, 'storeid' => $storeid, 'message' => '打印机未开启'));
		return error(-1, '打印机未开启');
	}
	
	$content = print_get_content($printer_info, $orderid);
	if (empty($content)) {
		print_insert_log(array('status' => 1, 'storeid' => $storeid, 'message' => '订单错误'));
		return error(-1, '订单错误！');
	}
	mload()->classs('printer');
	$printer_api = new Printer($printer_info['user'], $printer_info['key'], $printer_info['sn']);
	$result = $printer_api->printOrderAction($content);
	if (empty($result['data'])) {
		$msg = !empty($result['msg']) ? $result['msg'] : '连接API失败';
		print_insert_log(array('status' => 1, 'storeid' => $storeid, 'message' => $result['msg']));
		return error(-1, $msg);
	}
	print_insert_log(array('status' => 2, 'storeid' => $storeid, 'message' => $result['data']));
	return $result;
}

function print_insert_log($params) {
	global $_W;
	$logs['uniacid'] = intval($_W['uniacid']);
	$logs['status'] = intval($params['status']);
	$logs['storeid'] = trim($params['storeid']);
	$logs['message'] = trim($params['message']);
	$logs['time'] = time();
	pdo_insert('storex_plugin_print_logs', $logs);
}

function print_get_content($printer_info, $orderid) {
	global $_W;
	$order_info = pdo_get('storex_order', array('id' => $orderid), array('remark', 'time', 'sum_price', 'mobile', 'static_price', 'hotelid', 'roomid', 'ordersn', 'cprice', 'nums', 'day', 'contact_name', 'mode_distribute', 'addressid', 'order_time'));
	if (empty($order_info)) {
		return array();
	}
	$storeid = $order_info['hotelid'];
	$storeinfo = get_store_info($order_info['hotelid']);
	$goods_table = gettablebytype($storeinfo['store_type']);
	$fields = array('title', 'express_set');
	if ($goods_table == 'storex_room') {
		$fields[] = 'is_house';
		$fields[] = 'service';
	}
	$goodsinfo = pdo_get($goods_table, array('id' => $order_info['roomid']), $fields);
	$title = !empty($printer_info['header']) ? $printer_info['header'] : $storeinfo['title'];
	$content['title'] = '<CB>' . $title . '</CB><BR>';
	$content[] = '--------------------------------<BR>';
	$ordersn = !empty($order_info['ordersn']) ? $order_info['ordersn'] : '暂无';
	$content[] = '订单编号　' . $ordersn . '<BR>';
	$content[] = '名称　　　' . $goodsinfo['title'] . '<BR>';
	$content[] = '单价　　　' . $order_info['cprice'] . '元<BR>';
	
	if ($storeinfo['store_type'] == 1) {
		if ($goodsinfo['is_house'] == 1) {
			$content[] = '服务费　　' . $goodsinfo['service'] * $order_info['nums'] * $order_info['day'] . '<BR>';
			$content[] = '房间数量　' . $order_info['nums'] . '<BR>';
			$content[] = '入住天数　' . $order_info['day'] . '<BR>';
			$content[] = '联系人　　' . $order_info['contact_name'] . '<BR>';
		} else {
			$content[] = '数量　　　' . $order_info['nums'] . '<BR>';
			if ($order_info['mode_distribute'] == 1) {
				$content[] = '提货方式　自提 <BR>';
				$content[] = '自提时间　' . date('Y-m-d H:i:s', $order_info['order_time']) . '<BR>';
			} else {
				$content[] = '提货方式　配送 <BR>';
				$content[] = '配送时间　' . date('Y-m-d H:i:s', $order_info['order_time']) . '<BR>';
				$address = print_get_address($order_info['addressid']);
			}
		}
	} else {
		$content[] = '数量　　　' . $order_info['nums'] . '<BR>';
		if ($order_info['mode_distribute'] == 1) {
			$content[] = '提货方式　自提 <BR>';
			$content[] = '自提时间　' . date('Y-m-d H:i', $order_info['order_time']) . '<BR>';
		} else {
			$content[] = '提货方式　配送 <BR>';
			$content[] = '配送时间　' . date('Y-m-d H:i', $order_info['order_time']) . '<BR>';
			$address = print_get_address($order_info['addressid']);
		}
	}
	if ($order_info['mode_distribute'] == 2) {
		//邮费
		if (!empty($goodsinfo['express_set'])) {
			$express_set = iunserializer($goodsinfo['express_set']);
			if (!empty($express_set['express'])) {
				if ($order_info['static_price'] < $express_set['full_free']) {
					$content[] = '邮费　　　' . $express_set['express'] . '<BR>';
				} else {
					$content[] = '邮费　　　免邮 <BR>';
				}
			}
		}
		$content[] = '送货地址　' . $address . '<BR>';
	}
	$content[] = '联系电话　' . $order_info['mobile'] . ' <BR>';
	$content[] = '下单时间　' . date('Y-m-d H:i', $order_info['time']) . '<BR>';
	$content[] = '--------------------------------<BR>';
	$content[] = '合计　' . $order_info['sum_price'] . ' 元<BR>';
	$content[] = '备注　' . $order_info['remark'] .'<BR>';
	
	if (!empty($printer_info['footer'])) {
		$content[] = $printer_info['footer'] . '<BR>';
	}
	$content[] = '<BR>';
	$content[] = '<QR>' . $printer_info['qrcode'] . '</QR>';
	return $content;
}

function print_get_address($addressid) {
	$address = '';
	if (!empty($addressid)) {
		$address_info = pdo_get('mc_member_address', array('id' => $addressid), array('province', 'city', 'district', 'address'));
		if (!empty($address_info)) {
			$address = $address_info['province'] . $address_info['city'] . $address_info['district'] . $address_info['address'];
		}
	}
	return $address;
}