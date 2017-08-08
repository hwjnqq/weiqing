<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 用户注册
 * PS:密码字段不要加密
 * @param array $user 用户注册信息，需要的字段必须包括 username, password, remark
 * @return int 成功返回新增的用户编号，失败返回 0
 */
function user_register($user) {
	if (empty($user) || !is_array($user)) {
		return 0;
	}
	if (isset($user['uid'])) {
		unset($user['uid']);
	}
	$user['salt'] = random(8);
	$user['password'] = user_hash($user['password'], $user['salt']);
	$user['joinip'] = CLIENT_IP;
	$user['joindate'] = TIMESTAMP;
	$user['lastip'] = CLIENT_IP;
	$user['lastvisit'] = TIMESTAMP;
	if (empty($user['status'])) {
		$user['status'] = 2;
	}
	$result = pdo_insert('users', $user);
	if (!empty($result)) {
		$user['uid'] = pdo_insertid();
	}
	return intval($user['uid']);
}

/**
 * 检查用户是否存在，多个如果检查的参数包括多个字段，则必须满足所有参数条件符合才返回true
 * PS:密码字段不要加密，不能单独依靠密码查询
 * @param array $user 用户信息，需要的字段可以包括 uid, username, password, status
 * @return bool
 */
function user_check($user) {
	if (empty($user) || !is_array($user)) {
		return false;
	}
	$where = ' WHERE 1 ';
	$params = array();
	if (!empty($user['uid'])) {
		$where .= ' AND `uid`=:uid';
		$params[':uid'] = intval($user['uid']);
	}
	if (!empty($user['username'])) {
		$where .= ' AND `username`=:username';
		$params[':username'] = $user['username'];
	}
	if (!empty($user['status'])) {
		$where .= " AND `status`=:status";
		$params[':status'] = intval($user['status']);
	}
	if (empty($params)) {
		return false;
	}
	$sql = 'SELECT `password`,`salt` FROM ' . tablename('users') . "$where LIMIT 1";
	$record = pdo_fetch($sql, $params);
	if (empty($record) || empty($record['password']) || empty($record['salt'])) {
		return false;
	}
	if (!empty($user['password'])) {
		$password = user_hash($user['password'], $record['salt']);
		return $password == $record['password'];
	}
	return true;
}

/**
 * 根据用户名获取副创始人的uid
 * @param string $username
 * @return bool
 */
function user_get_uid_byname($username = '') {
	$username = trim($username);
	if (empty($username)) {
		return false;
	}
	$uid = pdo_getcolumn('users', array('username' => $username, 'founder_groupid' => ACCOUNT_MANAGE_GROUP_VICE_FOUNDER), 'uid');
	return $uid;
}

/**
 * 判断是否是创始人
 */
function user_is_founder($uid) {
	global $_W;
	$founders = explode(',', $_W['config']['setting']['founder']);
	if (in_array($uid, $founders)) {
		return true;
	} else {
		$founder_groupid = pdo_getcolumn('users', array('uid' => $uid), 'founder_groupid');
		if ($founder_groupid == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER) {
			return true;
		}
	}
	return false;
}

/**
 * 判断是否是副创始人
 */
function user_is_vice_founder($uid = 0) {
	global $_W;
	$uid = intval($uid) > 0 ? intval($uid) : $_W['uid'];
	$user_info = user_single($uid);

	if (user_is_founder($uid) && $user_info['founder_groupid'] == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER) {
		return true;
	}
	return false;
}

/**
 * 永久删除用户
 * @param $uid
 * @return bool
 */
