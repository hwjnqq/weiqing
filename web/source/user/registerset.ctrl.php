<?php
/**
 * 用户注册设置
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('setting');

uni_user_permission_check('system_user_display');
$_W['page']['title'] = '注册选项 - 用户管理';
$state = uni_permission($_W['uid'], $uniacid);
if ($state != 'founder' && $state != 'manager') {
	message('没有操作权限！');
}

if (checksubmit('submit')) {
	setting_save(array('open' => intval($_GPC['open']), 'verify' => intval($_GPC['verify']), 'code' => intval($_GPC['code']), 'groupid' => intval($_GPC['groupid'])), 'register');
	cache_delete("defaultgroupid:{$_W['uniacid']}");
	message('更新设置成功！', url('user/registerset'));
}
$settings = $_W['setting']['register'];
$groups = pdo_fetchall("SELECT id, name FROM ".tablename('users_group')." ORDER BY id ASC");

template('user/registerset');