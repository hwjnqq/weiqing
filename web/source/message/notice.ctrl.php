<?php
/**
 * 消息提醒功能
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

$dos = array('display', 'change_read_status');
$do = in_array($do, $dos) ? $do : 'display';

$_W['page']['title'] = '系统管理 - 消息提醒 - 消息提醒';

if ($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;

	$type = !empty($_GPC['type']) ? intval($_GPC['type']) :
			(IMS_FAMILY == 'x' && user_is_founder($_W['uid']) && !user_is_vice_founder() ? MESSAGE_ORDER_TYPE : MESSAGE_ACCOUNT_EXPIRE_TYPE);
	$is_read = !empty($_GPC['is_read']) ? trim($_GPC['is_read']) : '';

	$message_table = table('message');

	if (!empty($is_read)) {
		$message_table->searchWithIsRead($is_read);
	}

	$message_table->searchWithType($type);
	$message_table->searchWithPage($pindex, $psize);
	$lists = $message_table->messageList();

	if (!empty($lists)) {
		foreach($lists as &$list) {
			$list['create_time'] = date('Y-m-d H:i:s', $list['create_time']);
		}
	}
	$total = $message_table->getLastQueryTotal();
	$pager = pagination($total, $pindex, $psize);
}

if ($do == 'change_read_status') {
	$id = $_GPC['id'];
	pdo_update('message_notice_log', array('is_read' => MESSAGE_READ), array('id' => $id));
	iajax(0, '成功');
}
template('message/notice');