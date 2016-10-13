<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

function order_fetch($id) {
	global $_W;
	$id = intval($id);
	$order = pdo_fetch('SELECT * FROM ' . tablename('tiny_wmall_order') . ' WHERE uniacid = :aid AND id = :id', array(':aid' => $_W['uniacid'], ':id' => $id));
	$pay_types = order_pay_types();
	if(empty($order['is_pay'])) {
		$order['pay_type'] = '未支付';
	} else {
		$order['pay_type'] = !empty($pay_types[$order['pay_type']]['text']) ? $pay_types[$order['pay_type']]['text'] : '其他支付方式';
	}
	if(empty($order['delivery_time'])) {
		$order['delivery_time'] = '尽快送出';
	}
	return $order;
}

function order_fetch_goods($oid) {
	global $_W;
	$oid = intval($oid);
	$data = pdo_fetchall('SELECT * FROM ' . tablename('tiny_wmall_order_stat') . ' WHERE uniacid = :aid AND oid = :oid', array(':aid' => $_W['uniacid'], ':oid' => $oid));
	return $data;
}

function order_fetch_discount($id) {
	global $_W;
	$data = pdo_getall('tiny_wmall_order_discount', array('uniacid' => $_W['uniacid'], 'oid' => $id));
	return $data;
}

function order_place_again($sid, $order_id) {
	global $_W;
	$order = order_fetch($order_id);
	if(empty($order)) {
		return false;
	}
	$isexist = pdo_fetchcolumn('SELECT id FROM ' . tablename('tiny_wmall_order_cart') . " WHERE uniacid = :aid AND sid = :sid AND uid = :uid", array(':aid' => $_W['uniacid'], ':sid' => $sid, ':uid' => $_W['member']['uid']));
	$data = array(
		'uniacid' => $_W['uniacid'],
		'sid' => $sid,
		'uid' => $_W['member']['uid'],
		'groupid' => $_W['member']['groupid'],
		'num' => $order['num'],
		'price' => $order['price'],
		'data' =>  $order['data'],
		'addtime' => TIMESTAMP,
	);
	if(empty($isexist)) {
		pdo_insert('tiny_wmall_order_cart', $data);
	} else {
		pdo_update('tiny_wmall_order_cart', $data, array('uniacid' => $_W['uniacid'], 'id' => $isexist, 'uid' => $_W['member']['uid']));
	}
	$data['data'] = iunserializer($order['data']);
	return $data;
}

//order_insert_discount
function order_insert_discount($id, $sid, $discount_data) {
	global $_W;
	if(empty($discount_data)) {
		return false;
	}
	foreach($discount_data as $data) {
		$insert = array(
			'uniacid' => $_W['uniacid'],
			'sid' => $sid,
			'oid' => $id,
			'type' => $data['type'],
			'name' => $data['name'],
			'icon' => $data['icon'],
			'note' => $data['text'],
			'fee' => $data['value']
		);
		pdo_insert('tiny_wmall_order_discount', $insert);
	}
	return true;
}

