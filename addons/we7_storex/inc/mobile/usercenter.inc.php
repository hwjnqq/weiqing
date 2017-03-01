<?php

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
load()->model('mc');
// paycenter_check_login();
// $ops = array('display', 'post', 'delete');
// $op = in_array($op, $op) ? $op : 'display';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'display';

$_W['openid'] = 'oTKzFjpkpEKpqXibIshcJLsmeLVo';
$uid = mc_openid2uid($_W['openid']);

if ($op == 'personal_info') {
	$user_info = mc_fetch($_W['openid']);
	message(error(0, $user_info), '', 'ajax');
}
if ($op == 'personal_update'){
	foreach($_GPC['fields'] as $key => $value){
		if(empty($value) || empty($key)){
			message(error(-1, '不能为空'), '', 'ajax');
		}
	}
	$result = mc_update($_W['openid'], $_GPC['fields']);
	if (!empty($result)) {
		message(error(0, '修改成功'), '', 'ajax');
	} else {
		message(error(-1, '修改失败'), '', 'ajax');
	}
}
if ($op == 'credits_record'){
	$condition = 'WHERE uniacid = :uniacid AND credittype = :credittype AND uid = :uid AND module = :module';
	$params = array(':uniacid' => $_W['uniacid'], ':credittype' => $_GPC['credittype'], ':uid' => $uid, 'module' => 'we7_storex');
	$credits_record = pdo_fetchall('SELECT num, createtime , module, remark FROM ' .tablename('mc_credits_record') .$condition .' ORDER BY id DESC', $params);
//	$credits_record = pdo_getall('mc_credits_record', array('uniacid' => $_W['uniacid'], 'credittype' => $_GPC['credittype'], 'uid' => $uid, 'module' => 'we7_storex'), array('num', 'createtime', 'module','remark' ), '', 'id DESC');
	message(error(0, $credits_record), '', 'ajax');
}
if ($op == 'address_lists'){
	$address_info = pdo_getall('mc_member_address', array('uid' => $uid));
	message(error(0,$address_info),'','ajax');
}
if ($op == 'post'){
	$address_id = intval($_GPC['id']);
	$address_info = $_GPC['__input']['fields'];
	if (empty($address_info['username']) || empty($address_info['zipcode']) || empty($address_info['province']) || empty($address_info['city']) || empty($address_info['city']) || empty($address_info['district']) || empty($address_info['address'])){
		message(error(-1, 请填写正确的信息), '', 'ajax');
	}
	if (!preg_match(REGULAR_MOBILE, $address_info['mobile'])){
		message(error(-1, 手机号格式不正确), '', 'ajax');
	}
	if(!empty($address_id)){
		$result = pdo_update('mc_member_address', $address_info, array('id' => $address_id));
		message(error(0, $result), '', 'ajax');
	}else{
		$result = pdo_insert('mc_member_address', $address_info);
		message(error(0, $result), '', 'ajax');
	}
}
if ($op == 'address_default'){
	if(empty($_GPC['id']) || empty($_GPC['__input']['fields'])){
		message(error(-1, 设置失败), '', 'ajax');
	}
	$address_id = pdo_getcolumn('mc_member_address', array('isdefault' => '1', 'uid' => $uid), 'id');
	$default_result = pdo_update('mc_member_address', array('isdefault' => '0'), array('id' => $address_id));
	$result = pdo_update('mc_member_address', $_GPC['fields'], array('id' => $_GPC['id']));
	message(error(0, $result), '', 'ajax');
}
if ($op == 'address_delete'){
	$address_id = intval($_GPC['id']);
	if(empty($address_id)){
		message(error(-1, 删除失败), '', 'ajax');
	}
	$result = pdo_delete('mc_member_address', array('id' => $address_id));
	message(error(0, $result), '', 'ajax');
}


include $this->template('usercenter');