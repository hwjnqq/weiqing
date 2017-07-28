<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('personal_info', 'personal_update', 'credits_record', 'address_lists', 'current_address', 'address_post', 'address_default', 'address_delete', 'extend_switch', 'credit_password', 'check_password_lock', 'set_credit_password');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

check_params();
load()->model('mc');
mload()->model('card');
$uid = mc_openid2uid($_W['openid']);

if (in_array($op, array('address_post', 'address_default', 'address_delete')) && !empty($_GPC['id'])) {
	$address_info = pdo_get('mc_member_address', array('uniacid' => $_W['uniacid'], 'uid' => $uid, 'id' => intval($_GPC['id'])));
	if (empty($address_info)) {
		wmessage(error(-1, '设置失败'), '', 'ajax');
	}
}

if ($op == 'extend_switch') {
	$extend_switch = extend_switch_fetch();
	$notices = card_notices();
	$notice_unread_num = 0;
	if (!empty($notices)) {
		foreach ($notices as $val) {
			if (empty($val['read_status'])) {
				$notice_unread_num++;
			}
		}
	}
	if (check_ims_version()) {
		$plugin_list = get_plugin_list();
		$extend_switch['plugin_list'] = $plugin_list;
	}
	$extend_switch['notice_unread_num'] = $notice_unread_num;
	wmessage(error(0, $extend_switch), '', 'ajax');
}

