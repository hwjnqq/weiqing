<?php

defined('IN_IA') or exit('Access Denied');
include IA_ROOT . '/addons/we7_storex/function/function.php';
global $_W, $_GPC;

load()->model('mc');

$ops = array('personal_info', 'personal_update', 'credits_record', 'address_lists', 'current_address', 'address_post', 'address_default', 'address_delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

$_W['openid'] = 'oTKzFjpkpEKpqXibIshcJLsmeLVo';
$uid = mc_openid2uid($_W['openid']);

check_params($op);
if (in_array($op, array('address_post', 'address_default', 'address_delete')) && !empty($_GPC['id'])) {
	$address_info = pdo_get('mc_member_address', array('uniacid' => $_W['uniacid'], 'uid' => $uid, 'id' => intval($_GPC['id'])));
	if(empty($address_info)){
		message(error(-1, '设置失败'), '', 'ajax');
	}
}

if ($op == 'error') {
	message(error(-1, '参数错误'), '', 'ajax');
}
if ($op == 'personal_info') {
	$user_info = mc_fetch($_W['openid']);
	message(error(0, $user_info), '', 'ajax');
}
if ($op == 'personal_update'){
	foreach($_GPC['__input']['fields'] as $key=>$value){
		if(empty($value) || empty($key)){
			message(error(-1, '不能为空'), '', 'ajax');
		}
	}
	$result = mc_update($_W['openid'], $_GPC['__input']['fields']);
	if (!empty($result)) {
		message(error(0, '修改成功'), '', 'ajax');
	} else {
		message(error(-1, '修改失败'), '', 'ajax');
	}
}
if ($op == 'credits_record'){
	$condition = 'WHERE uniacid = :uniacid AND credittype = :credittype AND uid = :uid AND module = :module';
	$params = array(':uniacid' => $_W['uniacid'], ':credittype' => $_GPC['credittype'], ':uid' => $uid, 'module' => 'we7_storex');
	$credits_record = pdo_fetchall('SELECT num, createtime , module FROM ' .tablename('mc_credits_record') .$condition .' ORDER BY id DESC', $params);
	if (!empty($credits_record)) {
		foreach ($credits_record as &$data) {
			if ($data['num'] > 0) {
				$data['remark'] = '充值' . $data['num'] . '元';
			} else {
				$data['remark'] = '消费' . - $data['num'] . '元';
			}
			$data['createtime'] = date('Y-m-d h:i:s', $data['createtime']);
		}
	}
	message(error(0, $credits_record), '', 'ajax');
}
if ($op == 'address_lists'){
	$address_info = pdo_getall('mc_member_address', array('uid' => $uid, 'uniacid' => $_W['uniacid']));
	message(error(0, $address_info), '', 'ajax');
}
if ($op == 'current_address'){
	if(empty($_GPC['id'])){
		message(error(-1, '获取失败'), '', 'ajax');
	}
	$current_info = pdo_get('mc_member_address', array('id' => intval($_GPC['id'])));
	message(error(0, $current_info), '', 'ajax');
}
if ($op == 'address_post'){
	$address_info = $_GPC['__input']['fields'];
	if (empty($address_info['username']) || empty($address_info['zipcode']) || empty($address_info['province']) || empty($address_info['city'])  || empty($address_info['district']) || empty($address_info['address'])){
		message(error(-1, '请填写正确的信息'), '', 'ajax');
	}
	if (!preg_match(REGULAR_MOBILE, $address_info['mobile'])){
		message(error(-1, '手机号格式不正确'), '', 'ajax');
	}
	if(!empty($_GPC['id'])){
		$result = pdo_update('mc_member_address', $address_info, array('id' => intval($_GPC['id'])));
		message(error(0, $result), '', 'ajax');
	}else{
		$address_info['uid'] = $uid;
		$address_info['uniacid'] = $_W['uniacid'];
		$result = pdo_insert('mc_member_address', $address_info);
		message(error(0, $result), '', 'ajax');
	}
}
if ($op == 'address_default'){
	$address_id = pdo_getcolumn('mc_member_address', array('isdefault' => '1', 'uid' => $uid), 'id');
	$default_result = pdo_update('mc_member_address', array('isdefault' => '0'), array('id' => $address_id));
	$result = pdo_update('mc_member_address', array('isdefault' => '1'), array('id' => intval($_GPC['id'])));
	message(error(0, '设置成功'), '', 'ajax');

}
if ($op == 'address_delete'){
	$result = pdo_delete('mc_member_address', array('id' => intval($_GPC['id'])));
	message(error(0, '删除成功'), '', 'ajax');
}


include $this->template('usercenter');