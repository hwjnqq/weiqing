<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('personal_info', 'personal_update', 'credits_record', 'address_lists', 'current_address', 'address_post', 'address_default', 'address_delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

check_params();
$uid = mc_openid2uid($_W['openid']);
if (in_array($op, array('address_post', 'address_default', 'address_delete')) && !empty($_GPC['id'])) {
	$address_info = pdo_get('mc_member_address', array('uniacid' => $_W['uniacid'], 'uid' => $uid, 'id' => intval($_GPC['id'])));
	if(empty($address_info)){
		message(error(-1, '设置失败'), '', 'ajax');
	}
}

if ($op == 'personal_info') {
	$user_info = mc_fetch($_W['openid']);
	message(error(0, $user_info), '', 'ajax');
}
if ($op == 'personal_update'){
	if(empty($_GPC['__input']['fields'])){
		message(error(-1, '不能为空'), '', 'ajax');
	}
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
	$credits = array();
	$credits_record = pdo_getall('mc_credits_record', array('uniacid' => $_W['uniacid'], 'credittype' => $_GPC['credittype'], 'uid' => $uid, 'module' => 'we7_storex'), array('num', 'createtime', 'module'), 'num', 'id DESC');
	if (!empty($credits_record)) {
		foreach ($credits_record as $data) {
			$data['createtime'] = date('Y-m-d h:i:s', $data['createtime']);
			$offset = $_GPC['credittype'] == 'credit2' ? '元' : '积分';
			if ($data['num'] > 0) {
				$data['remark'] = '充值' . $data['num'] . $offset;
				$credits['recharge'][] = $data;
			} else {
				$data['remark'] = '消费' . - $data['num'] . $offset;
				$credits['consume'][] = $data;
			}
		}
	}
	message(error(0, $credits), '', 'ajax');
}
if ($op == 'address_lists'){
	$address_info = pdo_getall('mc_member_address', array('uid' => $uid, 'uniacid' => $_W['uniacid']));
	message(error(0, $address_info), '', 'ajax');
}
if ($op == 'current_address'){
	$current_info = pdo_get('mc_member_address', array('id' => intval($_GPC['id']), 'uid' => $uid, 'uniacid' => $_W['uniacid']));
	message(error(0, $current_info), '', 'ajax');
}
if ($op == 'address_post'){
	$address_info = $_GPC['__input']['fields'];
	if (empty($address_info['username']) || empty($address_info['zipcode']) || empty($address_info['province']) || empty($address_info['city'])  || empty($address_info['district']) || empty($address_info['address'])){
		message(error(-1, '请填写正确的信息'), '', 'ajax');
	}
	if (empty($address_info['mobile'])){
		message(error(-1, '手机号码不能为空'), '', 'ajax');
	}
	if (!preg_match(REGULAR_MOBILE, $address_info['mobile'])){
		message(error(-1, '手机号码格式不正确'), '', 'ajax');
	}
	unset($address_info['id']);
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
	$default_result = pdo_update('mc_member_address', array('isdefault' => '0'), array('uid' => $uid, 'uniacid' => $_W['uniacid']));
	$result = pdo_update('mc_member_address', array('isdefault' => '1'), array('id' => intval($_GPC['id'])));
	message(error(0, '设置成功'), '', 'ajax');

}
if ($op == 'address_delete'){
	$result = pdo_delete('mc_member_address', array('id' => intval($_GPC['id'])));
	message(error(0, '删除成功'), '', 'ajax');
}
