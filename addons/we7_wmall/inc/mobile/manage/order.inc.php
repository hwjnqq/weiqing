<?php
/**
 * 微外卖模块微站定义
 * @author 微擎团队&灯火阑珊
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$do = 'mgorder';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
mload()->model('manage');
mload()->model('order');
mload()->model('deliveryer');
checkstore();
$sid = intval($_GPC['__mg_sid']);
$title = '订单管理';

if($op == 'list') {
	$condition = ' WHERE uniacid = :aid AND sid = :sid';
	$params[':aid'] = $_W['uniacid'];
	$params[':sid'] = $sid;

	$status = isset($_GPC['status']) ? intval($_GPC['status']) : 1;
	if($status > 0) {
		$condition .= ' AND status = :stu';
		$params[':stu'] = $status;
	}
	$orders = pdo_fetchall('SELECT * FROM ' . tablename('tiny_wmall_order') . $condition . ' order by id desc limit 5', $params, 'id');
	$min = 0;
	if(!empty($orders)) {
		foreach($orders as &$da) {
			$da['goods'] = order_fetch_goods($da['id']);
		}
		$min = min(array_keys($orders));
	}
	$order_status = order_status();
	$pay_types = order_pay_types();
	$deliveryers = deliveryer_fetchall($sid);
}

if($op == 'more') {
	$pay_types = order_pay_types();
	$order_status = order_status();
	$id = intval($_GPC['id']);
	$orders = pdo_fetchall('select * from ' . tablename('tiny_wmall_order') . ' where uniacid = :uniacid and sid = :sid and id < :id order by id desc limit 5', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':id' => $id), 'id');
	$min = 0;
	if(!empty($orders)) {
		foreach ($orders as &$row) {
			$row['goods'] =  order_fetch_goods($row['id']);
			$row['addtime_cn'] = date('Y-m-d H:i:s', $row['addtime']);
			$row['status_color'] = $order_status[$row['status']]['color'];
			$row['status_cn'] = $order_status[$row['status']]['text'];
		}
		$min = min(array_keys($orders));
	}
	$orders = array_values($orders);
	$respon = array('error' => 0, 'message' => $orders, 'min' => $min);
	message($respon, '', 'ajax');
}

if($op == 'print') {
	$id = intval($_GPC['id']);
	$status = order_print($id, true);
	if(is_error($status)) {
		message($status, '', 'ajax');
	}
	message(error(0, ''), '', 'ajax');
}

if($op == 'status') {
	$ids = $_GPC['id'];
	if(!is_array($ids)) {
		$ids = array($ids);
	}
	$status = intval($_GPC['status']);
	$type = trim($_GPC['type']);
	foreach($ids as $id) {
		$id = intval($id);
		if($id <= 0) continue;
		if($status == 7) {
			pdo_update('tiny_wmall_order', array('pay_type' => 'cash', 'is_pay' => 1), array('uniacid' => $_W['uniacid'], 'id' => $id));
		} else {
			pdo_update('tiny_wmall_order', array('status' => $status, 'delivery_status' => $status), array('uniacid' => $_W['uniacid'], 'id' => $id));
		}
		if($status > 2 && $status != 6) {
			pdo_update('tiny_wmall_order_stat', array('status' => 1), array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'oid' => $id));
		}
		order_insert_status_log($id, $sid, $type);
		order_status_notice($sid, $id, $type);
		if($status == '3') {
			order_deliveryer_notice($sid, $id, $type);
		}
	}
	message(error(0, ''), '', 'ajax');
}

if($op == 'deliveryer') {
	$deliveryer_id = intval($_GPC['deliveryer_id']);
	$deliveryer = pdo_get('tiny_wmall_deliveryer', array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'id' => $deliveryer_id));
	if(empty($deliveryer)) {
		message(error(-1, '没有找到对应的配送员'), '', 'ajax');
	}
	$id = intval($_GPC['id']);
	pdo_update('tiny_wmall_order', array('deliveryer_id' => $deliveryer_id, 'status' => '4'), array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'id' => $id));
	$content = "配送员:{$deliveryer['title']}, 手机号:{$deliveryer['mobile']}";
	order_insert_status_log($id, $sid, 'delivery_ing', $content);
	order_status_notice($sid, $id, 'delivery_ing', "配送员：{$deliveryer['title']}, 手机号：{$deliveryer['mobile']}");
	order_status_notice($sid, $id, 'delivery_ing', "配送　员:{$deliveryer['title']}\n手机　号：{$deliveryer['mobile']}");
	order_deliveryer_notice($sid, $id, 'new_delivery', $deliveryer_id);
	message(error(0, ''), '', 'ajax');
}


include $this->template('manage/order');