<?php
/**
 * 我的账户
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('user');
load()->func('file');
load()->classs('oauth2/oauth2client');

$dos = array('base', 'post', 'bind', 'validate_mobile', 'bind_mobile', 'unbind_third');
$do = in_array($do, $dos) ? $do : 'base';
$_W['page']['title'] = '账号信息 - 我的账户 - 用户管理';

if ($do == 'post' && $_W['isajax'] && $_W['ispost']) {
	$type = trim($_GPC['type']);

	if ($_W['isfounder']) {
		$uid = is_array($_GPC['uid']) ? 0 : intval($_GPC['uid']);
	} else {
		$uid = $_W['uid'];
	}
	if (empty($uid) || empty($type)) {
		iajax(40035, '参数错误，请刷新后重试！', '');
	}
	$user = user_single($uid);
	if (empty($user)) {
		iajax(-1, '用户不存在或已经被删除！', '');
	}

	if ($user['status'] == USER_STATUS_CHECK || $user['status'] == USER_STATUS_BAN) {
		iajax(-1, '访问错误，该用户未审核或者已被禁用，请先修改用户状态！', '');
	}

	$users_profile_exist = pdo_get('users_profile', array('uid' => $uid));

	if ($type == 'birth') {
		if ($users_profile_exist['year'] == $_GPC['year'] && $users_profile_exist['month'] == $_GPC['month'] && $users_profile_exist['day'] == $_GPC['day']) iajax(0, '未作修改！', '');
	} elseif ($type == 'reside') {
		if ($users_profile_exist['province'] == $_GPC['province'] && $users_profile_exist['city'] == $_GPC['city'] && $users_profile_exist['district'] == $_GPC['district']) iajax(0, '未作修改！', '');
	} else {
		if (in_array($type, array('username', 'password'))) {
			if ($user[$type] == $_GPC[$type] && $type != 'password') iajax(0, '未做修改！', '');
		} else {
			if ($users_profile_exist[$type] == $_GPC[$type]) iajax(0, '未作修改！', '');
		}
	}
	switch ($type) {
		case 'avatar':
		case 'realname':
		case 'address':
		case 'qq':
		case 'mobile':
			if ($type == 'mobile') {
				$match = preg_match(REGULAR_MOBILE, trim($_GPC[$type]));
				if (empty($match)) {
					iajax(-1, '手机号不正确', '');
				}
				$users_mobile = pdo_get('users_profile', array('mobile' => trim($_GPC[$type]), 'uid <>' => $uid));
				if (!empty($users_mobile)) {
					iajax(-1, '手机号已存在，请联系管理员', '');
				}
			}
			if ($users_profile_exist) {
				$result = pdo_update('users_profile', array($type => trim($_GPC[$type])), array('uid' => $uid));
			} else {
				$data = array(
					'uid' => $uid,
					'createtime' => TIMESTAMP,
					$type => trim($_GPC[$type])
				);
				$result = pdo_insert('users_profile', $data);
			}
			break;
		case 'username':
			$founders = explode(',', $_W['config']['setting']['founder']);
			if (in_array($uid, $founders) && !in_array($_W['uid'], $founders)) {
				iajax(1, '用户名不可与网站创始人同名！', '');
			}
			$username = trim($_GPC['username']);
			$name_exist = pdo_get('users', array('username' => $username));
			if (!empty($name_exist)) {
				iajax(2, '用户名已存在，请更换其他用户名！', '');
			}
			$result = pdo_update('users', array('username' => $username), array('uid' => $uid));
			break;
		case 'vice_founder_name':
			$owner_uid = user_get_uid_byname($_GPC['vice_founder_name']);
			if (empty($owner_uid)) {
				iajax(1, '创始人不存在', '');
			}
			$result = pdo_update('users', array('owner_uid' => $owner_uid), array('uid' => $uid));
			break;
		case 'remark':
			$result = pdo_update('users', array('remark' => trim($_GPC['remark'])), array('uid' => $uid));
			break;
		case 'password':
			if ($_GPC['newpwd'] !== $_GPC['renewpwd']) iajax(2, '两次密码不一致！', '');
			if (!$_W['isfounder'] && empty($user['register_type'])) {
				$pwd = user_hash($_GPC['oldpwd'], $user['salt']);
				if ($pwd != $user['password']) iajax(3, '原密码不正确！', '');
			}
			$newpwd = user_hash($_GPC['newpwd'], $user['salt']);
			if ($newpwd == $user['password']) {
				iajax(0, '未作修改！', '');
			}
			$result = pdo_update('users', array('password' => $newpwd), array('uid' => $uid));
			break;
		case 'endtime' :
			if ($_GPC['endtype'] == 1) {
				$endtime = 0;
			} else {
				$endtime = strtotime($_GPC['endtime']);
			}
			if (user_is_vice_founder() && !empty($_W['user']['endtime']) && ($endtime > $_W['user']['endtime'] || empty($endtime))) {
				iajax(-1, '副创始人给用户设置的时间不能超过自己的到期时间');
			}
			$result = pdo_update('users', array('endtime' => $endtime), array('uid' => $uid));
			pdo_update('users_profile', array('send_expire_status' => 0), array('uid' => $uid));
			$uni_account_user = pdo_getall('uni_account_users', array('uid' => $uid, 'role' => 'owner'));
			if (!empty($uni_account_user)) {
				foreach ($uni_account_user as $account) {
					cache_delete("uniaccount:{$account['uniacid']}");
				}
			}
			break;
		case 'birth':
			if ($users_profile_exist) {
				$result = pdo_update('users_profile', array('birthyear' => intval($_GPC['year']), 'birthmonth' => intval($_GPC['month']), 'birthday' => intval($_GPC['day'])), array('uid' => $uid));
			} else {
				$data = array(
					'uid' => $uid,
					'createtime' => TIMESTAMP,
					'birthyear' => intval($_GPC['year']),
					'birthmonth' => intval($_GPC['month']),
					'birthday' => intval($_GPC['day'])
				);
				$result = pdo_insert('users_profile', $data);
			}
			break;
		case 'reside':
			if ($users_profile_exist) {
				$result = pdo_update('users_profile', array('resideprovince' => $_GPC['province'], 'residecity' => $_GPC['city'], 'residedist' => $_GPC['district']), array('uid' => $uid));
			} else {
				$data = array(
					'uid' => $uid,
					'createtime' => TIMESTAMP,
					'resideprovince' => $_GPC['province'],
					'residecity' => $_GPC['city'],
					'residedist' => $_GPC['district']
				);
				$result = pdo_insert('users_profile', $data);
			}
			break;
	}
	if ($result) {
		pdo_update('users_profile', array('edittime' => TIMESTAMP), array('uid' => $uid));
		iajax(0, '修改成功！', '');
	} else {
		iajax(1, '修改失败，请稍候重试！', '');
	}
}

//账号信息
if ($do == 'base') {
	$user_type = !empty($_GPC['user_type']) ? trim($_GPC['user_type']) : PERSONAL_BASE_TYPE;
	//基础信息
	$user = user_single($_W['uid']);
	if (empty($user)) {
		itoast('抱歉，用户不存在或是已经被删除！', url('user/profile'), 'error');
	}
	$user['last_visit'] = date('Y-m-d H:i:s', $user['lastvisit']);
	$user['joindate'] = date('Y-m-d H:i:s', $user['joindate']);
	$user['url'] = user_invite_register_url($_W['uid']);

	$profile = pdo_get('users_profile', array('uid' => $_W['uid']));

	$profile = user_detail_formate($profile);

	if (!$_W['isfounder'] || user_is_vice_founder()) {
		//应用模版权限
		if ($_W['user']['founder_groupid'] == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER) {
			$groups = user_founder_group();
			$group_info = user_founder_group_detail_info($user['groupid']);
		} else {
			$groups = user_group();
			$group_info = user_group_detail_info($user['groupid']);
		}

		//使用帐号列表
		$account_detail = user_account_detail_info($_W['uid']);
	}
	template('user/profile');
}


$user_table = table('users');
$user = $user_table->usersInfo($_W['uid']);
$user_profile = $user_table->userProfile($_W['uid']);

if ($do == 'bind') {
	$user_table->bindSearchWithUser($_W['uid']);
	$bind_info = $user_table->userBind();

	$signs = array_keys($bind_info);

	if (!empty($user['openid']) && !in_array($user['openid'], $signs)) {
		pdo_insert('users_bind', array('uid' => $user['uid'], 'bind_sign' => $user['openid'], 'third_type' => $user['register_type'], 'third_nickname' => $user_profile['nickname']));
	}

	if (!empty($user_profile['mobile']) && !in_array($user_profile['mobile'], $signs)) {
		pdo_insert('users_bind', array('uid' => $user_profile['uid'], 'bind_sign' => $user_profile['mobile'], 'third_type' => USER_REGISTER_TYPE_MOBILE, 'third_nickname' => $user_profile['mobile']));
	}

	$user_table->bindSearchWithUser($_W['uid']);
	$lists = $user_table->userBind();

	$bind_qq = array();
	$bind_wechat = array();
	$bind_mobile = array();

	if (!empty($lists)) {
		foreach($lists as $list) {
			switch($list['third_type']){
				case USER_REGISTER_TYPE_QQ:
					$bind_qq = $list;
					break;
				case USER_REGISTER_TYPE_WECHAT:
					$bind_wechat = $list;
					break;
				case USER_REGISTER_TYPE_MOBILE:
					$bind_mobile = $list;
					break;
			}
		}
	}

	$support_login_urls = user_support_urls();

	template('user/bind');
}

if (in_array($do, array('validate_mobile', 'bind_mobile'))) {
	$mobile = trim($_GPC['mobile']);
	$type = trim($_GPC['type']);
	$user_table = table('users');

	$mobile_exists = $user_table->userProfileMobile($mobile);
	if (empty($mobile)) {
		iajax(-1, '手机号不能为空');
	}
	if (!preg_match(REGULAR_MOBILE, $mobile)) {
		iajax(-1, '手机号格式不正确');
	}

	if (!empty($type) && $mobile != $user_profile['mobile']) {
		iajax(-1, '请输入已绑定的手机号');
	}

	if (empty($type) && !empty($mobile_exists)) {
		iajax(-1, '手机号已存在');
	}
}
if ($do == 'validate_mobile') {
	iajax(0, '本地校验成功');
}

if ($do == 'bind_mobile') {
	if ($_W['isajax'] && $_W['ispost']) {
		$sms_code = trim($_GPC['smscode']);
		$image_code =trim($_GPC['imagecode']);
		$password = $_GPC['password'];
		$repassword = $_GPC['repassword'];

		if (empty($sms_code)) {
			iajax(-1, '短信验证码不能为空');
		}

		if (empty($image_code)) {
			iajax(-1, '图形验证码不能为空');
		}

		$captcha = checkcaptcha($image_code);
		if (empty($captcha)) {
			iajax(-1, '图形验证码错误,请重新获取');
		}

		if ((empty($password) || empty($repassword)) && empty($type)) {
			iajax(-1, '密码不能为空');
		}

		if ($password != $repassword && empty($type)) {
			iajax(-1, '两次密码不一致');
		}

		$code_info = $user_table->userVerifyCode($mobile, $sms_code);
		if (empty($code_info)) {
			iajax(-1, '短信验证码不正确');
		}
		if ($code_info['createtime'] + 120 < TIMESTAMP) {
			iajax(-1, '短信验证码已过期，请重新获取');
		}

		if (!empty($type)) {
			if ($user['register_type'] == USER_REGISTER_TYPE_MOBILE) {
				pdo_update('users', array('openid' => ''), array('uid' => $_W['uid']));
			}
			pdo_update('users_profile', array('mobile' => ''), array('id' => $user_profile['id']));
			pdo_delete('users_bind', array('uid' => $_W['uid'], 'bind_sign' => $mobile, 'third_type' => USER_REGISTER_TYPE_MOBILE));
		}

		if (empty($type)) {
			pdo_update('users', array('password' => user_hash($password, $user['salt'])), array('uid' => $_W['uid']));
			pdo_update('users_profile', array('mobile' => $mobile), array('id' => $user_profile['id']));
			pdo_insert('users_bind', array('uid' => $_W['uid'], 'bind_sign' => $mobile, 'third_type' => USER_REGISTER_TYPE_MOBILE, 'third_nickname' => $mobile));
		}
		iajax(0, '成功', url('user/profile/bind'));
	} else {
		iajax(-1, '非法请求');
	}
}

if ($do == 'unbind_third') {
	$third_type = $_GPC['third_type'];
	if (!in_array($third_type, array(USER_REGISTER_TYPE_QQ, USER_REGISTER_TYPE_WECHAT))) {
		iajax(-1, '类型错误');
	}
	if ($_W['isajax'] && $_W['ispost']) {
		$user_table->bindSearchWithUser($_W['uid']);
		$user_table->bindSearchWithType($third_type);
		$bind_info = $user_table->bindInfo();

		if (empty($bind_info)) {
			iajax(-1, '已经解除绑定');
		}
		pdo_update('users', array('openid' => ''), array('uid' => $_W['uid']));
		pdo_delete('users_bind', array('uid' => $_W['uid'], 'third_type' => $third_type));
		iajax(0, '解绑成功', url('user/profile/bind'));
	}
	iajax(-1, '非法请求');
}