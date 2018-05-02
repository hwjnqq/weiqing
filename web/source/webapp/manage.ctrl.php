<?php
/**
 * pc列表
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');


load()->model('webapp');
$account_info = permission_user_account_num();

$do = safe_gpc_belong($do, array('create', 'list', 'create_display'), 'list');

if($do == 'create') {
	if(!checksubmit()) {
		echo '非法提交';
		return;
	}
	if (!webapp_can_create($_W['uid'])) {
		itoast('创建PC个数已满', url('account/display', array('type' => WEBAPP_TYPE_SIGN)));
	}
	$data = array(
		'name' => safe_gpc_string($_GPC['name']),
		'description' => safe_gpc_string($_GPC['description'])
	);

	$webapp = table('webapp');
	$uniacid = $webapp->createWebappInfo($data, $_W['uid']);
	if($uniacid){
		itoast('创建成功', url('account/display', array('type' => WEBAPP_TYPE_SIGN)));
	}
}

if($do == 'create_display') {
	if(!webapp_can_create($_W['uid'])) {
		itoast('', url('account/display', array('type' => WEBAPP_TYPE_SIGN)));
	}
	template('webapp/create');
}