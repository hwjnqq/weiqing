<?php
/**
 * 管理公众号--使用者管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('module');
load()->model('system');

$dos = array('delete', 'edit', 'set_permission', 'set_manager', 'module');
$do = in_array($do, $dos) ? $do : 'edit';

$uniacid = intval($_GPC['uniacid']);
$acid = intval($_GPC['acid']);
$_W['page']['title'] = '管理设置 - 微信' . ACCOUNT_TYPE_NAME . '管理';
if (empty($uniacid) || empty($acid)) {
	message('请选择要编辑的公众号', referer(), 'error');
}
$state = uni_permission($_W['uid'], $uniacid);
//只有创始人、主管理员、管理员才有权限
if ($state != ACCOUNT_MANAGE_NAME_OWNER && $state != ACCOUNT_MANAGE_NAME_FOUNDER && $state != ACCOUNT_MANAGE_NAME_MANAGER) {
	message('无权限操作！', referer(), 'error');
}
$founders = explode(',', $_W['config']['setting']['founder']);
$headimgsrc = tomedia('headimg_'.$acid.'.jpg');
$account = account_fetch($acid);
if ($do == 'edit') {
	$permissions = pdo_fetchall("SELECT id, uid, role FROM ".tablename('uni_account_users')." WHERE uniacid = '$uniacid' and role != :role  ORDER BY uid ASC, role DESC", array(':role' => 'clerk'), 'uid');
	$owner = pdo_get('uni_account_users', array('uniacid' => $uniacid, 'role' => 'owner'), array('uid', 'id'));
	if (!empty($permissions)) {
		$member = pdo_fetchall("SELECT username, uid FROM ".tablename('users')." WHERE uid IN (".implode(',', array_keys($permissions)).")", array(), 'uid');
		if (!empty($member)) {
			foreach ($permissions as $key => $per_val) {
				$permissions[$key]['isfounder'] = in_array($member[$key]['uid'], $founders) ? 1 : 0;
				$permissions[$key]['username'] = $member[$key]['username'] ? $member[$key]['username'] : '';
			}
		}
	}
	$uids = array();
	foreach ($permissions as $v) {
		$uids[] = $v['uid'];
	}
	template('account/manage-users' . ACCOUNT_TYPE_TEMPLATE);
} elseif ($do == 'delete') {
	$uid = is_array($_GPC['uid']) ? 0 : intval($_GPC['uid']);
	if (empty($uid)) {
		message('请选择要删除的用户！', referer(), 'error');
	}
	$data = array(
		'uniacid' => $uniacid,
		'uid' => $uid,
	);
	$exists = pdo_get('uni_account_users', array('uniacid' => $uniacid, 'uid' => $uid));
	if (!empty($exists)) {
		if ($state == ACCOUNT_MANAGE_NAME_MANAGER && ($exists['role'] == ACCOUNT_MANAGE_NAME_OWNER || $exists['role'] == ACCOUNT_MANAGE_NAME_MANAGER)) {
			message('管理员不可操作其他管理员', referer(), 'error');
		}
		$result = pdo_delete('uni_account_users', $data);
		if ($result) {
			message('删除成功！', referer(), 'success');
		} else {
			message('删除失败，请重试！', referer(), 'error');
		}
	} else {
		message('该公众号下不存在该用户！', referer(), 'error');
	}
} elseif ($do == 'set_manager') {
	$username = trim($_GPC['username']);
	$user = user_single(array('username' => $username));
	if (!empty($user)) {
		if ($user['status'] != 2) {
			message(error(3, '用户未通过审核或不存在！'), '', 'ajax');
		}
		if (in_array($user['uid'], $founders)) {
			message(error(1, '不可操作网站创始人！'), '', 'ajax');
		}
		//添加/修改公众号操作员、管理员、主管理员时执行数量判断
		$uni_account_type = ACCOUNT_TYPE == ACCOUNT_TYPE_APP_NORMAL ? 3 : 1;
		if (is_error($permission = uni_create_permission($user['uid'], $uni_account_type))) {
			message(error(5, $permission['message']), '', 'error');
		}

		$addtype = intval($_GPC['addtype']);
		$data = array(
			'uniacid' => $uniacid,
			'uid' => $user['uid'],
		);

		$exists = pdo_get('uni_account_users', $data);
		$owner = pdo_get('uni_account_users', array('uniacid' => $uniacid, 'role' => 'owner'));
		if (empty($exists)) {
			if ($addtype == ACCOUNT_MANAGE_TYPE_OWNER) {
				if ($state == ACCOUNT_MANAGE_NAME_MANAGER) {
					message(error(4, '管理员不可操作主管理员'), '', 'ajax');
				}
				if (empty($owner)) {
					$data['role'] = ACCOUNT_MANAGE_NAME_OWNER;
				} else  {
					$result = pdo_update('uni_account_users', $data, array('id' => $owner['id']));
					if ($result) {
						message(error(0, '修改成功！'), '', 'ajax');
					} else  {
						message(error(1, '修改失败！'), '', 'ajax');
					}
					exit;
				}
			} else if ($addtype == ACCOUNT_MANAGE_TYPE_MANAGER) {
				if ($state == ACCOUNT_MANAGE_NAME_MANAGER) {
					message(error(4, '管理员不可操作管理员'), '', 'ajax');
				}
				$data['role'] = ACCOUNT_MANAGE_NAME_MANAGER;
			} else  {
				$data['role'] = ACCOUNT_MANAGE_NAME_OPERATOR;
			}
			pdo_delete('uni_account_users',  array('uniacid' => $uniacid,'uid' => $user['uid']));
			$result = pdo_insert('uni_account_users', $data);
			if ($result) {
				message(error(0, '添加成功！'), '', 'ajax');
			} else  {
				message(error(1, '添加失败！'), '', 'ajax');
			}
		} else {
			//{$username} 已经是该公众号的操作员或管理员，请勿重复添加
			message(error(2, $username.'已经是该公众号的操作员或管理员，请勿重复添加！'), '', 'ajax');
		}
	} else  {
		message(error(-1, '参数错误，请刷新重试！'), '', 'ajax');
	}
} elseif ($do == 'set_permission') {
	$uid = intval($_GPC['uid']);
	$user = user_single(array('uid' => $uid));
	if (empty($user)) {
		message('您操作的用户不存在或是已经被删除！');
	}
	$role = uni_permission($_W['uid'], $uniacid);
	if (empty($role)) {
		message('此用户没有操作该统一公众号的权限，请选指派“管理员”或是“操作员”权限！');
	}
	
	//获取系统权限
	$user_menu_permission = pdo_get('users_permission', array('uniacid' => $uniacid, 'uid' => $uid, 'type' => 'system'));
	if (!empty($user_menu_permission['permission'])) {
		$user_menu_permission['permission'] = explode('|', $user_menu_permission['permission']);
	} else {
		$user_menu_permission['permission'] = array();
	}
	//获取模块权限
	$module_permission = pdo_getall('users_permission', array('uniacid' => $uniacid, 'uid' => $uid, 'type !=' => 'system'), array(), 'type');
	$module_permission_keys = array_keys($module_permission);
	
	$menus = system_menu_permission_list($role);
	$module = uni_modules();
	
	if (checksubmit('submit')) {
		//获取全部permission_name，方便判断是否是系统菜单
		$menu_permission = array();
		if (!empty($menus)) {
			foreach ($menus as $nav_id => $section) {
				foreach ($section['section'] as $section_id => $section) {
					foreach ($section['menu']  as $menu_id => $menu) {
						$menu_permission[] = $menu['permission_name'];
						if (!empty($menu['sub_permission'])) {
							foreach ($menu['sub_permission'] as $sub_menu) {
								$menu_permission[] = $sub_menu['permission_name'];
							}
						}
					}
				}
			}
		}
		$user_menu_permission_new = array();
		if (!empty($_GPC['system'])) {
			foreach ($_GPC['system'] as $permission_name) {
				if (in_array($permission_name, $menu_permission)) {
					$user_menu_permission_new[] = $permission_name;
				}
			}
			if (empty($user_menu_permission['id'])) {
				$insert = array(
					'uniacid' => $uniacid,
					'uid' => $uid,
					'type' => 'system',
					'permission' => implode('|', $user_menu_permission_new),
				);
				pdo_insert('users_permission', $insert);
			} else {
				$update = array(
					'permission' => implode('|', $user_menu_permission_new),
				);
				pdo_update('users_permission', $update, array('uniacid' => $uniacid, 'uid' => $uid));
			}
		} else {
			pdo_delete('users_permission', array('uniacid' => $uniacid, 'uid' => $uid));
		}
		message('操作菜单权限成功！', referer(), 'success');
	}
	template('account/set-permission');
} elseif($do == 'module' && $_W['isajax']) {
	$uid = intval($_GPC['uid']);
	$user = user_single($uid);
	if(empty($user)) {
		message(error(1, '访问错误, 未找到指定操作用户.'), '', 'ajax');
	}
	$founders = explode(',', $_W['config']['setting']['founder']);
	$isfounder = in_array($user['uid'], $founders);
	if($isfounder) {
		message(error(2, '访问错误, 无法编辑站长.'), '', 'ajax');
	}

	$module_name = trim($_GPC['m']);
	$uniacid = intval($_GPC['uniacid']);
	$uid = intval($_GPC['uid']);
	$module = pdo_fetch('SELECT * FROM ' . tablename('modules') . ' WHERE name = :m', array(':m' => $module_name));
	//获取模块权限
	$purview = pdo_fetch('SELECT * FROM ' . tablename('users_permission') . ' WHERE uniacid = :aid AND uid = :uid AND type = :type', array(':aid' => $uniacid, ':uid' => $uid, ':type' => $module_name));
	if(!empty($purview['permission'])) {
		$purview['permission'] = explode('|', $purview['permission']);
	} else {
		$purview['permission'] = array();
	}

	$mineurl = array();
	$all = 0;
	if(!empty($mods)) {
		foreach($mods as $mod) {
			if($mod['url'] == 'all') {
				$all = 1;
				break;
			} else {
				$mineurl[] = $mod['url'];
			}
		}
	}
	$data = array();
	if($module['settings']) {
		$data[] = array('title' => '参数设置', 'permission' => $module_name.'_settings');
	}
	if($module['isrulefields']) {
		$data[] = array('title' => '回复规则列表', 'permission' => $module_name.'_rule');
	}
	$entries = module_entries($m);
	if(!empty($entries['home'])) {
		$data[] = array('title' => '微站首页导航', 'permission' => $module_name.'_home');
	}
	if(!empty($entries['profile'])) {
		$data[] = array('title' => '个人中心导航', 'permission' => $module_name.'_profile');
	}
	if(!empty($entries['shortcut'])) {
		$data[] = array('title' => '快捷菜单', 'permission' => $module_name.'_shortcut');
	}
	if(!empty($entries['cover'])) {
		foreach($entries['cover'] as $cover) {
			$data[] = array('title' => $cover['title'], 'permission' => $module_name.'_cover_'.$cover['do']);
		}
	}
	if(!empty($entries['menu'])) {
		foreach($entries['menu'] as $menu) {
			$data[] = array('title' => $menu['title'], 'permission' => $module_name.'_menu_'.$menu['do']);
		}
	}
	unset($entries);
	if(!empty($module['permissions'])) {
		$module['permissions'] = (array)iunserializer($module['permissions']);
		$data = array_merge($data, $module['permissions']);
	}
	foreach($data as &$data_val) {
		$data_val['checked'] = 0;
		if(in_array($data_val['permission'], $purview['permission']) || in_array('all', $purview['permission'])) {
			$data_val['checked'] = 1;
		}
	}
	unset($data_val);
	if (empty($data)) {
		message(error(3, '无子权限！'), '', 'ajax');
	} else {
		message(error(0, $data), '', 'ajax');
	}
}