//order_insert_status_log
function order_insert_status_log($id, $sid, $type, $note = '') {
	global $_W;
	if(empty($type)) {
		return false;
	}
	mload()->model('store');
	$order = order_fetch($id);
	$store = store_fetch($order['sid'], array('pay_time_limit'));
	$notes = array(
		'place_order' => array(
			'status' => 1,
			'title' => '订单提交成功',
			'note' => "单号:{$order['ordersn']},请耐心等待商家确认",
			'ext' => array(
				array(
					'key' => 'pay_time_limit',
					'title' => '待支付',
					'note' => "请在订单提交后{$store['pay_time_limit']}分钟内完成支付",
				)
			)
		),
		'handel' => array(
			'status' => 2,
			'title' => '商户已经确认订单',
			'note' => '正在为您准备商品'
		),
		'delivery_wait' => array(
			'status' => 3,
			'title' => '商品已准备就绪',
			'note' => '商品已准备就绪,正在分配配送员'
		),
		'delivery_ing' => array(
			'status' => 4,
			'title' => '已分配配送员',
			'note' => ''
		),
		'end' => array(
			'status' => 5,
			'title' => '订单已完成',
			'note' => '任何已经和吐槽,都欢迎联系我们'
		),
		'cancel' => array(
			'status' => 6,
			'title' => '订单已取消',
			'note' => ''
		),
		'pay' => array(
			'status' => 7,
			'title' => '订单已支付',
			'note' => '0',
		),
		'remind' => array(
			'status' => 8,
			'title' => '商家已收到催单',
			'note' => '商家会尽快回复您的催单请求'
		),
		'remind_reply' => array(
			'status' => 9,
			'title' => '商家回复了您的催单',
			'note' => ''
		),
		'delivery_success' => array(
			'status' => 10,
			'title' => '订单配送完成',
			'note' => ''
		),
		'delivery_fail' => array(
			'status' => 10,
			'title' => '订单配送失败',
			'note' => ''
		),
	);
	$title = $notes[$type]['title'];
	$note = $note ? $note : $notes[$type]['note'];
	$data = array(
		'uniacid' => $_W['uniacid'],
		'sid' => $sid,
		'oid' => $id,
		'status' => $notes[$type]['status'],
		'type' => $type,
		'title' => $title,
		'note' => $note,
		'addtime' => TIMESTAMP,
	);
	pdo_insert('tiny_wmall_order_status_log', $data);
	if(!empty($notes[$type]['ext'])) {
		foreach($notes[$type]['ext'] as $val) {
			if($val['key'] == 'pay_time_limit' && !$store['pay_time_limit']) {
				unset($val['note']);
			}
			$data = array(
				'uniacid' => $_W['uniacid'],
				'sid' => $sid,
				'oid' => $id,
				'title' => $val['title'],
				'note' => $val['note'],
				'addtime' => TIMESTAMP,
			);
			pdo_insert('tiny_wmall_order_status_log', $data);
		}
	}
	return true;
}

//order_fetch_status_log
function order_fetch_status_log($id) {
	global $_W;
	$data = pdo_fetchall("SELECT * FROM " . tablename('tiny_wmall_order_status_log') . ' WHERE uniacid = :uniacid and oid = :oid order by id asc', array(':uniacid' => $_W['uniacid'], ':oid' => $id), 'id');
	return $data;
}

