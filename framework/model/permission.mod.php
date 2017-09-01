<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 构建管理员或操作员权限数据
 * @return boolean
 */
function permission_build() {
	global $_W;
	$we7_file_permission = require IA_ROOT . '/web/common/permission.inc.php';
	$permission_frames = require IA_ROOT . '/web/common/frames.inc.php';
	if (!in_array($_W['role'], array(ACCOUNT_MANAGE_NAME_OPERATOR, ACCOUNT_MANAGE_NAME_MANAGER)) || empty($_W['uniacid'])) {
		return $we7_file_permission;
	}

	$cachekey = cache_system_key("permission:{$_W['uniacid']}:{$_W['uid']}");
	$cache = cache_load($cachekey);
	if (!empty($cache)) {
		return $cache;
	}
	$permission_exist = uni_user_permission_exist($_W['uid'], $_W['uniacid']);
	if (empty($permission_exist)) {
		$we7_file_permission['platform'][$_W['role']] = array('platform*');
		$we7_file_permission['site'][$_W['role']] = array('site*');
		$we7_file_permission['mc'][$_W['role']] = array('mc*');
		$we7_file_permission['profile'][$_W['role']] = array('profile*');
		$we7_file_permission['module'][$_W['role']] = array('manage-account', 'display');
		$we7_file_permission['wxapp'][$_W['role']] = array('display', 'payment', 'post', 'version');
		cache_write($cachekey, $we7_file_permission);
		return $we7_file_permission;
	}
	$user_account_permission = uni_user_menu_permission($_W['uid'], $_W['uniacid'], PERMISSION_ACCOUNT);
	$user_wxapp_permission = uni_user_menu_permission($_W['uid'], $_W['uniacid'], PERMISSION_WXAPP);
	$user_permission = array_merge($user_account_permission, $user_wxapp_permission);

	$permission_contain = array('account', 'wxapp', 'system');
	$section = array();
	$permission_result = array();
	foreach ($permission_frames as $key => $frames) {
		if (!in_array($key, $permission_contain) || empty($frames['section'])) {
			continue;
		}
		foreach ($frames['section'] as $frame_key => $frame) {
			if (empty($frame['menu'])) {
				continue;
			}
			$section[$key][$frame_key] = $frame['menu'];
		}
	}
	$account = permission_get_nameandurl($section[$permission_contain[0]]);
	$wxapp = permission_get_nameandurl($section[$permission_contain[1]]);
	$system = permission_get_nameandurl($section[$permission_contain[2]]);
	$permission_result = array_merge($account, $wxapp, $system);

	foreach ($permission_result as $permission_val) {
		if (in_array($permission_val['permission_name'], $user_permission)) {
			$we7_file_permission[$permission_val['controller']][$_W['role']][] = $permission_val['action'];
		}
	}
	cache_write($cachekey, $we7_file_permission);
	return $we7_file_permission;
}

/**
 * @return array()
 */
function permission_get_nameandurl($permission) {
	$result = array();
	if (empty($permission)) {
		return $result;
	}
	foreach ($permission as $menu) {
		if (empty($menu)) {
			continue;
		}
		foreach ($menu as $permission_name) {
			$url_query_array = url_params($permission_name['url']);
			$result[] = array(
				'url' => $permission_name['url'],
				'controller' => $url_query_array['c'],
				'action' => $url_query_array['a'],
				'permission_name' => $permission_name['permission_name']
			);
		}
	}
	return $result;
}