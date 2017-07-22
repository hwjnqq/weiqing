<?php
/**
 * 公众号列表
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->func('file');
load()->model('user');
$dos = array('delete', 'display');
$do = in_array($_GPC['do'], $dos)? $do : 'display';

$_W['page']['title'] = $account_typename . '列表 - ' . $account_typename;
//模版调用，显示该用户所在用户组可添加的主公号数量，已添加的数量，还可以添加的数量
$account_info = uni_user_account_permission();

if ($do == 'display') {
	header("Location: " . url('account/post', array('account_type' => ACCOUNT_TYPE_APP_NORMAL)));
}

if ($do == 'delete') {
	$uid = $_W['uid'];
	//只有创始人、主管理员才有权限停用公众号
	$state = uni_permission($uid, $_W['uniacid']);
	if ($state != ACCOUNT_MANAGE_NAME_OWNER && $state != ACCOUNT_MANAGE_NAME_FOUNDER) {
		itoast('无权限操作！', url('account/manage'), 'error');
	}
	if (!empty($_W['uniacid'])) {
		$account = pdo_get('uni_account', array('uniacid' => $_W['uniacid']));
		if (empty($account)) {
			itoast('抱歉，帐号不存在或是已经被删除', url('account/post', array('account_type' => ACCOUNT_TYPE)), 'error');
		}
		$state = uni_permission($uid, $_W['uniacid']);
		if($state != ACCOUNT_MANAGE_NAME_FOUNDER && $state != ACCOUNT_MANAGE_NAME_OWNER) {
			itoast('没有该'. ACCOUNT_TYPE_NAME . '操作权限！', url('account/post', array('account_type' => ACCOUNT_TYPE)), 'error');
		}
		$state = uni_permission($_W['uid'], $_W['uniacid']);
		account_delete($_W['acid']);
	}
	itoast('卸载成功', url('account/post', array('account_type' => ACCOUNT_TYPE)), 'success');
}