//print_order
function order_print($id) {
	global $_W;
	$order= order_fetch($id);
	if(empty($order)) {
		return error(-1, '订单不存在');
	}
	mload()->model('store');
	$sid = intval($order['sid']);
	$store = store_fetch($order['sid'], array('title'));
	//获取该门店的所有打印机
	$prints = pdo_fetchall('SELECT * FROM ' . tablename('tiny_wmall_printer') . ' WHERE uniacid = :aid AND sid = :sid AND status = 1', array(':aid' => $_W['uniacid'], ':sid' => $sid));
	if(empty($prints)) {
		return error(-1, '没有有效的打印机');
	}
	mload()->model('print');
	$order['goods'] = order_fetch_goods($order['id']);

	$num = 0;
	foreach($prints as $li) {
		if(!empty($li['print_no']) && !empty($li['key'])) {
			$content = array(
				"<CB>{$store['title']}</CB>",
			);
			if(!empty($li['print_header'])) {
				$content[] = $li['print_header'];
			}
			$content[] = '名称　　　　　 单价  数量 金额';
			$content[] = '****************************';
			foreach($order['goods'] as $di) {
				$content[] = str_pad(cutstr($di['goods_title'], 7), '21', '　', STR_PAD_RIGHT);
				$content[] = ' ' . str_pad($di['goods_unit_price'], '6', ' ', STR_PAD_RIGHT);
				$content[] = 'X ' . str_pad($di['goods_num'], '3', ' ', STR_PAD_RIGHT);
				$content[] = ' ' . str_pad($di['goods_price'], '5', ' ', STR_PAD_RIGHT);
			}
			$content[] = '****************************';
			$content[] = "订单　号：{$order['ordersn']}";
			$content[] = "支付方式：{$order['pay_type']}";
			$content[] = "配送　费：{$order['delivery_fee']}";
			$content[] = "合　　计：{$order['total_price']}";
			if($order['discount_fee'] > 0) {
				$content[] = "线上优惠: -{$order['discount_fee']}元";
				$content[] = "实际支付: {$order['final_fee']}元";
			}
			$content[] = "下单　人：{$order['username']}";
			$content[] = "联系电话：{$order['mobile']}";
			$content[] = "配送地址：{$order['address']}";
			$content[] = "下单时间：".date('Y-m-d H:i', $order['addtime']);
			if(!empty($li['qrcode_link'])) {
				$content[] = "<QR>{$li['qrcode_link']}</QR>";
			}

			$status = print_add_order($li['type'], $li['print_no'], $li['key'], $li['member_code'], $content, $li['print_times'], $li['ordersn']);
			if(!is_error($status)) {
				$num++;
				$data = array(
					'uniacid' => $_W['uniacid'],
					'sid' => $order['sid'],
					'pid' => $li['id'], //打印机id
					'oid' => $order['id'], //订单id
					'status' => 2,
					'foid' => $status, //打印机下发的唯一打印编号
					'printer_type' => $li['type'], //打印机品牌
					'addtime' => TIMESTAMP
				);
				pdo_insert('tiny_wmall_order_print_log', $data);
			}
		}
	}

	if($num > 0) {
		pdo_query('UPDATE ' . tablename('tiny_wmall_order') . " SET print_nums = print_nums + {$num} WHERE uniacid = {$_W['uniacid']} AND id = {$order['id']}");
	} else {
		return error(-1,'发送打印指令失败。没有有效的打印机或没有开启打印机');
	}
	return true;
}

function order_status() {
	$data = array(
		'0' => array(
			'css' => '',
			'text' => '所有',
			'color' => ''
		),
		'1' => array(
			'css' => 'label label-default',
			'text' => '待确认',
			'color' => '',
			'color' => ''
		),
		'2' => array(
			'css' => 'label label-info',
			'text' => '处理中',
			'color' => 'color-primary'
		),
		'3' => array(
			'css' => 'label label-warning',
			'text' => '待配送',
			'color' => 'color-warning'
		),
		'4' => array(
			'css' => 'label label-warning',
			'text' => '配送中',
			'color' => 'color-warning'
		),
		'5' => array(
			'css' => 'label label-success',
			'text' => '已完成',
			'color' => 'color-success'
		),
		'6' => array(
			'css' => 'label label-danger',
			'text' => '已取消',
			'color' => 'color-danger'
		)
	);
	return $data;
}

function order_delivery_status() {
	$data = array(
		'0' => array(
			'css' => '',
			'text' => '',
			'color' => ''
		),
		'3' => array(
			'css' => 'label label-warning',
			'text' => '待配送',
			'color' => 'color-warning'
		),
		'4' => array(
			'css' => 'label label-warning',
			'text' => '配送中',
			'color' => 'color-warning'
		),
		'5' => array(
			'css' => 'label label-success',
			'text' => '配送成功',
			'color' => 'color-success'
		),
		'6' => array(
			'css' => 'label label-danger',
			'text' => '配送失败',
			'color' => 'color-danger'
		)
	);
	return $data;
}

