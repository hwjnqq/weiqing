<?php
/**
 * [WeEngine System] Copyright (c) 2014 W7.CC.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');
load()->model('extension');

$eid = intval($_GPC['eid']);
if (!empty($eid)) {
	$entry = module_entry($eid);
} else {
	$entry_module_name = safe_gpc_string($_GPC['module_name']) ?: safe_gpc_string($_GPC['m']);
	$entry = table('modules_bindings')
		->where(array(
			'module' => $entry_module_name,
			'do' => trim($_GPC['do'])
		))
		->get();
	if (empty($entry)) {
		$entry = array(
			'module' => $entry_module_name,
			'do' => $_GPC['do'],
			'state' => $_GPC['state'],
			'direct' => $_GPC['direct'],
		);
	}
}
if (empty($entry) || empty($entry['do'])) {
	itoast('非法访问.', '', '');
}

$module = module_fetch($entry['module']);

if (empty($module)) {
	
	itoast("访问非法, 没有操作权限. (module: {$entry['module']})", '', '');
}
if (!$entry['direct']) {
	checklogin();
	$referer = (url_params(referer()));
	if (empty($_W['isajax']) && empty($_W['ispost']) && empty($_GPC['version_id']) && intval($referer['version_id']) > 0 &&
		('wxapp' == $referer['c'] ||
		'site' == $referer['c'] && in_array($referer['a'], array('entry', 'nav')) ||
		'home' == $referer['c'] && 'welcome' == $referer['a'] ||
		'module' == $referer['c'] && in_array($referer['a'], array('manage-account', 'permission')))) {
		itoast('', $_W['siteurl'] . '&version_id=' . $referer['version_id']);
	}
	
	
		if (empty($_W['uniacid'])) {
			itoast('', $_W['siteroot'] . 'web/home.php');
		}
	

	if ('menu' == $entry['entry']) {
		$permission = permission_check_account_user_module($entry['module'] . '_menu_' . $entry['do'], $entry['module']);
	} else {
		$permission = permission_check_account_user_module($entry['module'] . '_rule', $entry['module']);
	}
	$module_permissions = permission_account_user_menu($_W['uid'], $_W['uniacid'], 'modules');
	if (!$permission && 'all' != $module_permissions[0]) {
		itoast('您没有权限进行该操作', '', '');
	}

	// 兼容历史性问题：模块内获取不到模块信息$module的问题
	define('CRUMBS_NAV', 1);

	$_W['page']['title'] = $entry['title'];
	define('ACTIVE_FRAME_URL', url('site/entry/', array('eid' => $entry['eid'], 'version_id' => $_GPC['version_id'])));
}

$_GPC['__entry'] = $entry['title'];
$_GPC['__state'] = $entry['state'];
$_GPC['state'] = $entry['state'];
$_GPC['m'] = $entry['module'];
$_GPC['do'] = $entry['do'];

$_W['current_module'] = $module;




	$site = WeUtility::createModuleSite($entry['module']);


define('IN_MODULE', $entry['module']);
if (!is_error($site)) {
	if (ACCOUNT_MANAGE_NAME_OWNER == $_W['role']) {
		$_W['role'] = ACCOUNT_MANAGE_NAME_MANAGER;
	}
	$sysmodule = module_system();
	if (in_array($m, $sysmodule)) {
		$site_urls = $site->getTabUrls();
	}
	
		$method = 'doWeb' . ucfirst($entry['do']);
	
	
	exit($site->$method());
}
itoast("访问的方法 {$method} 不存在.", referer(), 'error');
