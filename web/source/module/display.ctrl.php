<?php 
/**
 * 应用列表
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');
load()->model('wxapp');

$dos = array('display', 'switch', 'have_permission_uniacids', 'accounts_dropdown_menu');
$do = in_array($do, $dos) ? $do : 'display';

if ($do == 'display') {
	$user_module = user_modules($_W['uid']);
	foreach ($user_module as $key => $module_value) {
		if (!empty($module_value['issystem'])) {
			unset($user_module[$key]);
		}
	}
	template('module/display');
}

if ($do == 'switch') {
	$module_name = trim($_GPC['module_name']);
	$module_info = module_fetch($module_name);
	if (empty($module_name) || empty($module_info)) {
		itoast('模块不存在或已经删除！', referer(), 'error');
	}
	$last_module_info = module_last_switch($module_name);
	if (empty($last_module_info)) {
		$accounts_list = module_link_uniacid_fetch($_W['uid'], $module_name);
		$current_account = current($accounts_list);
		$uniacid = $current_account['uniacid'];
		$version_id = $current_account['version_id'];
	} else {
		$uniacid = $last_module_info['uniacid'];
		$version_id = $last_module_info['version_id'];
	}
	if (!empty($version_id)) {
		$version_info = wxapp_version($version_id);
	}
	if (empty($uniacid) && !empty($version_id)) {
		wxapp_save_switch($version_info['uniacid']);
		itoast('', url('wxapp/display/switch', array('module' => $module_name, 'version_id' => $version_id)), 'success');
	}
	if (!empty($uniacid)) {
		if (empty($version_id)) {
			uni_account_save_switch($uniacid);
			uni_account_switch($uniacid, url('home/welcome/ext/', array('m' => $module_name)));
		}
		if ($version_info['uniacid'] != $uniacid) {
			uni_account_save_switch($uniacid);
			itoast('', url('home/welcome/ext', array('m' => $module_name, 'version_id' => $version_id)), 'success');
		} else {
			wxapp_save_switch($version_info['uniacid']);
			itoast('', url('wxapp/display/switch', array('module' => $module_name, 'version_id' => $version_id)), 'success');
		}
	}
}

if ($do == 'have_permission_uniacids') {
	$module_name = trim($_GPC['module_name']);
	$accounts_list = module_link_uniacid_fetch($_W['uid'], $module_name);
	iajax(0, $accounts_list);
}
if ($do == 'accounts_dropdown_menu') {
	$module_name = trim($_GPC['module_name']);
	if (empty($module_name)) {
		exit('');
	}
	$last_module_info = module_last_switch($module_name);
	$accounts_list = module_link_uniacid_fetch($_W['uid'], $module_name);
	if (empty($accounts_list)) {
		exit('');
	}

	$return_selected_html = '<span class="top-view">';
	foreach ($accounts_list as $account) {
		if (empty($account['uniacid']) || $account['uniacid'] != $_W['uniacid']) {
			continue;
		}
		if (in_array($_W['account']['type'], array(ACCOUNT_TYPE_OFFCIAL_NORMAL, ACCOUNT_TYPE_OFFCIAL_AUTH))) {
			$return_selected_html .= '<a href="' . url('account/display/switch', array('uniacid' => $_W['uniacid'])) . '"><i class="wi wi-wechat"></i>' .  $_W['account']['name'] . '</a>';
			if (!empty($account['version_id'])) {
				$version_info = wxapp_version($account['version_id']);
				$return_selected_html .= '<a href="' . url('wxapp/display/switch', array('uniacid' => $version_info['uniacid'], 'version_id' => $account['version_id'])) . '"><i class="wi wi-wxapp"></i>' .  $account['wxapp_name'] . '</a>';
			}
			break;
		} elseif ($_W['account']['type'] == ACCOUNT_TYPE_APP_NORMAL) {
			$version_info = wxapp_version($account['version_id']);
			if ($version_info['uniacid'] != $account['uniacid']) {
				$return_selected_html .= '<a href="' . url('account/display/switch', array('uniacid' => $account['uniacid'])) . '"><i class="wi wi-wechat"></i>' .  $_W['account']['name'] . '</a>';
			}
			$return_selected_html .= '<a href="' . url('wxapp/display/switch', array('uniacid' => $version_info['uniacid'], 'version_id' => $account['version_id'])) . '"><i class="wi wi-wxapp"></i>' .  $account['wxapp_name'] . '</a>';
			break;
		}
		
	}
	$return_selected_html .= '</span>';

	$return_dropmenu_html = '<span class="dropdown"><a href="javascript:;" class="dropdown-icon" data-toggle="dropdown"><i class="wi wi-angle-down"></i></a><ul class="dropdown-menu dropdown-menu-right" role="menu">';
	foreach ($accounts_list as $account) {
		$return_dropmenu_html .= '<li>';
		if (!empty($account['app_name'])) {
			$return_dropmenu_html .= '<span><a href="' . url('account/display/switch', array('uniacid' => $account['uniacid'])) . '"><i class="wi wi-wechat"></i>' . $account['app_name'] . '</a></span>';
		}
		if (!empty($account['app_name']) && !empty($account['wxapp_name'])) {
			$return_dropmenu_html .= '<span class="plus"><i class="wi wi-plus"></i></span>';
		}
		if (!empty($account['wxapp_name'])) {
			$version_info = wxapp_version($account['version_id']);
			$return_dropmenu_html .= '<span><a href="' . url('wxapp/display/switch', array('uniacid' => $version_info['uniacid'], 'version_id' => $account['version_id'])) . '"><i class="wi wi-wechat"></i>' . $account['wxapp_name'] . '</a></span>';
		}
		$return_dropmenu_html .= '</li>';
	}
	$return_dropmenu_html .= '</ul></span>';
	echo $return_selected_html . $return_dropmenu_html;
	exit;
}