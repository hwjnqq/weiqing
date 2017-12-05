<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 更改消息提醒状态
 * @param $id
 * @return bool
 */
function message_notice_read($id) {
	$id = intval($id);
	if (empty($id)) {
		return true;
	}
	pdo_update('message_notice_log', array('is_read' => MESSAGE_READ), array('id' => $id));
	return true;
}


/**
 * 消息提醒记录
 * @param array $message_notice_log
 * @return bool
 */
function message_record($content, $uid, $sign, $type, $extend_message = array()) {
	$message['message'] = $content;
	$message['uid'] = $uid;
	$message['sign'] = $sign;
	$message['type'] = $type;
	$message['create_time'] = TIMESTAMP;
	$message_notice_log = array_merge($message, $extend_message);
	pdo_insert('message_notice_log', $message_notice_log);
	return true;
}

/**
 * frame  顶部消息提醒获取
 * @return array
 */
function message_notice() {
	global $_W;
	$message_table = table('message');
	$message_table->searchWithIsRead(MESSAGE_NOREAD);
	if (user_is_founder($_W['uid']) && !user_is_vice_founder($_W['uid'])) {
		$type = array(MESSAGE_ORDER_TYPE, MESSAGE_ACCOUNT_EXPIRE_TYPE, MESSAGE_REGISTER_TYPE, MESSAGE_WECHAT_EXPIRE_TYPE);
	} else {
		$type = MESSAGE_ACCOUNT_EXPIRE_TYPE;
	}
	$message_table->searchWithType($type);
	$message_table->searchWithPage(1, 10);
	$lists = $message_table->messageList();

	$message_table->searchWithIsRead(MESSAGE_NOREAD);
	$message_table->searchWithType($type);
	$total = $message_table->messageNoReadCount();
	return array(
		'lists' => $lists,
		'total' => $total
	);
}

/**
 * 消息内容格式化
 * @param $message_list
 * @return array
 */
function message_record_formate ($message_list) {
	if (empty($message_list)) {
		return array();
	}

	foreach ($message_list as &$message) {
		$message['create_time'] = date('Y-m-d H:i:s', $message['create_time']);

		if ($message['type'] == MESSAGE_ORDER_TYPE) {
			$message['url'] = url('site/entry/orders', array('m' => 'store', 'direct'=>1, 'message_id' => $message['id']));
		}
		if ($message['type'] == MESSAGE_ACCOUNT_EXPIRE_TYPE) {
			$message['url'] = url('account/manage', array('account_type' => ACCOUNT_TYPE_OFFCIAL_NORMAL, 'message_id' => $message['id']));
		}
		if ($message['type'] == MESSAGE_WECHAT_EXPIRE_TYPE) {
			$message['url'] = url('account/manage', array('account_type' => ACCOUNT_TYPE_APP_NORMAL, 'message_id' => $message['id']));
		}

		if ($message['type']==MESSAGE_REGISTER_TYPE && $message['status']==USER_STATUS_CHECK) {
			$message['url'] = url('user/display', array('type' => 'check', 'message_id' => $message['id']));
		}

		if ($message['type']==MESSAGE_REGISTER_TYPE && $message['status']==USER_STATUS_CHECK) {
			$message['url'] = url('user/display', array('message_id' => $message['id']));
		}
	}
	return $message_list;
}