//order_status_notice
function order_status_notice($sid, $id, $status, $note = '') {
	global $_W;
	$status_arr = array(
		'handel', //处理中
		'delivery_ing',
		'end', //已完成
		'cancel',//已取消
		'pay',//已支付
		'remind',
		'reply_remind',
	);
	if(!in_array($status, $status_arr)) {
		return false;
	}
	$type = $status;
	$store = store_fetch($sid, array('title'));
	$order = order_fetch($id);
	$acc = WeAccount::create($_W['acid']);
	$title = '订单进度通知';
	$from = $_W['account']['name'];
	if(!empty($order['openid'])) {
		if($type == 'pay') {
			$content = '订单已付款';
			$remark = array(
				"门店名称：{$store['title']}",
				"订单　号：{$order['ordersn']}",
				"支付状态：已付款",
				"支付方式：{$order['pay_type']}",
				"支付时间：" . date('Y-m-d H:i', time()),
			);
		}

		if($type == 'handel') {
			$content = '商家已接单,正在准备商品中...';
			$remark = array(
				"门店名称：{$store['title']}",
				"订单　号：{$order['ordersn']}",
				"订单状态：已确认",
				"处理时间：" . date('Y-m-d H:i', time()),
			);
		}

		if($type == 'delivery_ing') {
			$content = '您的订单正在为您配送中';
			$remark = array(
				"门店名称：{$store['title']}",
				"订单　号：{$order['ordersn']}",
				"订单状态：配送中",
			);
		}

		if($type == 'end') {
			$content = '订单处理完成';
			$remark = array(
				"门店名称：{$store['title']}",
				"订单　号：{$order['ordersn']}",
				"订单状态：已完成",
				"完成时间：" . date('Y-m-d H:i', time()),
			);
		}

		if($type == 'cancel') {
			$content = '订单已取消';
			$remark = array(
				"门店名称：{$store['title']}",
				"订单　号：{$order['ordersn']}",
				"订单状态：已取消",
				"取消时间：" . date('Y-m-d H:i', time()),
			);
		}

		if($type == 'reply_remind') {
			$content = '订单催单有新的回复';
			$remark = array(
				"门店名称：{$store['title']}",
				"订单　号：{$order['ordersn']}",
				"回复时间：" . date('Y-m-d H:i', time()),
			);
		}
		if(!empty($note)) {
			$remark[] = $note;
		}
		$url = $_W['siteroot'] . 'app' . ltrim(murl('entry', array('do' => 'myorder', 'm' => 'tiny_wmall', 'op' => 'detail', 'sid' => $order['sid'], 'id' => $order['id'])), '.');
		$remark = implode("\n", $remark);
		$send = tpl_format($title, $from, $content, $remark);
		$status = $acc->sendTplNotice($order['openid'], $_W['we7_wmall']['notice']['public_tpl'], $send, $url);
		return $status;
	}
	return true;
}

//order_clerk_notice
function order_clerk_notice($sid, $id, $type, $note = '') {
	global $_W;
	mload()->model('store');
	mload()->model('order');
	$store = store_fetch($sid, array('title', 'id'));
	$order = order_fetch($id);
	if(empty($store) || empty($order)) {
		return false;
	}
	mload()->model('clerk');
	$clerks = clerk_fetchall($sid);
	if(empty($clerks)) {
		return false;
	}
	$types_arr = array(
		'place_order' => array(
			'title' => '新订单通知',
		),
		'remind' => array(
			'title' => '催单通知',
		),
	);
	if(!in_array($type, array_keys($types_arr))) {
		return false;
	}
	$title = $types_arr[$type]['title'];
	$from = $_W['account']['name'];
	$acc = WeAccount::create($_W['acid']);
	if($type == 'place_order') {
		$content = '您有新的订单,订单号:' . $order['ordersn'];
		$remark = array(
			"门店名称：{$store['title']}",
			"下单时间：" . date('Y-m-d H:i', $order['addtime']),
			"总金　额：{$order['final_fee']}",
			"支付状态：{$order['pay_type']}",
			"下单　人：{$order['username']}",
			"联系手机：{$order['mobile']}",
			"送货地址：{$order['address']}",
		);
	}
	if($type == 'remind') {
		$content = '该订单有催单, 请请尽快回复';
		$remark = array(
			"门店名称：{$store['title']}",
			"订单　号：{$order['ordersn']}",
			"下单时间：" . date('Y-m-d H:i', $order['addtime']),
		);
	}
	$url = $_W['siteroot'] . 'app' . ltrim(murl('entry', array('do' => 'manage', 'm' => 'tiny_wmall', 'op' => 'detail', 'sid' => $order['sid'], 'id' => $order['id'])), '.');
	$remark = implode("\n", $remark);
	$send = tpl_format($title, $from, $content, $remark);
	foreach($clerks as $clerk) {
		$acc->sendTplNotice($clerk['openid'], $_W['we7_wmall']['notice']['public_tpl'], $send, $url);
	}
	return true;
}

