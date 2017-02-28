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
//	$fields = array()
	$user_information = mc_fetch($_W['openid']);
	message(error(0, $user_information), '', 'ajax');
}
if ($op == 'personal_update'){
//	$fields数组形式，所要更改的内容
	$fields = $_GPC['fields'];
	$user_update = mc_update($_W['openid'], $fields);
	message(error(0, $user_update), '', 'ajax');
//	更改成功返回true，失败返回false
}
if ($op == 'balance'){
//	余额消费记录
	$uid = mc_openid2uid($_W['openid']);
	$condition = 'WHERE uniacid = :uniacid AND credittype = :credittype AND uid = :uid AND module = :module';
	$params = array(':uniacid' => $_W['uniacid'], ':credittype' => 'credit2', ':uid' => $uid, 'module' => 'we7_storex');
	$balance = pdo_fetchall('SELECT num,createtime,module,remark FROM ' .tablename('mc_credits_record') .$condition .' ORDER BY id DESC', $params );
	message(error(0, $balance), '', 'ajax');
}
if ($op == 'address_info'){
//	收获地址
	$uid = mc_openid2uid($_W['openid']);
	$address_info = pdo_getall('store_address', array('uid' => $uid));
	message(error(0,$address_info),'','ajax');
}
if ($op == 'address_update'){
	$address_id = intval($_GPC['id']);
	$fields = $_GPC['fields'];
	$result = pdo_update('store_address', $fields, array('id' => $address_id));
	message(error(0, $result), '', 'ajax');
}
if ($op == 'address_delete'){
	$address_id = intval($_GPC['id']);
	$result = pdo_delete('store_address', array('id' => $address_id));
	message(error(0, $result), '', 'ajax');
}
if ($op == 'address_add'){
	$fields = $_GPC['fields'];
	$result = pdo_insert('store_address',$fields );
	message(error(0, $result), '', 'ajax');
}

include $this->template('usercenter');