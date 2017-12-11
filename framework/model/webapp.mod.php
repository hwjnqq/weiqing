<?php

defined('IN_IA') or exit('Access Denied');


function webapp_switch($uniacid, $redirect = '') {
	global $_W;
	webapp_save_switch($uniacid);
	isetcookie('__uid', $_W['uid'], 7 * 86400);
	if (!empty($redirect)) {
		header('Location: ' . $redirect);
		exit;
	}
	return true;
}

/**
 * 切换pc时，保留最后一次操作的pc，以便点pc时再切换回
 */
function webapp_save_switch($uniacid) {
	global $_W, $_GPC;
	if (empty($_GPC['__switch'])) {
		$_GPC['__switch'] = random(5);
	}

	$cache_key = cache_system_key(CACHE_KEY_ACCOUNT_SWITCH, $_GPC['__switch']);
	$cache_lastaccount = cache_load($cache_key);
	if (empty($cache_lastaccount)) {
		$cache_lastaccount = array(
			'webapp' => $uniacid,
		);
	} else {
		$cache_lastaccount['webapp'] = $uniacid;
	}
	cache_write($cache_key, $cache_lastaccount);
	isetcookie('__uniacid', $uniacid, 7 * 86400);
	isetcookie('__switch', $_GPC['__switch'], 7 * 86400);
	return true;
}

/**
 *  是否可创建PC
 * @param $uid
 *
 *
 * @since version
 */
function webapp_can_create($uid) {
	if(user_is_founder($uid)) { //创始人可以创建
		return true;
	}
	$data = permission_user_account_num($uid);
	return isset($data['webapp_limit']) && $data['webapp_limit'] > 0;
}