function order_deliveryer_notice($sid, $id, $type, $deliveryer_id = 0, $note = '') {
	global $_W;
	mload()->model('store');
	mload()->model('order');
	$store = store_fetch($sid, array('title', 'id'));
	$order = order_fetch($id);
	if(empty($store) || empty($order)) {
		return false;
	}
	mload()->model('deliveryer');
	if($deliveryer_id > 0) {
		$deliveryer = deliveryer_fetch($deliveryer_id);
		$deliveryers[] = $deliveryer;
	} else {
		$deliveryers = deliveryer_fetchall($sid);
	}
	if(empty($deliveryers)) {
		return false;
	}
	$types_arr = array(
		'new_delivery' => array(
			'title' => '新配送订单通知',
		),
	);
	if(!in_array($type, array_keys($types_arr))) {
		return false;
	}
	$title = $types_arr[$type]['title'];
	$from = $_W['account']['name'];
	$acc = WeAccount::create($_W['acid']);
	if($type == 'new_delivery') {
		$content = '您有新的配送订单,订单号:' . $order['ordersn'];
		$remark = array(
			"门店名称：{$store['title']}",
			"下单时间：" . date('Y-m-d H:i', $order['addtime']),
			"总金　额： {$order['final_fee']}",
			"支付状态：{$order['pay_type']}",
			"下单　人：{$order['username']}",
			"联系手机：{$order['mobile']}",
			"送货地址：{$order['address']}",
		);
	}
	$url = $_W['siteroot'] . 'app' . ltrim(murl('entry', array('do' => 'deliveryer', 'm' => 'tiny_wmall', 'op' => 'detail', 'sid' => $order['sid'], 'id' => $order['id'])), '.');
	$remark = implode("\n", $remark);
	$send = tpl_format($title, $from, $content, $remark);
	foreach($deliveryers as $deliveryer) {
		$acc->sendTplNotice($deliveryer['openid'], $_W['we7_wmall']['notice']['public_tpl'], $send, $url);
	}
	return true;
}

//pay_types
function order_pay_types() {
	$pay_types = array(
		'' => '未支付',
		'alipay' => array(
			'css' => 'label label-info',
			'text' => '支付宝',
		),
		'wechat' => array(
			'css' => 'label label-success',
			'text' => '微信支付',
		),
		'credit' => array(
			'css' => 'label label-warning',
			'text' => '余额支付',
		),
		'delivery' => array(
			'css' => 'label label-primary',
			'text' => '餐到付款',
		),
		'cash' => array(
			'css' => 'label label-primary',
			'text' => '现金支付',
		)
	);
	return $pay_types;
}

