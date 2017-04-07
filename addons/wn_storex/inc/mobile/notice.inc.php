<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('notice_list', 'read_notice', 'unread_num');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';
check_params();
$uid = mc_openid2uid($_W['openid']);

$setting = pdo_get('mc_card', array('uniacid' => $_W['uniacid']));
if (empty($setting) || $setting['sign_status'] != 1) {
	message(error(-1, '没有开启签到！'), '', 'ajax');
}

if ($op == 'unread_num') {
	$notices = get_notices();
	$unread_num = 0;
	if (!empty($notices)) {
		foreach ($notices as $val){
			if (empty($val['read_status'])) {
				$unread_num ++;
			}
		}
	}
	message(error(0, $unread_num), '', 'ajax');
}

if ($op == 'notice_list') {
	$notices = get_notices();
	message(error(0, $notices), '', 'ajax');
}

if ($op == 'read_notice') {
	$id = intval($_GPC['id']);
	$notice = pdo_get('storex_notices', array('uniacid' => intval($_W['uniacid']), 'id' => $id));
	if (!empty($notice)) {
		$read_record = pdo_get('storex_notices_unread', array('uniacid' => intval($_W['uniacid']), 'notice_id' => $id, 'uid' => $uid));
		if (empty($read_record)) {
			$insert_record = array(
				'uniacid' => intval($_W['uniacid']),
				'uid' => $uid,
				'notice_id' => $id,
				'is_new' => 1,
				'type' => $notice['type'],
			);
			pdo_insert('storex_notices_unread', $insert_record);
		}
		message(error(0, $notice), '', 'ajax');
	} else {
		message(error(-1, '通知不存在，请刷新！'), '', 'ajax');
	}
}

function get_notices(){
	global $_W, $_GPC;
	$uid = mc_openid2uid($_W['openid']);
	$notices = pdo_getall('storex_notices', array('uniacid' => intval($_W['uniacid']), 'type' => 1), array(), 'id', 'addtime DESC');
	if (!empty($notices)) {
		$notice_ids = array();
		foreach ($notices as &$info) {
			$info['read_status'] = 0; //未读
			$info['addtime'] = date('Y-m-d', $info['addtime']);
			if (!empty($info['thumb'])) {
				$info['thumb'] = tomedia($info['thumb']);
			}
			$notice_ids[] = $info['id'];
		}
		$read_record = pdo_getall('storex_notices_unread', array('uid' => $uid, 'uniacid' => intval($_W['uniacid']), 'notice_id IN' => $notice_ids));
		if (!empty($read_record)) {
			foreach ($read_record as $val){
				if (!empty($notices[$val['notice_id']])) {
					$notices[$val['notice_id']]['read_status'] = 1; //已读
				}
			}
		}
	}
	return $notices;
}