if ($op == 'personal_info') {
	$user_info = mc_fetch($_W['openid']);
	$storex_clerk = pdo_get('storex_clerk', array('weid' => intval($_W['uniacid']), 'from_user' => trim($_W['openid']), 'status !=' => -1), array('id', 'from_user'));
	if (!empty($storex_clerk)) {
		$user_info['clerk'] = 1;
	} else {
		$user_info['clerk'] = 0;
	}
	$card_info = card_setting_info();
	$user_info['mycard'] = pdo_get('storex_mc_card_members', array('uniacid' => intval($_W['uniacid']), 'uid' => $uid));
	if (!empty($user_info['mycard'])) {
		$user_info['mycard']['is_receive'] = 1;//是否领取,1已经领取，2没有领取
		$user_info['mycard']['fields'] = iunserializer($user_info['mycard']['fields']);
		$user_info['mycard']['group'] = array();
		$user_info['mycard']['group'] = card_group_id($uid);
	} else {
		$user_info['mycard']['is_receive'] = 2;
	}
	if (!empty($card_info)) {
		$show_fields = array('title', 'color', 'background', 'logo', 'description');
		foreach ($show_fields as $val) {
			if (!empty($card_info[$val])) {
				$user_info['mycard'][$val] = $card_info[$val];
			}
			if ($val == 'background') {
				if ($card_info[$val]['background'] == 'user') {
					$user_info['mycard'][$val]['image'] = $user_info['mycard'][$val]['image'];
				} else {
					$png = $user_info['mycard'][$val]['image'];
					$png = !empty($png) ? $png : '1';
					$user_info['mycard'][$val]['image'] = tomedia("addons/wn_storex/template/style/img/card/" . $png . ".png");
				}
			}
		}
		if (!empty($card_info['params']['cardBasic']['params'])) {
			$user_info['mycard']['card_level'] = $card_info['params']['cardBasic']['params']['card_level'];
			$user_info['mycard']['card_label'] = $card_info['params']['cardBasic']['params']['card_label'];
		}
		$user_info['mycard']['cardNums'] = array(
			'status' => 0,
		);
		if (!empty($card_info['params']['cardNums']) && $card_info['params']['cardNums']['params']['nums_status'] == 1) {
			$cardNums = $card_info['params']['cardNums']['params'];
			$user_info['mycard']['cardNums']['status'] = $cardNums['nums_status'];
			$user_info['mycard']['cardNums']['text'] = $cardNums['nums_text'];
			$user_info['mycard']['cardNums']['nums'] = $user_info['mycard']['nums'];
		}
		$user_info['mycard']['cardTimes'] = array(
			'status' => 0,
		);
		if (!empty($card_info['params']['cardTimes']) && $card_info['params']['cardTimes']['params']['times_status'] == 1) {
			$times_status = $card_info['params']['cardTimes']['params'];
			$user_info['mycard']['cardTimes']['status'] = $times_status['times_status'];
			$user_info['mycard']['cardTimes']['text'] = $times_status['times_text'];
			$user_info['mycard']['cardTimes']['endtime'] = $user_info['mycard']['endtime'];
		}
	}
	$member = pdo_get('storex_member', array('weid' => $_W['uniacid'], 'from_user' => $_W['openid']));
	$user_info['password'] = 0;
	if (!empty($member['credit_password']) && !empty($member['password_lock'])) {
		$user_info['password'] = 1;
	}
	wmessage(error(0, $user_info), '', 'ajax');
}
if ($op == 'personal_update') {
	if (!empty($_GPC['fields'])) {
		foreach ($_GPC['fields'] as $key=>$value) {
			if (empty($key) && $key != 'gender' && empty($value)) {
				wmessage(error(-1, '信息不完整'), '', 'ajax');
			}
		}
	}
	$result = mc_update($_W['openid'], $_GPC['fields']);
	if (!empty($result)) {
		wmessage(error(0, '修改成功'), '', 'ajax');
	} else {
		wmessage(error(-1, '修改失败'), '', 'ajax');
	}
}
if ($op == 'credits_record') {
	$credits = array();
	$credits_record = pdo_getall('mc_credits_record', array('uniacid' => $_W['uniacid'], 'credittype' => $_GPC['credittype'], 'uid' => $uid, 'module' => 'wn_storex'), array('num', 'createtime', 'module'), '', 'id DESC');
	if (!empty($credits_record)) {
		foreach ($credits_record as $data) {
			$data['createtime'] = date('Y-m-d H:i:s', $data['createtime']);
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
	wmessage(error(0, $credits), '', 'ajax');
}
if ($op == 'address_lists') {
	$address_info = pdo_getall('mc_member_address', array('uid' => $uid, 'uniacid' => $_W['uniacid']), '', '', 'isdefault DESC');
	wmessage(error(0, $address_info), '', 'ajax');
}
if ($op == 'current_address') {
	$current_info = pdo_get('mc_member_address', array('id' => intval($_GPC['id']), 'uid' => $uid, 'uniacid' => $_W['uniacid']));
	wmessage(error(0, $current_info), '', 'ajax');
}
if ($op == 'address_post') {
	$address_info = $_GPC['fields'];
	if (empty($address_info['username']) || empty($address_info['zipcode']) || empty($address_info['province']) || empty($address_info['city'])  || empty($address_info['district']) || empty($address_info['address'])) {
		wmessage(error(-1, '请填写正确的信息'), '', 'ajax');
	}
	if (empty($address_info['mobile'])) {
		wmessage(error(-1, '手机号码不能为空'), '', 'ajax');
	}
	if (!preg_match(REGULAR_MOBILE, $address_info['mobile'])) {
		wmessage(error(-1, '手机号码格式不正确'), '', 'ajax');
	}
	unset($address_info['id']);
	if (!empty($_GPC['id'])) {
		$result = pdo_update('mc_member_address', $address_info, array('id' => intval($_GPC['id'])));
		wmessage(error(0, $result), '', 'ajax');
	} else {
		$address_info['uid'] = $uid;
		$address_info['uniacid'] = $_W['uniacid'];
		$address = pdo_get('mc_member_address', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
		if (empty($address)) {
			$address_info['isdefault'] = 1;
		}
		$result = pdo_insert('mc_member_address', $address_info);
		wmessage(error(0, $result), '', 'ajax');
	}
}
if ($op == 'address_default') {
	$default_result = pdo_update('mc_member_address', array('isdefault' => '0'), array('uid' => $uid, 'uniacid' => $_W['uniacid']));
	$result = pdo_update('mc_member_address', array('isdefault' => '1'), array('id' => intval($_GPC['id'])));
	wmessage(error(0, '设置成功'), '', 'ajax');

}
if ($op == 'address_delete') {
	$result = pdo_delete('mc_member_address', array('id' => intval($_GPC['id'])));
	wmessage(error(0, '删除成功'), '', 'ajax');
}

if ($op == 'credit_password') {
	if (empty($_GPC['password'])) {
		wmessage(error(-1, '余额支付密码不能为空'), '', 'ajax');
	}
	$member = pdo_get('storex_member', array('weid' => $_W['uniacid'], 'from_user' => $_W['openid']), array('id', 'credit_password', 'credit_salt'));
	$password = hotel_member_hash($_GPC['password'], $member['credit_salt']);
	if ($password != $member['credit_password']) {
		wmessage(error(-1, '余额支付密码错误'), '', 'ajax');
	} else {
		wmessage(error(0, '密码正确'), '', 'ajax');
	}
}

if ($op == 'check_password_lock') {
	$member = pdo_get('storex_member', array('weid' => $_W['uniacid'], 'from_user' => $_W['openid']), array('id', 'password_lock'));
	if ($member['password_lock'] != trim($_GPC['password_lock'])) {
		wmessage(error(-1, '更改密码依据输入错误'), '', 'ajax');
	} else {
		wmessage(error(0, trim($_GPC['password_lock'])), '', 'ajax');
	}
}

if ($op == 'set_credit_password') {
	$member = pdo_get('storex_member', array('weid' => $_W['uniacid'], 'from_user' => $_W['openid']));
	$password = $_GPC['password'];
	$password_lock = $_GPC['password_lock'];
	if (istrlen($password) < 6) {
		wmessage(error(-1, '密码长度至少6位'), '', 'ajax');
	}
	if (istrlen($password_lock) > 10) {
		wmessage(error(-1, '改密依据不要太长'), '', 'ajax');
	}
	if (istrlen($password_lock) < 4) {
		wmessage(error(-1, '改密依据太短'), '', 'ajax');
	}
	$salt = random(8);
	$password = hotel_member_hash($password, $salt);
	$result = pdo_update('storex_member', array('credit_password' => $password, 'credit_salt' => $salt, 'password_lock' => $password_lock), array('weid' => $_W['uniacid'], 'from_user' => $_W['openid']));
	if (!empty($result)) {
		wmessage(error(0, '设置密码成功'), '', 'ajax');
	} else {
		wmessage(error(-1, '设置密码失败'), '', 'ajax');
	}
}