// order_insert_member_cart
function order_insert_member_cart($sid) {
	global $_W, $_GPC;
	if(!empty($_GPC['goods'])) {
		$num = 0;
		$price = 0;
		$ids_str = implode(',', array_keys($_GPC['goods']));
		$goods_info = pdo_fetchall('SELECT * FROM ' . tablename('tiny_wmall_goods') ." WHERE uniacid = :aid AND sid = :sid AND id IN ($ids_str)", array(':aid' => $_W['uniacid'], ':sid' => $sid), 'id');
		foreach($_GPC['goods'] as $k => $v) {
			$k = intval($k);
			if(!$goods_info[$k]['is_options']) {
				$v = intval($v['options'][0]);
				if($v > 0) {
					$goods[$k][0] = array(
						'title' => $goods_info[$k]['title'],
						'num' => $v,
						'price' => $goods_info[$k]['price'],
					);
					$num += $v;
					$price += $goods_info[$k]['price'] * $v;
				}
			} else {
				foreach($v['options'] as $key => $val) {
					$key = intval($key);
					$val = intval($val);
					if($key > 0 && $val > 0) {
						$option = pdo_get('tiny_wmall_goods_options', array('uniacid' => $_W['uniacid'], 'id' => $key));
						if(empty($option)) {
							continue;
						}
						$goods[$k][$key] = array(
							'title' => $goods_info[$k]['title'] . "({$option['name']})",
							'num' => $val,
							'price' => $option['price'],
						);
						$num += $val;
						$price += $option['price'] * $val;
					}
				}
			}
		}

		$isexist = pdo_fetchcolumn('SELECT id FROM ' . tablename('tiny_wmall_order_cart') . " WHERE uniacid = :aid AND sid = :sid AND uid = :uid", array(':aid' => $_W['uniacid'], ':sid' => $sid, ':uid' => $_W['member']['uid']));
		$data = array(
			'uniacid' => $_W['uniacid'],
			'sid' => $sid,
			'uid' => $_W['member']['uid'],
			'groupid' => $_W['member']['groupid'],
			'num' => $num,
			'price' => $price,
			'data' => iserializer($goods),
			'addtime' => TIMESTAMP,
		);
		if(empty($isexist)) {
			pdo_insert('tiny_wmall_order_cart', $data);
		} else {
			pdo_update('tiny_wmall_order_cart', $data, array('uniacid' => $_W['uniacid'], 'id' => $isexist, 'uid' => $_W['member']['uid']));
		}
		$data['data'] = $goods;
		return $data;
	} else {
		return error(-1, '商品信息错误');
	}
	return true;
}

//order_fetch_member_cart
function order_fetch_member_cart($sid) {
	global $_W, $_GPC;
	$cart = pdo_fetch('SELECT * FROM ' . tablename('tiny_wmall_order_cart') . " WHERE uniacid = :aid AND sid = :sid AND uid = :uid", array(':aid' => $_W['uniacid'], ':sid' => $sid, ':uid' => $_W['member']['uid']));
	if(empty($cart)) {
		return false;
	}
	if((TIMESTAMP - $cart['addtime']) > 7*86400) {
		pdo_delete('tiny_wmall_order_cart', array('id' => $cart['id']));
	}
	$cart['data'] = iunserializer($cart['data']);
	return $cart;
}

//order_del_member_cart
function order_del_member_cart($sid) {
	global $_W;
	pdo_delete('tiny_wmall_order_cart', array('sid' => $sid, 'uid' => $_W['member']['uid']));
	return true;
}

