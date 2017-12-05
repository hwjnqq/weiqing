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
function message_notice_record($content, $uid, $sign, $type, $extend_message = array()) {
	$message['message'] = $content;
	$message['uid'] = $uid;
	$message['sign'] = $sign;
	$message['type'] = $type;
	$message_notice_log = array_merge($message, $extend_message);
	$message_exists = message_validate_exists($message_notice_log);
	if (!empty($message_exists)) {
		return true;
	}
	$message_notice_log['create_time'] = TIMESTAMP;
	pdo_insert('message_notice_log', $message_notice_log);
	return true;
}

/**
 * 检测消息记录是否已经插入数据库
 */
function message_validate_exists($message) {
	$message_exists = pdo_get('message_notice_log', $message);
	if (!empty($message_exists)) {
		return true;
	}
	return false;
}


/**
 * frame  栏目小红点消息提醒获取
 * @return array
 */
function message_event_notice_list() {
	load()->model('user');
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
	if (!empty($lists)) {
		foreach ($lists as &$message) {
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
	}
	return array(
		'lists' => $lists,
		'total' => $total
	);
}


/**
 * 公众号过期记录
 * @return bool
 */
function message_account_expire() {
	load()->model('account');
	load()->model('message');
	if (!pdo_tableexists('message_notice_log')) {
		return true;
	}
	$account_table = table('account');
	$expire_account_list = $account_table->searchAccountList();
	if (empty($expire_account_list)) {
		return true;
	}
	foreach ($expire_account_list as $account) {
		$account_detail = uni_fetch($account['uniacid']);
		if (empty($account_detail['uid'])) {
			continue;
		}
		if ($account_detail['endtime'] > 0 && $account_detail['endtime'] < TIMESTAMP) {
			$type = $account_detail['type'] == ACCOUNT_TYPE_APP_NORMAL ? MESSAGE_WECHAT_EXPIRE_TYPE : MESSAGE_ACCOUNT_EXPIRE_TYPE;
			$account_name = $account_detail['type'] == ACCOUNT_TYPE_APP_NORMAL ? '-小程序过期' : '-公众号过期';
			$message = array(
				'end_time' => $account_detail['endtime']
			);
			message_notice_record($account_detail['name'] . $account_name, $account_detail['uid'], $account_detail['uniacid'], $type, $message);
		}
	}
	return true;
}