function user_delete($uid, $is_recycle = false) {
	if (!empty($is_recycle)) {
		pdo_update('users', array('status' => 3) , array('uid' => $uid));
		return true;
	}

	load()->model('cache');
	$founder_groupid = pdo_getcolumn('users', array('uid' => $uid), 'founder_groupid');
	if ($founder_groupid == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER) {
		pdo_update('users', array('owner_uid' => 0), array('owner_uid' => $uid));
		pdo_update('users_group', array('owner_uid' => 0), array('owner_uid' => $uid));
		pdo_update('uni_group', array('owner_uid' => 0), array('owner_uid' => $uid));
	}
	pdo_delete('users', array('uid' => $uid));
	$user_set_account = pdo_getall('uni_account_users', array('uid' => $uid, 'role' => 'owner'));
	if (!empty($user_set_account)) {
		foreach ($user_set_account as $account) {
			cache_build_account_modules($account['uniacid']);
		}
	}
	pdo_delete('uni_account_users', array('uid' => $uid));
	pdo_delete('users_profile', array('uid' => $uid));
	return true;
}

/**
 * 获取单条用户信息，如果查询参数多于一个字段，则查询满足所有字段的用户
 * PS:密码字段不要加密
 * @param array $user_or_uid 要查询的用户字段，可以包括  uid, username, password, status
 * @return array 完整的用户信息
 */
function user_single($user_or_uid) {
	$user = $user_or_uid;
	if (empty($user)) {
		return false;
	}
	if (is_numeric($user)) {
		$user = array('uid' => $user);
	}
	if (!is_array($user)) {
		return false;
	}
	$where = ' WHERE 1 ';
	$params = array();
	if (!empty($user['uid'])) {
		$where .= ' AND `uid`=:uid';
		$params[':uid'] = intval($user['uid']);
	}
	if (!empty($user['username'])) {
		$where .= ' AND `username`=:username';
		$params[':username'] = $user['username'];
	}
	if (!empty($user['email'])) {
		$where .= ' AND `email`=:email';
		$params[':email'] = $user['email'];
	}
	if (!empty($user['status'])) {
		$where .= " AND `status`=:status";
		$params[':status'] = intval($user['status']);
	}
	if (empty($params)) {
		return false;
	}
	$sql = 'SELECT * FROM ' . tablename('users') . " $where LIMIT 1";
	$record = pdo_fetch($sql, $params);
	if (empty($record)) {
		return false;
	}
	if (!empty($user['password'])) {
		$password = user_hash($user['password'], $record['salt']);
		if ($password != $record['password']) {
			return false;
		}
	}
	if (!empty($record['owner_uid'])) {
		$record['vice_founder_name'] = pdo_getcolumn('users', array('uid' => $record['owner_uid']), 'username');
	}
	if($record['type'] == ACCOUNT_OPERATE_CLERK) {
		$clerk = pdo_get('activity_clerks', array('uid' => $record['uid']));
		if(!empty($clerk)) {
			$record['name'] = $clerk['name'];
			$record['clerk_id'] = $clerk['id'];
			$record['store_id'] = $clerk['storeid'];
			$record['store_name'] = pdo_fetchcolumn('SELECT business_name FROM ' . tablename('activity_stores') . ' WHERE id = :id', array(':id' => $clerk['storeid']));
			$record['clerk_type'] = '3';
			$record['uniacid'] = $clerk['uniacid'];
		}
	} else {
		//clerk_type 操作人类型,1: 线上操作 2: 系统后台(公众号管理员和操作员) 3: 店员
		$record['name'] = $record['username'];
		$record['clerk_id'] = $user['uid'];
		$record['store_id'] = 0;
		$record['clerk_type'] = '2';
	}
	return $record;
}

/**
 * 更新用户资料
 * PS:密码字段不需要加密
 * @param array $user 用户的资料数据, 需要的字段可以包括password, status, lastvisit, lastip, remark 必须包括 uid
 * @return boolean
 */
