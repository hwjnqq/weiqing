<?php

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
load()->model('mc');
// paycenter_check_login();
// $ops = array('display', 'post', 'delete');
// $op = in_array($op, $op) ? $op : 'display';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'display';

if ($op == 'user_credit') {
	// $user_info = pdo_get('mc_members', array('uid' => $_GPC['uid']));
	$user_info = mc_fetch($_W['openid']);
	message(error(-1, $user_info), '', 'ajax');
	// return result(0, '获取酒店成功', $_W['openid']);
}
// return result(0, '获取酒店成功', array('1', '2', '3'));
//获取个人信息
if ($op == 'personal_info') {
	$user_information = mc_fetch($_W['openid']);
	message(error(0, $user_information), '', 'ajax');
}
if ($op == 'personal_update'){
//	$fields数组形式，所要更改的内容
	$user_update = mc_update($_W['openid'], $fields);
	message(error(0, $user_update), '', 'ajax');
//	更改成功返回true，失败返回false
}
if ($op == 'info'){

}

include $this->template('usercenter');