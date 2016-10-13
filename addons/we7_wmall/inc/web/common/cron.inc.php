<?php
/**
 * 超级外卖模块微站定义
 * @author strday
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$_W['page']['title'] = '计划任务-' . $_W['wmall']['module']['name'];
$store = checkstore();
$sid = $store['id'];
$do = 'cron';
$op = trim($_GPC['op']);

if($op == 'order_notice') {
	$order = pdo_fetch('SELECT id FROM ' . tablename('tiny_wmall_order') . ' WHERE uniacid = :uniacid AND sid = :sid AND is_notice = 0 ORDER BY id asc', array(':uniacid' => $_W['uniacid'], ':sid' => $sid));
	if(!empty($order)) {
		pdo_update('tiny_wmall_order', array('is_notice' => 1), array('uniacid' => $_W['uniacid'], 'id' => $order['id']));
		exit('success');
	}
	exit('error');
}

if($op == 'order_cancel') {
	init_cron();
}

if($op == 'order_print') {
	mload()->model('print');
	$data = pdo_fetchall('SELECT a.foid, b.print_no, b.key FROM ' . tablename('tiny_wmall_order_print_log') . ' AS a LEFT JOIN '.tablename('tiny_wmall_printer').' AS b ON a.pid = b.id WHERE a.uniacid = :aid AND a.sid = :sid AND a.status = 2 AND a.printer_type = 1 ORDER BY addtime ASC LIMIT 10', array(':aid' => $_W['uniacid'], ':sid' => $sid));
	if(!empty($data)) {
		foreach($data as $da) {
			if(!empty($da['foid']) && !empty($da['print_no']) && !empty($da['key'])) {
				$status = print_query_order_status($da['type'], $da['print_no'], $da['key'], $da['member_code'], $da['foid']);
				if(!is_error($status)) {
					pdo_update('tiny_wmall_order_print_log', array('status' => $status), array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'foid' => $da['foid']));
				}
			}
		}
	}
}





