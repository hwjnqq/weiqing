<?php
/**
 * 用户列表
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('display', 'check_display', 'check_pass', 'recycle_display', 'recycle_delete','recycle_restore', 'recycle', 'vice_founder');
$do = in_array($do, $dos) ? $do: 'display';

$_W['page']['title'] = '用户列表 - 用户管理';
load()->model('user');
$founders = explode(',', $_W['config']['setting']['founder']);

if (in_array($do, array('display', 'recycle_display', 'check_display', 'vice_founder'))) {
	switch ($do) {
		case 'check_display':
			uni_user_permission_check('system_user_check');
			$condition = ' WHERE u.status = 1 ';
			break;
		case 'recycle_display':
			uni_user_permission_check('system_user_recycle');
			$condition = ' WHERE u.status = 3 ';
			break;
		case 'vice_founder':
			$condition = ' WHERE u.founder_groupid = ' . ACCOUNT_MANAGE_GROUP_VICE_FOUNDER;
			break;
		default:
			uni_user_permission_check('system_user');
			$condition = ' WHERE u.status = 2 ';
			break;
	}
	if ($_W['user']['founder_groupid'] == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER) {
		$condition .= ' AND u.vice_founder_id = ' . $_W['uid'];
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
	$system_module_num = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('modules') . "WHERE type = :type AND issystem = :issystem", array(':type' => 'system',':issystem' => 1));
	foreach ($users as &$user) {
		$user['avatar'] = !empty($user['avatar']) ? $user['avatar'] : './resource/images/nopic-user.png';
		if (empty($user['endtime'])) {
			$user['endtime'] = '永久有效';
		} else {
			if ($user['endtime'] <= TIMESTAMP) {
				$user['endtime'] = '服务已到期';
			} else {
				$user['endtime'] = date('Y-m-d', $user['endtime']);
			}
		}

		$user['founder'] = user_is_founder($user['uid']);
		$user['uniacid_num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('uni_account_users')." WHERE uid = :uid", array(':uid' => $user['uid']));

		$user['module_num'] =array();
		$group = pdo_get('users_group', array('id' => $user['groupid']));
		if (!empty($group)) {
			$user['maxaccount'] = in_array($user['uid'], $founders) ? '不限' : $group['maxaccount'];
			$user['groupname'] = $group['name'];
			$package = iunserializer($group['package']);
			$group['package'] = uni_groups($package);
			foreach ($group['package'] as $modules) {
				if (is_array($modules['modules'])) {
					foreach ($modules['modules'] as  $module) {
						$user['module_num'][] = $module['name'];
					}
				}
			}
		}

		if ($user['founder_groupid'] == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER) {
			$user['maxaccount'] = '不限';
		}
		$user['module_num'] = array_unique($user['module_num']);
		$user['module_nums'] = count($user['module_num']) + $system_module_num;
	}
	unset($user);
	$usergroups = pdo_getall('users_group', array(), array(), 'id');
	template('user/display');
}

if (in_array($do, array('recycle', 'recycle_delete', 'recycle_restore', 'check_pass'))) {
	switch ($do) {
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
	switch ($do) {
		case 'check_pass':
			$data = array('status' => 2);
			pdo_update('users', $data , array('uid' => $uid));
			itoast('更新成功！', referer(), 'success');
			break;
		case 'recycle'://删除用户到回收站
			$data = array('status' => 3);
			pdo_update('users', $data , array('uid' => $uid));
			itoast('更新成功！', referer(), 'success');
			break;
		case 'recycle_delete'://永久删除用户
			$founder_groupid = pdo_getcolumn('users', array('uid' => $uid), 'founder_groupid');
			if ($founder_groupid == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER) {
				pdo_update('users', array('vice_founder_id' => 0), array('vice_founder_id' => $uid));
				pdo_update('users_group', array('vice_founder_id' => 0), array('vice_founder_id' => $uid));
				pdo_update('uni_group', array('vice_founder_id' => 0), array('vice_founder_id' => $uid));
			}
			if (pdo_delete('users', array('uid' => $uid)) === 1) {
				//把该用户所属的公众号返给创始人
				$user_set_account = pdo_getall('uni_account_users', array('uid' => $uid, 'role' => 'owner'));
				if (!empty($user_set_account)) {
					foreach ($user_set_account as $account) {
						cache_build_account_modules($account['uniacid']);
					}
				}
				pdo_delete('uni_account_users', array('uid' => $uid));
				pdo_delete('users_profile', array('uid' => $uid));
				itoast('删除成功！', referer(), 'success');
			} else {
				itoast('删除失败！', referer(), 'error');
			}
			break;
		case 'recycle_restore':
			$data = array('status' => 2);
			pdo_update('users', $data , array('uid' => $uid));
			itoast('启用成功！', referer(), 'success');
			break;
	}
}