function user_update($user) {
	if (empty($user['uid']) || !is_array($user)) {
		return false;
	}
	$record = array();
	if (!empty($user['username'])) {
		$record['username'] = $user['username'];
	}
	if (!empty($user['password'])) {
		$record['password'] = user_hash($user['password'], $user['salt']);
	}
	if (!empty($user['lastvisit'])) {
		$record['lastvisit'] = (strlen($user['lastvisit']) == 10) ? $user['lastvisit'] : strtotime($user['lastvisit']);
	}
	if (!empty($user['lastip'])) {
		$record['lastip'] = $user['lastip'];
	}
	if (isset($user['joinip'])) {
		$record['joinip'] = $user['joinip'];
	}
	if (isset($user['remark'])) {
		$record['remark'] = $user['remark'];
	}
	if (isset($user['type'])) {
		$record['type'] = $user['type'];
	}
	if (isset($user['status'])) {
		$status = intval($user['status']);
		if (!in_array($status, array(1, 2))) {
			$status = 2;
		}
		$record['status'] = $status;
	}
	if (isset($user['groupid'])) {
		$record['groupid'] = $user['groupid'];
	}
	if (isset($user['starttime'])) {
		$record['starttime'] = $user['starttime'];
	}
	if (isset($user['endtime'])) {
		$record['endtime'] = $user['endtime'];
	}
	if(isset($user['lastuniacid'])) {
		$record['lastuniacid'] = intval($user['lastuniacid']);
	}
	if (empty($record)) {
		return false;
	}
	return pdo_update('users', $record, array('uid' => intval($user['uid'])));
}

/**
 * 计算用户密码
 * @param string $passwordinput 输入字符串
 * @param string $salt 附加字符串
 * @return string
 */
function user_hash($passwordinput, $salt) {
	global $_W;
	$passwordinput = "{$passwordinput}-{$salt}-{$_W['config']['setting']['authkey']}";
	return sha1($passwordinput);
}

/**
 * 获取用户状态说明
 * @return mixed
 */
function user_level() {
	static $level = array(
		'-3' => '锁定用户',
		'-2' => '禁止访问',
		'-1' => '禁止发言',
		'0' => '普通会员',
		'1' => '管理员',
	);
	return $level;
}

/**
 * 获取当前用户可用的用户组
 */
function user_group() {
	global $_W;
	if (user_is_vice_founder()) {
		$condition = array(
			'owner_uid' => $_W['uid'],
		);
	}
	$groups = pdo_getall('users_group', $condition, array('id', 'name', 'package'), 'id', 'id ASC');
	return $groups;
}

/**
 * 获取当前可用的管理组
 * @return Ambigous
 */
function user_founder_group() {
	$groups = pdo_getall('users_founder_group', array(), array('id', 'name', 'package'), 'id', 'id ASC');
	return $groups;
}

/**
 * 获取用户组下详细信息
 * @param  number $groupid 用户组ID
 * @return array
 */
function user_group_detail_info($groupid = 0) {
	$group_info = array();

	$groupid = is_array($groupid) ? 0 : intval($groupid);
	if(empty($groupid)) {
		return $group_info;
	}
	$group_info = pdo_get('users_group', array('id' => $groupid));
	if (empty($group_info)) {
		return $group_info;
	}

	$group_info['package'] = (array)iunserializer($group_info['package']);
	if (!empty($group_info['package'])) {
		$group_info['package_detail'] = uni_groups($group_info['package']);
	}
	return $group_info;
}

/**
 * 获取管理组下详细信息
 * @param int $groupid
 * @return Ambigous|array|string
 */
function user_founder_group_detail_info($groupid = 0) {
	$group_info = array();

	$groupid = is_array($groupid) ? 0 : intval($groupid);
	if(empty($groupid)) {
		return $group_info;
	}
	$group_info = pdo_get('users_founder_group', array('id' => $groupid));
	if (empty($group_info)) {
		return $group_info;
	}

	$group_info['package'] = (array)iunserializer($group_info['package']);
	if (!empty($group_info['package'])) {
		$group_info['package_detail'] = uni_groups($group_info['package']);
	}
	return $group_info;
}

function user_group_and_group_detail($groupid, $foundergroupid) {
	if ($foundergroupid == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER) {
		$groups = user_founder_group();
		$group_info = user_founder_group_detail_info($groupid);
	} else {
		$groups = user_group();
		$group_info = user_group_detail_info($groupid);
	}
	return array(
		'groups' => $groups,
		'group_info' => $group_info
	);
}

