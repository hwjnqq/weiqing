<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('notice_list', 'read_notice');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';
check_params();
$uid = mc_openid2uid($_W['openid']);

$setting = pdo_get('mc_card', array('uniacid' => $_W['uniacid']));
if (empty($setting) || $setting['sign_status'] != 1) {
	message(error(-1, '没有开启签到！'), '', 'ajax');
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
		message(error(-1, '通知已过期！'), '', 'ajax');
	}
}