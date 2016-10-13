<?php
/**
 * 微外卖模块微站定义
 * @author 微擎团队&灯火阑珊
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$do = 'mggoods';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
mload()->model('manage');
checkstore();
$sid = intval($_GPC['__mg_sid']);
$title = '商品管理';

if($op == 'list') {
	$condition = ' WHERE uniacid = :aid AND sid = :sid';
	$params[':aid'] = $_W['uniacid'];
	$params[':sid'] = $sid;

	$status = isset($_GPC['status']) ? intval($_GPC['status']) : -1;
	if($status >= 0) {
		$condition .= ' AND status = :status';
		$params[':status'] = $status;
	}
	$goods = pdo_fetchall('SELECT * FROM ' . tablename('tiny_wmall_goods') . $condition . ' order by displayorder desc, id asc', $params);
}

if($op == 'status') {
	$id = intval($_GPC['id']);
	$value = intval($_GPC['value']);
	pdo_update('tiny_wmall_goods', array('status' => $value), array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'id' => $id));
	message(error(0, ''), '', 'ajax');
}

if($op == 'del') {
	$id = intval($_GPC['id']);
	pdo_delete('tiny_wmall_goods',array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'id' => $id));
	pdo_delete('tiny_wmall_goods_options',array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'goods_id' => $id));
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
			pdo_update('tiny_wmall_order', array('status' => $status), array('uniacid' => $_W['uniacid'], 'id' => $id));
		}
		order_insert_status_log($id, $sid, $type);
		$status = order_status_notice($sid, $id, $type);
	}
	message(error(0, ''), '', 'ajax');
}



include $this->template('manage/goods');