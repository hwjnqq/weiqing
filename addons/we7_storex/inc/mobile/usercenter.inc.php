<?php

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
load()->model('mc');
// paycenter_check_login();
// $ops = array('display', 'post', 'delete');
// $op = in_array($op, $op) ? $op : 'display';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'display';

$uid = mc_openid2uid($_W['openid']);

if ($op == 'personal_info') {
	$user_info = mc_fetch($_W['openid']);
	message(error(0, $user_info), '', 'ajax');
}
if ($op == 'personal_update'){
	foreach($_GPC['fields'] as $key => $value){
		if(empty($value) || empty($key)){
			message(error(-1, '不能为空' ), '', 'ajax');
		}
	}
	$user_update = mc_update($_W['openid'], $_GPC['fields']);
	message(error(0, $user_update), '', 'ajax');
}
if ($op == 'credits_record'){
	$credittype = $_GPC['credittype'];
	$condition = 'WHERE uniacid = :uniacid AND credittype = :credittype AND uid = :uid AND module = :module';
	$params = array(':uniacid' => $_W['uniacid'], ':credittype' => $credittype, ':uid' => $uid, 'module' => 'we7_storex');
	$credits_record = pdo_fetchall('SELECT num, createtime , module, remark FROM ' .tablename('mc_credits_record') .$condition .' ORDER BY id DESC', $params );
	message(error(0, $credits_record), '', 'ajax');
}
if ($op == 'address_info'){
	$address_info = pdo_getall('store_address', array('uid' => $uid));
	message(error(0,$address_info),'','ajax');
}
if ($op == 'post'){
	$address_id = intval($_GPC['id']);
	if(!empty($address_id)){
		$fields = $_GPC['fields'];
		$result = pdo_update('store_address', $fields, array('id' => $address_id));
		message(error(0, $result), '', 'ajax');
	}else{
		$fields = $_GPC['fields'];
		$result = pdo_insert('store_address',$fields );
		message(error(0, $result), '', 'ajax');
	}

}
if ($op == 'address_delete'){
	$address_id = intval($_GPC['id']);
	$result = pdo_delete('store_address', array('id' => $address_id));
	message(error(0, $result), '', 'ajax');
}


include $this->template('usercenter');