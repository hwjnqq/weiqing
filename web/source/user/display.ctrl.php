<?php
/**
 * 用户列表
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('user');

$dos = array('display', 'operate');
$do = in_array($do, $dos) ? $do: 'display';

$_W['page']['title'] = '用户列表 - 用户管理';
$founders = explode(',', $_W['config']['setting']['founder']);

if ($do == 'display') {
	$type = empty($_GPC['type']) ? 'display' : $_GPC['type'];
	if (in_array($type, array('display', 'check', 'recycle'))) {
		switch ($type) {
			case 'check':
				uni_user_permission_check('system_user_check');
				$condition = " WHERE u.status = 1 ";
				break;
			case 'recycle':
				uni_user_permission_check('system_user_recycle');
				$condition = " WHERE u.status = 3 ";
				break;
			default:
				uni_user_permission_check('system_user');
				$condition = " WHERE u.status = 2 AND u.founder_groupid != " . ACCOUNT_MANAGE_GROUP_VICE_FOUNDER;
				break;
		}
		if (user_is_vice_founder()) {
			$condition .= ' AND u.owner_uid = ' . $_W['uid'];
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$params = array();
		if (!empty($_GPC['username'])) {
			$condition .= " AND u.username LIKE :username";
			$params[':username'] = "%{$_GPC['username']}%";
		}
		$sql = 'SELECT u.*, p.avatar FROM ' . tablename('users') .' AS u LEFT JOIN ' . tablename('users_profile') . ' AS p ON u.uid = p.uid '. $condition . " LIMIT " . ($pindex - 1) * $psize .',' .$psize;
		$users = pdo_fetchall($sql, $params);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('users') .' AS u '. $condition, $params);
		$pager = pagination($total, $pindex, $psize);

		$groups = user_group();
		$users = user_list_format($users);
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