<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
if (!check_ims_version()) {
	message('系统版本太低，请升级系统到1.0以上版本', '', 'error');
}

load()->model('mc');
load()->model('module');

$ops = array('display', 'edit', 'delete', 'deleteall', 'showall', 'status', 'clerkcommentlist', 'pay_record');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_W['wn_storex']['store_info']['id']);
$current_module_permission = module_permission_fetch($this->module['name']);
if (!empty($current_module_permission)) {
	foreach ($current_module_permission as $key => $permission) {
		$permission_name[$permission['permission']] = $permission['title'];
	}
}
$user_permissions = pdo_getall('users_permission', array('uniacid' => $_W['uniacid'], 'type' => $this->module['name'], 'uid <>' => ''), '', 'uid');
$uids = !empty($user_permissions) && is_array($user_permissions) ? array_keys($user_permissions) : array();
$users_lists = array();
if (!empty($uids)) {
	$users_lists = pdo_getall('users', array('uid' => $uids), '', 'uid');
}
if (!empty($user_permissions)) {
	foreach ($user_permissions as $key => &$permission) {
		$permission['permission'] = explode('|', $permission['permission']);
		foreach ($permission['permission'] as $k => $val) {
			$permission['permission'][$val] = $permission_name[$val];
			unset($permission['permission'][$k]);
		}
		$permission['user_info'] = $users_lists[$key];
	}
	unset($permission);
}
$clerk_list = pdo_getall('storex_clerk', array('weid' => $_W['uniacid'], 'storeid' => $storeid), '', 'userid');
$available_user = $user_permissions;
foreach ($available_user as $key => $value) {
	if (!empty($clerk_list[$key])) {
		unset($available_user[$key]);
	}
}

if ($op == 'display') {
	$sql = "";
	$params = array();
	if (!empty($_GPC['realname'])) {
		$sql .= ' AND `realname` LIKE :realname';
		$params[':realname'] = "%{$_GPC['realname']}%";
	}
	if (!empty($_GPC['mobile'])) {
		$sql .= ' AND `mobile` LIKE :mobile';
		$params[':mobile'] = "%{$_GPC['mobile']}%";
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$list = pdo_getall('storex_clerk', array('weid' => $_W['uniacid'], 'storeid' => $storeid, 'realname LIKE' => "%{$_GPC['realname']}%", 'mobile LIKE' => "%{$_GPC['mobile']}%"), array(), '', 'id DESC', ($pindex - 1) * $psize . ',' . $psize);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_clerk') . " WHERE `weid` = '{$_W['uniacid']}' AND `storeid` = '{$storeid}' $sql", $params);
	$pager = pagination($total, $pindex, $psize);
	include $this->template('store/shop_clerk');
}

if ($op == 'edit') {
	$user_info = user_single($_GPC['uid']);
	$id = intval($_GPC['id']);
	$user_permissions[$uid]['permission'];
	if (!empty($id)) {
		$item = pdo_get('storex_clerk', array('id' => $id, 'weid' => $_W['uniacid']));

		if (empty($item)) {
			message('用户不存在或是已经删除', '', 'error');
		}
	}
	if (!empty($item['from_user'])) {
		$uid = mc_openid2uid($item['from_user']);
		$address_info = pdo_getall('mc_member_address', array('uid' => $uid, 'uniacid' => $_W['uniacid']), '', '', 'isdefault DESC');
	}
	$stores = pdo_getall('storex_bases', array('weid' => intval($_W['uniacid'])), array('id', 'title', 'store_type', 'thumb'), 'id');
	if (!empty($stores) && is_array($stores)) {
		foreach ($stores as &$value) {
			$value['thumb'] = tomedia($value['thumb']);
		}
		unset($value);
	}
	if (checksubmit('submit')) {
		if (!empty($user_permissions[$_GPC['uid']]['permission']) && is_array($user_permissions[$_GPC['uid']]['permission'])) {
			$permissions = array_keys($user_permissions[$_GPC['uid']]['permission']);
		}
		$insert = array(
			'weid' => intval($_W['uniacid']),
			'userid' => intval($_GPC['uid']),
			'storeid' => $storeid,
			'username' => trim($_GPC['username']),
			'realname' => trim($_GPC['realname']),
			'mobile' => $_GPC['mobile'],
			'status' => intval($_GPC['status']),
			'from_user' => $_GPC['from_user'],
			'password' => $_GPC['password'],
			'permission' => is_array($permissions) ? implode('|', $permissions) : '',
		);
		if (empty($insert['from_user'])) {
			message('店员的微信openid不能为空', '', 'info');
		}
		$password_exist = pdo_get('storex_clerk', array('weid' => $_W['uniacid'], 'storeid' => $storeid, 'password' => $_GPC['password']), array('id'));
		if (!empty($password_exist)) {
			message('店员卡券核销密码重复', '', 'error');
		}
		$fans_info = pdo_get('mc_mapping_fans', array('openid' => $insert['from_user'], 'uniacid' => $_W['uniacid']));
		if (empty($fans_info)) {
			message('关注公众号后才能成为店员', referer(), 'info');
		}
		if (empty($id)) {
			$insert['createtime'] = time();
			pdo_insert('storex_clerk', $insert);
		} else {
			pdo_update('storex_clerk', $insert, array('id' => $id, 'weid' => intval($_W['uniacid'])));
		}
		message('用户信息更新成功', $this->createWebUrl('shop_clerk', array('storeid' => $storeid)), 'success');
	}
	include $this->template('store/shop_clerk_form');
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	pdo_delete('storex_clerk', array('id' => $id));
	message('删除成功', referer(), 'success');
}

if ($op == 'deleteall') {
	foreach ($_GPC['idArr'] as $k => $id) {
		$id = intval($id);
		pdo_delete('storex_clerk', array('id' => $id));
	}
	message(error(0, '操作成功'), '', 'ajax');
}

if ($op == 'showall') {
	if ($_GPC['show_name'] == 'showall') {
		$show_status = 1;
	} else {
		$show_status = 0;
	}
	foreach ($_GPC['idArr'] as $k => $id) {
		$id = intval($id);
		if (!empty($id)) {
			pdo_update('storex_clerk', array('status' => $show_status), array('id' => $id));
		}
	}
	message(error(0, '操作成功'), '', 'ajax');
}

if ($op == 'status') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		message('参数错误', '', 'error');
	}
	$clerk = pdo_update('storex_clerk', array('status' => $_GPC['status']), array('id' => $id));
	if ($clerk == false) {
		message('操作失败', '', 'error');
	} else {
		message('设置成功', referer(), 'success');
	}
}

if ($op == 'pay_record') {
	$id = intval($_GPC['id']);
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$clerk = pdo_get('storex_clerk', array('id' => $id), array('username'));
	$pay_record = pdo_getall('storex_clerk_pay', array('clerkid' => $id, 'uniacid' => $_W['uniacid']), array(), '', 'time DESC', ($pindex - 1) * $psize . ',' . $psize);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_clerk_pay') . " WHERE clerkid={$id} AND uniacid={$_W['uniacid']}");
	$pager = pagination($total, $pindex, $psize);
	include $this->template('store/shop_clerk');
}