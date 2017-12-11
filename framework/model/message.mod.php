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
	if (empty($message_notice_log['create_time'])) {
		$message_notice_log['create_time'] = TIMESTAMP;
	}
	if (empty($message_notice_log['is_read'])) {
		$message_notice_log['is_read'] = MESSAGE_NOREAD;
	}
	if (in_array($type, array(MESSAGE_ORDER_TYPE, MESSAGE_WORKORDER_TYPE, MESSAGE_REGISTER_TYPE))) {
		message_notice_record_cloud($message_notice_log);
	}
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

/**
 * 工单消息记录
 */
function message_notice_worker() {
	global $_W;
	load()->func('communication');
	load()->classs('cloudapi');
	$api = new CloudApi();
	$table = table('message');
	$time = 0;
	$table->searchWithType(MESSAGE_WORKORDER_TYPE);
	$message_record = $table->messageRecord();

	if (!empty($message_record)) {
		$time = $message_record['create_time'];
	}
	$api_url = $api->get('system', 'workorder', array('do' => 'notload', 'time' => $time), 'json', false);
	if (is_error($api_url)) {
		return true;
	}

	$request_url = $api['data']['url'];
	$response = ihttp_get($request_url);
	$uid = $_W['config']['setting']['founder'];
	if ($response['code'] == 200) {
		$content = $response['content'];
		$worker_notice_lists = json_decode($content, JSON_OBJECT_AS_ARRAY);
		if (!empty($worker_notice_lists)) {
			foreach ($worker_notice_lists as $list) {
				message_notice_record($list['note'], $uid, $list['uuid'], MESSAGE_WORKORDER_TYPE, array('create_time' => strtotime($list['updated_at'])));
			}
		}
	}
	return true;
}

/**
 * 把消息推送到云服务
 * @param $message
 * @return array|mixed|string
 */
function message_notice_record_cloud($message) {
	load()->classs('cloudapi');
	$api = new CloudApi();
	$result = $api->post('system', 'notify', array('josn' => $message), 'html', false);
	return $result;
}