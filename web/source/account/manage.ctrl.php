<?php
/**
 * 公众号列表
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->func('file');
load()->model('user');
$dos = array('display', 'delete');
$do = in_array($_GPC['do'], $dos)? $do : 'display';


$_W['page']['title'] = $account_typename . '列表 - ' . $account_typename;
//模版调用，显示该用户所在用户组可添加的主公号数量，已添加的数量，还可以添加的数量
$account_info = uni_user_account_permission();

if ($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$start = ($pindex - 1) * $psize;

	$condition = array();
	$keyword = trim($_GPC['keyword']);

	$type_condition = array(
		ACCOUNT_TYPE_APP_NORMAL => array(ACCOUNT_TYPE_APP_NORMAL),
		ACCOUNT_TYPE_OFFCIAL_NORMAL => array(ACCOUNT_TYPE_OFFCIAL_NORMAL, ACCOUNT_TYPE_OFFCIAL_AUTH),
		ACCOUNT_TYPE_OFFCIAL_AUTH => array(ACCOUNT_TYPE_OFFCIAL_NORMAL, ACCOUNT_TYPE_OFFCIAL_AUTH),
	);
	$condition['type'] = $type_condition[ACCOUNT_TYPE];

	if (!empty($keyword)) {
		$condition['name'] = trim($_GPC['keyword']);
	}
	if(isset($_GPC['letter']) && strlen($_GPC['letter']) == 1) {
		$condition['letter'] = trim($_GPC['letter']);
	}
	$account_lists = uni_account_list($condition, array($pindex, $psize), 'manage', uni_account_tablename(ACCOUNT_TYPE));
	$list = $account_lists['list'];
	$total = $account_lists['total'];
	$pager = pagination($total, $pindex, $psize);
	template('account/manage-display' . ACCOUNT_TYPE_TEMPLATE);
}
if ($do == 'delete') {
	$uniacid = intval($_GPC['uniacid']);
	$acid = intval($_GPC['acid']);
	$uid = $_W['uid'];
	$type = intval($_GPC['type']);
	//只有创始人、主管理员才有权限停用公众号
	$state = uni_permission($uid, $uniacid);
	if ($state != ACCOUNT_MANAGE_NAME_OWNER && $state != ACCOUNT_MANAGE_NAME_FOUNDER) {
		itoast('无权限操作！', url('account/manage'), 'error');
	}
	if (!empty($acid) && empty($uniacid)) {
		$account = account_fetch($acid);
		if (empty($account)) {
			itoast('子公众号不存在或是已经被删除', '', '');
		}
		$uniaccount = uni_fetch($account['uniacid']);
		if ($uniaccount['default_acid'] == $acid) {
			itoast('默认子公众号不能删除', '', '');
		}
		pdo_update('account', array('isdeleted' => 1), array('acid' => $acid));
		itoast('删除子公众号成功！您可以在回收站中回复公众号', referer(), 'success');
	}
	
	if (!empty($uniacid)) {
		$account = pdo_get('uni_account', array('uniacid' => $uniacid));
		if (empty($account)) {
			itoast('抱歉，帐号不存在或是已经被删除', url('account/manage', array('account_type' => ACCOUNT_TYPE)), 'error');
		}
		$state = uni_permission($uid, $uniacid);
		if($state != ACCOUNT_MANAGE_NAME_FOUNDER && $state != ACCOUNT_MANAGE_NAME_OWNER) {
			itoast('没有该'. ACCOUNT_TYPE_NAME . '操作权限！', url('account/manage', array('account_type' => ACCOUNT_TYPE)), 'error');
		}
		pdo_update('account', array('isdeleted' => 1), array('uniacid' => $uniacid));
		if($_GPC['uniacid'] == $_W['uniacid']) {
			isetcookie('__uniacid', '');
		}
		cache_delete("uniaccount:{$uniacid}");
		cache_delete("unisetting:{$uniacid}");
	}
	itoast('停用成功！，您可以在回收站中恢复', url('account/manage', array('account_type' => ACCOUNT_TYPE)), 'success');
}