<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');


$_W['page']['title'] = '编辑用户 - 用户管理';
uni_user_permission_check('system_user_display');

$do = $_GPC['do'];
$dos = array('edit_base', 'edit_modules_tpl', 'edit_account');
$do = in_array($do, $dos) ? $do: 'edit_base';

load()->model('user');
$uid = intval($_GPC['uid']);
$user = user_single($uid);
if (empty($user)) {
	message('访问错误, 未找到该操作员.', url('user/display'), 'error');
}else {
	if($user['status'] == 1) message('访问错误，该用户未审核通过，请先审核通过再修改！', url('user/display/check_display'), 'error');
	if($user['status'] == 3) message('访问错误，该用户已被禁用，请先启用再修改！', url('user/display/recycle_display'), 'error');
}
$founders = explode(',', $_W['config']['setting']['founder']);
$profile = pdo_fetch('SELECT * FROM '.tablename('users_profile').' WHERE `uid` = :uid LIMIT 1',array(':uid' => $uid));
if(!empty($profile)) $profile['avatar'] = tomedia($profile['avatar']);

//编辑用户基础信息
if ($do == 'edit_base') {
	$user['last_visit'] = date('Y-m-d H:i:s', $user['lastvisit']);
	if(!empty($profile)) {
		$profile['reside'] = array(
			'province' => $profile['resideprovince'],
			'city' => $profile['residecity'],
			'district' => $profile['residedist']
		);
		$profile['birth'] = array(
			'year' => $profile['birthyear'],
			'month' => $profile['birthmonth'],
			'day' => $profile['birthday'],
		);
		$profile['resides'] = $profile['resideprovince'] . $profile['residecity'] . $profile['residedist'] ;
		$profile['births'] = $profile['birthyear'] . '年' . $profile['birthmonth'] . '月' . $profile['birthday'] .'日' ;
	}
	template('user/edit-base');
}
if($do == 'edit_modules_tpl') {
	if($_W['isajax'] && $_W['ispost']) {
		$post = $_GPC['__input'];
		if(intval($post['groupid']) == $user['groupid']){
			message('0' , 'ajax', 'success');
		}
		if(!empty($post['type']) && !empty($post['groupid'])) {
			$data['uid'] = $uid;
			$data[$post['type']] = intval($post['groupid']);
			if(user_update($data)) {
				$group_info = group_detail_info($post['groupid']);
				message($group_info, 'ajax', 'success');
			}else {
				message('1', 'ajax', 'error');
			}
		}else {
			message('-1', 'ajax', 'error');
		}
	}
	$groups = pdo_getall('users_group', array(), array('id', 'name'), 'id');
	$group_info = group_detail_info($user['groupid']);

	template('user/edit-modules-tpl');
}

if($do == 'edit_account') {
	echo 'account';
	template('user/edit-account');
}