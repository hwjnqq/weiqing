<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
if ($action != 'display') {
	define('FRAME', 'system');
}
if ($controller == 'account' && $action == 'manage') {
	if ($do == 'display') {
		header("Location: " . url('account/post', array('account_type' => ACCOUNT_TYPE_APP_NORMAL)));
		exit();
	}
	if ($_GPC['account_type'] == ACCOUNT_TYPE_APP_NORMAL) {
		define('ACTIVE_FRAME_URL', url('account/manage/display', array('account_type' => ACCOUNT_TYPE_APP_NORMAL)));
	}
}
if ($action == 'display') {
	header("Location: " . url('account/post', array('account_type' => ACCOUNT_TYPE_APP_NORMAL)));
	exit();
}

$_GPC['account_type'] = !empty($_GPC['account_type']) ? $_GPC['account_type'] : ACCOUNT_TYPE_OFFCIAL_NORMAL;
if ($_GPC['account_type'] == ACCOUNT_TYPE_APP_NORMAL) {
	define('ACCOUNT_TYPE', ACCOUNT_TYPE_APP_NORMAL);
	define('ACCOUNT_TYPE_OFFCIAL', 0);
	define('ACCOUNT_TYPE_NAME', '小程序');
	define('ACCOUNT_TYPE_TEMPLATE', '-wxapp');
} elseif (empty($_GPC['account_type']) || $_GPC['account_type'] == ACCOUNT_TYPE_OFFCIAL_NORMAL || $_GPC['account_type'] == ACCOUNT_TYPE_OFFCIAL_AUTH) {
	define('ACCOUNT_TYPE', ACCOUNT_TYPE_OFFCIAL_NORMAL);
	$account_type_offcial = $_GPC['account_type'] == ACCOUNT_TYPE_OFFCIAL_NORMAL ? ACCOUNT_TYPE_OFFCIAL_NORMAL : ACCOUNT_TYPE_OFFCIAL_AUTH;
	define('ACCOUNT_TYPE_OFFCIAL', $account_type_offcial);
	define('ACCOUNT_TYPE_NAME', '公众号');
	define('ACCOUNT_TYPE_TEMPLATE', '');
}

if ($action == 'post') {
	if (empty($_W['uniacid'])) {
		if (ACCOUNT_TYPE == ACCOUNT_TYPE_OFFCIAL_NORMAL) {
			header("Location: " . url('account/post-step'));
			exit();
		} else {
			header("Location: " . url('wxapp/post/design_method'));
			exit();
		}
	}
}