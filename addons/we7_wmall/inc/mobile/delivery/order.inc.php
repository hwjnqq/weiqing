<?php
/**
 * 微外卖模块微站定义
 * @author 微擎团队&灯火阑珊
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$do = 'dyorder';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
mload()->model('order');
mload()->model('deliveryer');
$deliveryer = checkdeliveryer();
$sid = $deliveryer['sid'];
$title = '订单管理';

if($op == 'list') {
	$condition = ' WHERE uniacid = :uniacid and sid = :sid';
	$params[':uniacid'] = $_W['uniacid'];
	$params[':sid'] = $sid;
	$status = isset($_GPC['status']) ? intval($_GPC['status']) : 3;
	$condition .= ' and delivery_status = :status';
	$params[':status'] = $status;
	if($status != 3) {
		$condition .= ' and deliveryer_id = :deliveryer_id';
		$params[':deliveryer_id'] = $deliveryer['id'];
		$condition .= ' order by id desc limit 10';
	}
	$orders = pdo_fetchall('SELECT id, addtime, status, username, mobile, address, delivery_status,delivery_time,sid, num, final_fee FROM ' . tablename('tiny_wmall_order') . $condition, $params, 'id');
	$min = 0;
	if(!empty($orders)) {
		$stores_id = array();
		foreach($orders as $da) {
			$stores_id[] = $da['sid'];
		}
		$stores_str = implode(',', array_unique($stores_id));
		$stores = pdo_fetchall('select id, title, address from ' . tablename('tiny_wmall_store') . " where uniacid = :uniacid and id in ({$stores_str})", array(':uniacid' => $_W['uniacid']), 'id');
		$min = min(array_keys($orders));
	}
	$delivery_status = order_delivery_status();
}

if($op == 'more') {
	$id = intval($_GPC['id']);
	$orders = pdo_fetchall('select * from ' . tablename('tiny_wmall_order') . ' where uniacid = :uniacid and sid = :sid and id < :id order by id desc limit 20', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':id' => $id), 'id');
	$min = 0;
	if(!empty($orders)) {
		$delivery_status = order_delivery_status();
		foreach ($orders as &$row) {
			$row['addtime_cn'] = date('Y-m-d H:i:s', $row['addtime']);
			$row['status_color'] = $delivery_status[$row['delivery_status']]['color'];
			$row['status_cn'] = $delivery_status[$row['delivery_status']]['text'];
			$row['store_address'] = pdo_fetchcolumn('select address from ' . tablename('tiny_wmall_store') . ' where uniacid = :uniacid and id = :sid', array(':uniacid' => $_W['uniacid'], ':sid' => $row['sid']));
		}
		$min = min(array_keys($orders));
	}
	$orders = array_values($orders);
	$respon = array('error' => 0, 'message' => $orders, 'min' => $min);
	message($respon, '', 'ajax');
}

if($op == 'delivery_status') {
	$id = intval($_GPC['id']);
	$status = intval($_GPC['status']);
	$order = pdo_get('tiny_wmall_order', array('uniacid' => $_W['uniacid'], 'id' => $id));
	if(empty($order)) {
		message(error(-1, '订单不存在或已经删除'), '', 'error');
	}
	if($status == 4) {
		if($order['delivery_id'] > 0) {
			message(error(-1, '该订单已被别人接单'), '', 'error');
		}
		//配送员抢单
		pdo_update('tiny_wmall_order', array('status' => $status, 'delivery_status' => $status, 'deliveryer_id' => $deliveryer_id), array('uniacid' => $_W['uniacid'], 'id' => $id));
		$content = "配送员:{$deliveryer['title']}, 手机号:{$deliveryer['mobile']}";
		order_insert_status_log($id, $sid, 'delivery_ing', $content);
		order_status_notice($sid, $id, 'delivery_ing', "配送　员：{$deliveryer['title']}\n手机　号：{$deliveryer['mobile']}");
		message(error(0, '抢单成功'), '', 'ajax');
	}

	if($status == 5 || $status == 6) {
		if($order['delivery_id'] > 0 && $order['delivery_id'] != $deliveryer['id']) {
			message(error(-1, '该订单不是由您配送,不能变更状态'), '', 'error');
		}
		pdo_update('tiny_wmall_order', array('status' => $status, 'delivery_status' => $status, 'deliveryedtime' => TIMESTAMP), array('uniacid' => $_W['uniacid'], 'id' => $id));
		$content = "配送员:{$deliveryer['title']}, 手机号:{$deliveryer['mobile']}";
		if($status == 5) {
			order_insert_status_log($id, $sid, 'delivery_success', $content);
		} else {
			order_insert_status_log($id, $sid, 'delivery_fail', $content);
		}
		message(error(0, '变更配送状态成功'), '', 'ajax');
	}

}

include $this->template('delivery/order');