//order_order_update_goods_info
function order_update_goods_info($order_id, $sid) {
	global $_W;
	$cart = order_fetch_member_cart($sid);
	if(empty($cart['data'])) {
		return false;
	}
	$ids_str = implode(',', array_keys($cart['data']));
	$goods_info = pdo_fetchall('SELECT id,title,price,total FROM ' . tablename('tiny_wmall_goods') ." WHERE uniacid = :aid AND sid = :sid AND id IN ($ids_str)", array(':aid' => $_W['uniacid'], ':sid' => $sid), 'id');
	foreach($cart['data'] as $k => $v) {
		foreach($v as $k1 => $v1) {
			pdo_query('UPDATE ' . tablename('tiny_wmall_goods') . " set sailed = sailed + {$v1['num']} WHERE uniacid = :aid AND id = :id", array(':aid' => $_W['uniacid'], ':id' => $k));
			if(!$k1) {
				if($goods_info[$k]['total'] != -1 && $goods_info[$k]['total'] > 0) {
					pdo_query('UPDATE ' . tablename('tiny_wmall_goods') . " set total = total - {$v1['num']} WHERE uniacid = :aid AND id = :id", array(':aid' => $_W['uniacid'], ':id' => $k));
				}
			} else {
				$option = pdo_get('tiny_wmall_goods_options', array('uniacid' => $_W['uniacid'], 'id' => $k1));
				if(!empty($option) && $option['total'] != -1 && $option['total'] > 0) {
					pdo_query('UPDATE ' . tablename('tiny_wmall_goods') . " set total = total - {$v1['num']} WHERE uniacid = :aid AND id = :id", array(':aid' => $_W['uniacid'], ':id' => $k1));
				}
			}
			$stat = array();
			if($k && $v1) {
				$stat['oid'] = $order_id;
				$stat['uniacid'] = $_W['uniacid'];
				$stat['sid'] = $sid;
				$stat['goods_id'] = $k;
				$stat['goods_num'] = $v1['num'];
				$stat['goods_title'] = $v1['title'];
				$stat['goods_price'] = $v1['num'] * $v1['price'];
				$stat['addtime'] = TIMESTAMP;
				pdo_insert('tiny_wmall_order_stat', $stat);
			}
		}
	}
	pdo_query('UPDATE ' . tablename('tiny_wmall_store') . " set sailed = sailed + {$cart['num']} WHERE uniacid = :uniacid AND id = :id", array(':uniacid' => $_W['uniacid'], ':id' => $cart['sid']));
	return true;
}

//
function order_stat_member($sid) {
	global $_W;
	$is_exist = pdo_get('tiny_wmall_store_members', array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'uid' => $_W['member']['uid']));
	if(empty($is_exist)) {
		$insert = array(
			'uniacid' => $_W['uniacid'],
			'sid' => $sid,
			'uid' => $_W['member']['uid'],
			'openid' => $_W['openid'],
			'nickname' => $_W['fans']['nickname'],
			'avatar' => $_W['fans']['avatar'],
			'realname' => $_W['member']['realname'],
			'mobile' => $_W['member']['mobile'],
			'first_order_time' => TIMESTAMP,
			'last_order_time' => TIMESTAMP,
		);
		pdo_insert('tiny_wmall_store_members', $insert);
	} else {
		$update = array(
			'uid' => $_W['member']['uid'],
			'realname' => $_W['member']['realname'],
			'mobile' => $_W['member']['mobile'],
			'last_order_time' => TIMESTAMP,
		);
		if(!empty($_W['fans'])) {
			$update['openid'] = $_W['openid'];
			$update['nickname'] = $_W['fans']['nickname'];
			$update['avatar'] = $_W['fans']['avatar'];
		}
		pdo_update('tiny_wmall_store_members', $update, array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'uid' => $_W['member']['uid']));
	}
	$is_exist = pdo_get('tiny_wmall_members', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid']));
	if(empty($is_exist)) {
		$insert = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $_W['member']['uid'],
			'openid' => $_W['openid'],
			'realname' => $_W['member']['realname'],
			'mobile' => $_W['member']['mobile'],
			'first_order_time' => TIMESTAMP,
			'last_order_time' => TIMESTAMP,
		);
		pdo_insert('tiny_wmall_members', $insert);
	} else {
		$update = array(
			'uid' => $_W['member']['uid'],
			'realname' => $_W['member']['realname'],
			'mobile' => $_W['member']['mobile'],
			'last_order_time' => TIMESTAMP,
		);
		if(!empty($_W['fans'])) {
			$update['openid'] = $_W['openid'];
			$update['nickname'] = $_W['fans']['nickname'];
			$update['avatar'] = $_W['fans']['avatar'];
		}
		pdo_update('tiny_wmall_members', $update, array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid']));
	}
	return false;
}

