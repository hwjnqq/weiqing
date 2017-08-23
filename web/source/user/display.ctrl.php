<?php
/**
 * 用户管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('user');

$dos = array('display', 'operate');
$do = in_array($do, $dos) ? $do: 'display';

$_W['page']['title'] = '用户列表 - 用户管理';
$founders = explode(',', $_W['config']['setting']['founder']);

if ($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$users_table = table('users');
	$type = empty($_GPC['type']) ? 'display' : $_GPC['type'];
	if (in_array($type, array('display', 'check', 'recycle', 'clerk'))) {
		switch ($type) {
			case 'check':
				uni_user_permission_check('system_user_check');
				$users_table->searchWithStatus(USER_STATUS_CHECK);
				break;
			case 'recycle':
				uni_user_permission_check('system_user_recycle');
				$users_table->searchWithStatus(USER_STATUS_BAN);
				break;
			case 'clerk':
				uni_user_permission_check('system_user_clerk');
				$users_table->searchWithStatus(USER_STATUS_NORMAL);
				$users_table->searchWithType(USER_TYPE_CLERK);
				break;
			default:
				uni_user_permission_check('system_user');
				$users_table->searchWithStatus(USER_STATUS_NORMAL);
				$users_table->searchWithType(USER_TYPE_COMMON);
				$users_table->searchWithFounder(array(ACCOUNT_MANAGE_GROUP_GENERAL, ACCOUNT_MANAGE_GROUP_FOUNDER));
				break;
		}

		$username = trim($_GPC['username']);
		if (!empty($username)) {
			$users_table->searchWithName($username);
		}

		$users_table->searchWithPage($pindex, $psize);
		$users = $users_table->searchUsersList();
		$total = $users_table->getLastQueryTotal();
		$users = user_list_format($users);
		$pager = pagination($total, $pindex, $psize);
	}
	template('user/display');
}

if ($do == 'operate') {
	$type = $_GPC['type'];
	$types = array('recycle', 'recycle_delete', 'recycle_restore', 'check_pass');
	if (!in_array($type, $types)) {
		itoast('类型错误！', referer(), 'fail');
	}
	switch ($type) {
		case 'check_pass':
			uni_user_permission_check('system_user_check');
			break;
		case 'recycle':
		case 'recycle_delete':
		case 'recycle_restore':
			uni_user_permission_check('system_user_recycle');
			break;
	}
	$uid = intval($_GPC['uid']);
	$uid_user = user_single($uid);
	if (in_array($uid, $founders)) {
		itoast('访问错误, 无法操作站长.', url('user/display'), 'error');
	}
	if (empty($uid_user)) {
		exit('未指定用户,无法删除.');
	}
	switch ($type) {
		case 'check_pass':
			$data = array('status' => 2);
			pdo_update('users', $data , array('uid' => $uid));
			itoast('更新成功！', referer(), 'success');
			break;
		case 'recycle'://删除用户到回收站
			user_delete($uid, true);
			itoast('更新成功！', referer(), 'success');
			break;
		case 'recycle_delete'://永久删除用户
			user_delete($uid);
			itoast('删除成功！', referer(), 'success');
			break;
		case 'recycle_restore':
			$data = array('status' => 2);
			pdo_update('users', $data , array('uid' => $uid));
			itoast('启用成功！', referer(), 'success');
			break;
	}
}