<?php
/**
 * 超级外卖模块微站定义
 * @author strday
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$_W['page']['title'] = '订单列表-' . $_W['wmall']['module']['name'];
mload()->model('store');
mload()->model('order');
mload()->model('deliveryer');

$store = store_check();
$sid = $store['id'];
$do = 'order';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';

if($op == 'list') {
	$condition = ' WHERE uniacid = :aid AND sid = :sid';
	$params[':aid'] = $_W['uniacid'];
	$params[':sid'] = $sid;

	$status = intval($_GPC['status']);
	if($status > 0) {
		$condition .= ' AND status = :stu';
		$params[':stu'] = $status;
	}
	$is_pay = isset($_GPC['is_pay']) ? intval($_GPC['is_pay']) : -1;
	if($is_pay > 0) {
		$condition .= ' AND is_pay = :is_pay';
		$params[':is_pay'] = $is_pay;
	}
	$keyword = trim($_GPC['keyword']);
	if(!empty($keyword)) {
		$condition .= " AND (username LIKE '%{$keyword}%' OR mobile LIKE '%{$keyword}%')";
	}
	if(!empty($_GPC['addtime'])) {
		$starttime = strtotime($_GPC['addtime']['start']);
		$endtime = strtotime($_GPC['addtime']['end']) + 86399;
	} else {
		$starttime = strtotime('-15 day');
		$endtime = TIMESTAMP;
	}
	$condition .= " AND addtime > :start AND addtime < :end";
	$params[':start'] = $starttime;
	$params[':end'] = $endtime;

	$pindex = max(1, intval($_GPC['page']));
	$psize = 15;

	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('tiny_wmall_order') .  $condition, $params);
	$data = pdo_fetchall('SELECT * FROM ' . tablename('tiny_wmall_order') . $condition . ' ORDER BY addtime DESC LIMIT '.($pindex - 1) * $psize.','.$psize, $params);

	$pager = pagination($total, $pindex, $psize);
	$pay_types = order_pay_types();
	$order_status = order_status();
	$store_ = store_fetch($sid, array('remind_reply'));
	$deliveryers = deliveryer_fetchall($sid);
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
	message('更新订状态成功', referer(), 'success');
} 

if($op == 'detail') {
	$id = intval($_GPC['id']);
	$order = order_fetch($id);
	if(empty($order)) {
		message('订单不存在或已经删除', $this->createWebUrl('manage', array('op' => 'order')), 'error');
	} 
	$order['goods'] = order_fetch_goods($order['id']);
	if($order['is_comment'] == 1) {
		$comment = pdo_fetch('SELECT * FROM ' . tablename('tiny_wmall_order_comment') .' WHERE uniacid = :aid AND oid = :oid', array(':aid' => $_W['uniacid'], ':oid' => $id));
		if(!empty($comment)) {
			$comment['data'] = iunserializer($comment['data']);
		}
	}
	if($order['discount_fee'] > 0) {
		$discount = order_fetch_discount($id);
	}
	$pay_types = order_pay_types();
	$order_status = order_status();
	$logs = order_fetch_status_log($id);
} 

if($op == 'del') {
	$id = intval($_GPC['id']);
	pdo_delete('tiny_wmall_order', array('uniacid' => $_W['uniacid'], 'id' => $id));
	pdo_delete('tiny_wmall_order_stat', array('uniacid' => $_W['uniacid'], 'oid' => $id));
	pdo_delete('tiny_wmall_order_comment', array('uniacid' => $_W['uniacid'], 'oid' => $id));
	message('删除订单成功', $this->createWebUrl('order', array('op' => 'list')), 'success');
}

if($op == 'print') {
	$id = intval($_GPC['id']);
	$status = order_print($id, true);
	if(is_error($status)) {
		exit($status['message']);
	}
	exit('success');
}

if($op == 'reply_remind') {
	if(!$_W['isajax']) {
		return false;
	}
	$id = intval($_GPC['id']);
	$order = order_fetch($id);
	if(empty($order)) {
		message(error(-1, '订单不存在或已经删除'), '', 'ajax');
	}
	$content = trim($_GPC['content']);
	pdo_update('tiny_wmall_order', array('is_remind' => 0), array('uniacid' => $_W['uniacid'], 'id' => $id));
	order_insert_status_log($id, $order['sid'], 'remind_reply', $content);
	order_status_notice($order['sid'], $id, 'reply_remind', "回复内容：" . $content);
	message(error(0, ''), '', 'ajax');
}

if($op == 'set_deliveryer') {
	if(!$_W['isajax']) {
		return false;
	}
	$deliveryer_id = intval($_GPC['deliveryer_id']);
	$deliveryer = pdo_get('tiny_wmall_deliveryer', array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'id' => $deliveryer_id));
	if(empty($deliveryer)) {
		message(error(-1, '没有找到对应的配送员'), '', 'ajax');
	}
	$order_ids = $_GPC['order_ids'];
	foreach($order_ids as $id) {
		$id = intval($id);
		if(!$id) continue;
		pdo_update('tiny_wmall_order', array('deliveryer_id' => $deliveryer_id, 'status' => '4'), array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'id' => $id));
		$content = "配送员:{$deliveryer['title']}, 手机号:{$deliveryer['mobile']}";
		order_insert_status_log($id, $sid, 'delivery_ing', $content);
		order_status_notice($sid, $id, 'delivery_ing', "配送　员：{$deliveryer['title']}\n手机　号：{$deliveryer['mobile']}");
		order_deliveryer_notice($sid, $id, 'new_delivery', $deliveryer_id);
		message(error(0, ''), '', 'ajax');
	}
}
include $this->template('store/order');