function order_amount_stat($sid) {
	global $_W;
	$stat = array();
	$today_starttime = strtotime(date('Y-m-d'));
	$month_starttime = strtotime(date('Y-m'));
	$stat['today_total'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('tiny_wmall_order') . ' where uniacid = :uniacid and sid = :sid and status = 5 and is_pay = 1 and addtime >= :starttime', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':starttime' => $today_starttime)));
	$stat['today_price'] = floatval(pdo_fetchcolumn('select sum(final_fee) from ' . tablename('tiny_wmall_order') . ' where uniacid = :uniacid and sid = :sid and status = 5 and is_pay = 1 and addtime >= :starttime', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':starttime' => $today_starttime)));
	$stat['month_total'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('tiny_wmall_order') . ' where uniacid = :uniacid and sid = :sid and status = 5 and is_pay = 1 and addtime >= :starttime', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':starttime' => $month_starttime)));
	$stat['month_price'] = floatval(pdo_fetchcolumn('select sum(final_fee) from ' . tablename('tiny_wmall_order') . ' where uniacid = :uniacid and sid = :sid and status = 5 and is_pay = 1 and addtime >= :starttime', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':starttime' => $month_starttime)));
	return $stat;
}

function order_avtivitys() {
	$data = array(
		'first_order' => array(
			'text' => '新用户优惠',
			'icon_b' => 'xin_b.png',
		),
		'discount' => array(
			'text' => '满减优惠',
			'icon_b' => 'jian_b.png',
		),
		'grant' => array(
			'text' => '满赠优惠',
			'icon_b' => 'zeng_b.png',
		),
	);
	return $data;
}

function order_count_activity($sid, $price) {
	global $_W;
	$activityed = array('list' => '', 'total' => 0);
	$activity = store_fetch_activity($sid);
	if(!empty($activity)) {
		if(!empty($activity['first_order_status'])) {
			$is_first = pdo_get('tiny_wmall_store_members', array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'uid' => $_W['member']['uid']));
			if(empty($is_first)) {
				$discount = array_compare($price, $activity['first_order_data']);
				if(!empty($discount)) {
					$activityed['list']['first_order'] = array('text' => "-￥{$discount['back']}", 'value' => $discount['back'], 'type' => 'first_order', 'name' => '新用户优惠', 'icon' => 'xin_b.png');
					$activityed['total'] += $discount['back'];
				}
			}
		}
		if(empty($activityed['first_order']) && !empty($activity['discount_status'])) {
			$discount = array_compare($price, $activity['discount_data']);
			if(!empty($discount)) {
				$activityed['list']['discount'] = array('text' => "-￥{$discount['back']}", 'value' => $discount['back'], 'type' => 'discount', 'name' => '满减优惠', 'icon' => 'jian_b.png');
				$activityed['total'] += $discount['back'];
			}
		}
		if(!empty($activity['grant_status'])) {
			$discount = array_compare($price, $activity['grant_data']);
			if(!empty($discount)) {
				$activityed['list']['grant'] = array('text' => "{$discount['back']}", 'value' => 0, 'type' => 'grant', 'name' => '满赠优惠', 'icon' => 'zeng_b.png');
				$activityed['total'] += 0;
			}
		}
	}
	return $activityed;
}

function order_check_payment($sid) {
	global $_W;
	$setting = uni_setting($_W['uniacid'], array('payment'));
	$pay = $setting['payment'];
	if(empty($pay)) {
		return error(-1, '公众号没有设置支付方式,请先设置支付方式');
	}
	if(!empty($pay['credit']['switch'])) {
		$dos[] = 'credit';
	}
	if(!empty($pay['alipay']['switch'])) {
		$dos[] = 'alipay';
	}
	if(!empty($pay['wechat']['switch'])) {
		$dos[] = 'wechat';
	}
	if(!empty($pay['delivery']['switch'])) {
		$dos[] = 'delivery';
	}
	if(!empty($pay['unionpay']['switch'])) {
		$dos[] = 'unionpay';
	}
	if(!empty($pay['baifubao']['switch'])) {
		$dos[] = 'baifubao';
	}
	if(empty($dos)) {
		return error(-1, '公众号没有设置支付方式,请先设置支付方式');
	}
	//支付方式
	if(empty($store['payment'])) {
		message('店铺没有设置有效的支付方式', referer(), 'error');
	}
	return false;
}