/**
 *获取某一用户可用公众号或小程序的详细信息
 *@param number $uid 用户ID
 *@param number $account_type账号类型，空是公众号，4是小程序
 *@return array
 */
function user_account_detail_info($uid) {
	global $_W;
	$account_lists = array();
	$uid = intval($uid);
	if (empty($uid)) {
		return $account_lists;
	}

	$sql = "SELECT b.uniacid, b.role, a.type FROM " . tablename('account'). " AS a LEFT JOIN ". tablename('uni_account_users') . " AS b ON a.uniacid = b.uniacid WHERE a.acid <> 0 AND a.isdeleted <> 1";
	$param = array();
	$founders = explode(',', $_W['config']['setting']['founder']);
	if (!in_array($uid, $founders)) {
		$sql .= " AND b.uid = :uid";
		$param[':uid'] = $uid;
	}
	$account_users_info = pdo_fetchall($sql, $param, 'uniacid');
	foreach ($account_users_info as $uniacid => $account) {
		if ($account['type'] == ACCOUNT_TYPE_OFFCIAL_NORMAL || $account['type'] == ACCOUNT_TYPE_OFFCIAL_AUTH) {
			$app_user_info[$uniacid] = $account;
		} elseif ($account['type'] == ACCOUNT_TYPE_APP_NORMAL) {
			$wxapp_user_info[$uniacid] = $account;
		}
	}
	$wxapps = $wechats = array();
	if (!empty($wxapp_user_info)) {
		$wxapps = pdo_fetchall("SELECT w.name, w.level, w.acid, a.* FROM " . tablename('uni_account') . " a INNER JOIN " . tablename(uni_account_tablename(ACCOUNT_TYPE_APP_NORMAL)) . " w USING(uniacid) WHERE a.uniacid IN (".implode(',', array_keys($wxapp_user_info)).") ORDER BY a.uniacid ASC", array(), 'acid');
	}
	if (!empty($app_user_info)) {
		$wechats = pdo_fetchall("SELECT w.name, w.level, w.acid, a.* FROM " . tablename('uni_account') . " a INNER JOIN " . tablename(uni_account_tablename(ACCOUNT_TYPE_OFFCIAL_NORMAL)) . " w USING(uniacid) WHERE a.uniacid IN (".implode(',', array_keys($app_user_info)).") ORDER BY a.uniacid ASC", array(), 'acid');
	}
	$accounts = array_merge($wxapps, $wechats);
	if (!empty($accounts)) {
		foreach ($accounts as &$account_val) {
			$account_val['thumb'] = tomedia('headimg_'.$account_val['acid']. '.jpg');
			foreach ($account_users_info as $uniacid => $user_info) {
				if ($account_val['uniacid'] == $uniacid) {
					$account_val['role'] = $user_info['role'];
					$account_val['type'] = $user_info['type'];
					if ($user_info['type'] == ACCOUNT_TYPE_APP_NORMAL) {
						$account_lists['wxapp'][$uniacid] = $account_val;
					} elseif ($user_info['type'] == ACCOUNT_TYPE_OFFCIAL_NORMAL || $user_info['type'] == ACCOUNT_TYPE_OFFCIAL_AUTH) {
						$account_lists['wechat'][$uniacid] = $account_val;
					}
				}
			}
		}
		unset($account_val);
	}
	return $account_lists;
}

/**
 * 获取当前用户拥有的所有模块及小程序的标识
 * @param $uid string 用户id
 * @return array 模块列表
 */
