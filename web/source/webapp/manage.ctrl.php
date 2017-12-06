<?php
/**
 * pc列表
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->classs('validator');
load()->model('webapp');
$account_info = permission_user_account_num();

if($do == 'create') {
	if(!checksubmit()) {
		echo '非法提交';
		return;
	}
	if (!webapp_can_create($_W['uid'])) {
		itoast('创建PC个数已满', url('webapp/manage/list'));
	}
	$data = array(
		'name'=>$_GPC['name'],
		'description'=>$_GPC['description']
	);

	$webapp = table('webapp');
	$uniacid = $webapp->create($data, $_W['uid']);
	if($uniacid){
		itoast('创建成功', url('webapp/manage/list'));
	}
}

if($do == 'createview') {
	if(!webapp_can_create($_W['uid'])) { //没有权限创建
		itoast('', url('webapp/manage/list'));
	}
	template('webapp/create');
}

/* pc 列表*/
if($do == 'list') {

	$pindex = max(1, intval($_GPC['page']));
	$psize = 15;

	$webapp = table('webapp');
	list($list, $total) = $webapp->webapplist($_W['uid'], $pindex, $psize);
	$pager = pagination($total, $pindex, $psize);

	foreach ($list as &$item) {
		$item['logo'] = tomedia('headimg_'.$account['acid']. '.jpg').'?time='.time();
		$item['switchurl'] = wurl('webapp/home/switch', array('uniacid' => $item['uniacid']));
	}
	template('webapp/list');
}