function user_modules($uid) {
	global $_W;
	load()->model('module');
	$cachekey = cache_system_key("user_modules:" . $uid);
	$modules = cache_load($cachekey);
	if (empty($modules)) {
		$user_info = user_single(array ('uid' => $uid));

		$system_modules = pdo_getall('modules', array('issystem' => 1), array('name'), 'name');
		if (empty($uid)  || user_is_founder($uid) && !user_is_vice_founder($uid)) {
			$module_list = pdo_getall('modules', array(), array('name'), 'name', array('mid DESC'));
		} elseif (!empty($user_info) && $user_info['type'] == ACCOUNT_OPERATE_CLERK) {
			$clerk_module = pdo_fetch("SELECT p.type FROM " . tablename('users_permission') . " p LEFT JOIN " . tablename('uni_account_users') . " u ON p.uid = u.uid AND p.uniacid = u.uniacid WHERE u.role = :role AND p.uid = :uid", array(':role' => ACCOUNT_MANAGE_NAME_CLERK, ':uid' => $uid));
			if (empty($clerk_module)) {
				return array();
			}
			$module_list = array($clerk_module['type'] => $clerk_module['type']);
		} elseif (!empty($user_info) && empty($user_info['groupid'])) {
			$module_list = $system_modules;
		} else {
			if ($user_info['founder_groupid'] == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER) {
				$user_group_info = user_founder_group_detail_info($user_info['groupid']);
			} else {
				$user_group_info = user_group_detail_info($user_info['groupid']);	
			}
			$packageids = $user_group_info['package'];

			//如果套餐组中包含-1，则直接取全部权限，否则根据情况获取模块权限
			if (!empty($packageids) && in_array('-1', $packageids)) {
				$module_list = pdo_getall('modules', array(), array('name'), 'name', array('mid DESC'));
			} else {
				//此处缺少公众号专属套餐
				$package_group = pdo_getall('uni_group', array('id' => $packageids));
				if (!empty($package_group)) {
					foreach ($package_group as $row) {
						if (!empty($row['modules'])) {
							$row['modules'] = (array)unserialize($row['modules']);
						}
						$package_group_module = array();
						if (!empty($row['modules'])) {
							foreach ($row['modules'] as $modulename => $module) {
								if (!is_array($module)) {
									$modulename = $module;
								}
								$package_group_module[$modulename] = $modulename;
							}
						}
					}
				}
				$module_list = pdo_fetchall("SELECT name FROM ".tablename('modules')." WHERE
										name IN ('" . implode("','", $package_group_module) . "') OR issystem = '1' ORDER BY mid DESC", array(), 'name');
			}
		}
		$module_list = array_keys($module_list);
		$plugin_list = $modules = array();
		if (pdo_tableexists('modules_plugin')) {
			$plugin_list = pdo_getall('modules_plugin', array('name' => $module_list), array());
		}
		$have_plugin_module = array();
		if (!empty($plugin_list)) {
			foreach ($plugin_list as $plugin) {
				$have_plugin_module[$plugin['main_module']][$plugin['name']] = $plugin['name'];
				$module_key = array_search($plugin['name'], $module_list);
				if ($module_key !== false) {
					unset($module_list[$module_key]);
				}
			}
		}
		if (!empty($module_list)) {
			foreach ($module_list as $module) {
				$modules[] = $module;
				if (!empty($have_plugin_module[$module])) {
					foreach ($have_plugin_module[$module] as $plugin) {
						$modules[] = $plugin;
					}
				}
			}
		}
		cache_write($cachekey, $modules);
	}

	$module_list = array();
	if (!empty($modules)) {
		foreach ($modules as $module) {
			$module_info = module_fetch($module);
			if (!empty($module_info)) {
				$module_list[$module] = $module_info;
			}
		}
	}
	return $module_list;
}

/**
 * 获取创始人、副创始人拥有的非系统模块
 * @param $uid
 * @return Ambigous|array
 */
function user_uniacid_modules($uid) {
	if (user_is_vice_founder($uid)) {
		$module_list = user_modules($uid);
		if (empty($module_list)) {
			return $module_list;
		}
		foreach ($module_list as $module => $module_info) {
			if (!empty($module_info['issystem'])) {
				unset($module_list[$module]);
			}
		}
	} else {
		$module_list = pdo_getall('modules', array('issystem' => 0), array(), 'name', 'mid DESC');
	}
	return $module_list;
}

/**
 * 获取用户登录后要跳转的地址
 * @param string $forward 要跳转的地址
 * return string
 */
function user_login_forward($forward = '') {
	global $_W;
	$login_forward = trim($forward);

	if (!empty($forward)) {
		return $login_forward;
	}
	if (user_is_vice_founder()) {
		return url('account/manage', array('account_type' => 1));
	}
	if (!empty($_W['isfounder'])) {
		return url('home/welcome/system');
	}
	if ($_W['user']['type'] == ACCOUNT_OPERATE_CLERK) {
		return url('module/display');
	}

	$login_forward = url('account/display');
	if (!empty($_W['uniacid']) && !empty($_W['account'])) {
		$permission = uni_permission($_W['uid'], $_W['uniacid']);
		if (empty($permission)) {
			return $login_forward;
		}
		if ($_W['account']['type'] == ACCOUNT_TYPE_OFFCIAL_NORMAL || $_W['account']['type'] == ACCOUNT_TYPE_OFFCIAL_AUTH) {
			$login_forward = url('home/welcome');
		} elseif ($_W['account']['type'] == ACCOUNT_TYPE_APP_NORMAL) {
			$login_forward = url('wxapp/display/home');
		}
	}

	return $login_forward;
}
/**
 * 获取公众号所有应用或者小程序所有应用
 * @param string $type 模块类型(account/wxapp)
 * @return array $modules 模块信息
 */
function user_module_by_account_type($type) {
	global $_W;
	$module_list = user_modules($_W['uid']);
	if (!empty($module_list)) {
		foreach ($module_list as $key => &$module) {
			if ((!empty($module['issystem']) && $module['name'] != 'we7_coupon')) {
				unset($module_list[$key]);
			}
			if ($module['wxapp_support'] != 2 && $type == 'wxapp') {
				unset($module_list[$key]);
			}
			if ($module['app_support'] != 2 && $type == 'account') {
				unset($module_list[$key]);
			}
		}
		unset($module);
	}
	return $module_list;
}

function user_invite_register_url($uid = 0) {
	global $_W;
	if (empty($uid)) {
		$uid = $_W['uid'];
	}
	return $_W['siteroot'] . '/index.php?c=user&a=register&owner_uid=' . $uid;
}

/**
 * 添加用户组
 * @param $group_info
 * @return array
 */
function user_save_group($group_info) {
	global $_W;
	$name = trim($group_info['name']);
	if (empty($name)) {
		return error(-1, '用户权限组名不能为空');
	}

	if (!empty($group_info['id'])) {
		$name_exist = pdo_get('users_group', array('id <>' => $group_info['id'], 'name' => $name));
	} else {
		$name_exist = pdo_get('users_group', array('name' => $name));
	}

	if (!empty($name_exist)) {
		return error(-1, '用户权限组名已存在！');
	}

	if (!empty($group_info['package'])) {
		foreach ($group_info['package'] as $value) {
			$package[] = intval($value);
		}
	}
	$group_info['package'] = iserializer($package);
	if (user_is_vice_founder()) {
		$group_info['owner_uid'] = $_W['uid'];
	}

	if (empty($group_info['id'])) {
		pdo_insert('users_group', $group_info);
	} else {
		pdo_update('users_group', $group_info, array('id' => $group_info['id']));
	}

	return error(0, '添加成功');
}

/**
 * 添加副创始人组
 * @param $group_info
 * @return array
 */
function user_save_founder_group($group_info) {
	$name = trim($group_info['name']);
	if (empty($name)) {
		return error(-1, '用户权限组名不能为空');
	}

	if (!empty($group_info['id'])) {
		$name_exist = pdo_get('users_founder_group', array('id <>' => $group_info['id'], 'name' => $name));
	} else {
		$name_exist = pdo_get('users_founder_group', array('name' => $name));
	}

	if (!empty($name_exist)) {
		return error(-1, '用户权限组名已存在！');
	}

	if (!empty($group_info['package'])) {
		foreach ($group_info['package'] as $value) {
			$package[] = intval($value);
		}
	}
	$group_info['package'] = iserializer($package);

	if (empty($group_info['id'])) {
		pdo_insert('users_founder_group', $group_info);
	} else {
		pdo_update('users_founder_group', $group_info, array('id' => $group_info['id']));
	}

	return error(0, '添加成功');
}

/**
 * 用户权限组和副创始人权限组列表格式化
 * @param $lists
 * @return mixed
 */
function user_group_format($lists) {
	if (empty($lists)) {
		return $lists;
	}
	foreach ($lists as $key => $group) {
		$package = iunserializer($group['package']);
		$group['package'] = uni_groups($package);
		if (empty($package)) {
			$lists[$key]['module_nums'] = '系统默认';
			$lists[$key]['wxapp_nums'] = '系统默认';
			continue;
		}
		if (is_array($package) && in_array(-1, $package)) {
			$lists[$key]['module_nums'] = -1;
			$lists[$key]['wxapp_nums'] = -1;
			continue;
		}
		$names = array();
		if (!empty($group['package'])) {
			foreach ($group['package'] as $modules) {
				$names[] = $modules['name'];
				$lists[$key]['module_nums'] = count($modules['modules']);
				$lists[$key]['wxapp_nums'] = count($modules['wxapp']);
			}
		}
		$lists[$key]['packages'] = implode(',', $names);
	}
	return $lists;
}
/**
 * 用户和副创始人列表
 * @param $users
 * @param int $type
 * @return array
 */
function user_list_format($users) {
	if (empty($users)) {
		return array();
	}
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

		$user_role = $user['founder'] = $user['founder_groupid'] == 1 ? true : false;
		$user['uniacid_num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('uni_account_users')." WHERE uid = :uid", array(':uid' => $user['uid']));

		$user['module_num'] =array();
		if ($user['founder_groupid'] == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER) {
			$group = pdo_get('users_founder_group', array('id' => $user['groupid']));
		} else {
			$group = pdo_get('users_group', array('id' => $user['groupid']));
		}
		if ($user_role) {
			$user['maxaccount'] = '不限';
		}
		if (!empty($group)) {
			if (empty($user_role)) {
				$user['maxaccount'] = $group['maxaccount'];
			}
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

		$user['module_num'] = array_unique($user['module_num']);
		$user['module_nums'] = count($user['module_num']) + $system_module_num;
	}
	unset($user);
	return $users;
}

function user_info_save($user, $is_founder_group = false) {
	global $_W;
	if (!preg_match(REGULAR_USERNAME, $user['username'])) {
		return error(-1, '必须输入用户名，格式为 3-15 位字符，可以包括汉字、字母（不区分大小写）、数字、下划线和句点。');
	}
	if (user_check(array('username' => $user['username']))) {
		return error(-1, '非常抱歉，此用户名已经被注册，你需要更换注册名称！');
	}
	if (istrlen($user['password']) < 8) {
		return error(-1, '必须输入密码，且密码长度不得低于8位。');
	}
	if (trim($user['password']) !== trim($user['repassword'])) {
		return error(-1, '两次密码不一致！');
	}
	if (!intval($user['groupid'])) {
		return error(-1, '请选择所属用户组');
	}

	if ($is_founder_group) {
		$group = user_founder_group_detail_info($user['groupid']);
	} else {
		$group = user_group_detail_info($user['groupid']);
	}
	if (empty($group)) {
		return error(-1, '会员组不存在');
	}

	$timelimit = intval($group['timelimit']);
	$timeadd = 0;
	if ($timelimit > 0) {
		$timeadd = strtotime($timelimit . ' days');
	}
	$user['endtime'] = $timeadd;
	$user['owner_uid'] = user_get_uid_byname($user['vice_founder_name']);
	if (user_is_vice_founder()) {
		$user['owner_uid'] = $_W['uid'];
	}
	unset($user['vice_founder_name']);
	unset($user['repassword']);
	$user_add_id = user_register($user);
	if (empty($user_add_id)) {
		return error(-1, '增加失败，请稍候重试或联系网站管理员解决！');
	}
	return array('uid' => $user_add_id);
}