<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */

/**
 * 检测会员信息是否存�邮箱和手机号)
 * @param array $data 会员信息
 * @return mixed
 */
function mc_check($data) {
	global $_W;
	if (!empty($data['email'])) {
		$email = trim($data['email']);
		if (!preg_match(REGULAR_EMAIL, $email)) {
			return error(-1, '邮箱格式不正�);
		}
		$isexist = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid AND email = :email AND uid != :uid', array(':uniacid' => $_W['uniacid'], ':email' => $email, ':uid' => $_W['member']['uid']));
		if ($isexist >= 1) {
			return error(-1, '邮箱已被注册');
		}
	}
	if (!empty($data['mobile'])) {
		$mobile = trim($data['mobile']);
		if (!preg_match(REGULAR_MOBILE, $mobile)) {
			return error(-1, '手机号格式不正确');
		}
		$isexist = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid AND mobile = :mobile AND uid != :uid', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile, ':uid' => $_W['member']['uid']));
		if ($isexist >= 1) {
			return error(-1, '手机号已被注�);
		}
	}
	return true;
}

/**
 * 更新会员个人信息.
 * 此函数不能更新用户的积分数量，更新积分数量使�mc_credit_update
 * @param mixed $uid 会员id
 * @param array $fields 更新字段
 * @return mixed
 */
function mc_update($uid, $fields) {
	global $_W;
	if (empty($fields)) {
		return false;
	}
	//如果�uid 是一个openid并且是新增用户，需要更新mc_mapping_fans对应的uid
	$uid_temp = $uid;

	$uid = mc_openid2uid($uid);

	$_W['weid'] && $fields['weid'] = $_W['weid'];
	$struct = array_keys(mc_fields());
	$struct[] = 'birthyear';
	$struct[] = 'birthmonth';
	$struct[] = 'birthday';
	$struct[] = 'resideprovince';
	$struct[] = 'residecity';
	$struct[] = 'residedist';
	$struct[] = 'groupid';
	
	if (isset($fields['birth']) && !is_array($fields['birth'])) {
		$birth = explode('-', $fields['birth']);
		$fields['birth'] = array(
			'year' => $birth[0],
			'month' => $birth[1],
			'day' => $birth[2],
		);
	}
	if (!empty($fields['birth'])) {
		$fields['birthyear'] = $fields['birth']['year'];
		$fields['birthmonth'] = $fields['birth']['month'];
		$fields['birthday'] = $fields['birth']['day'];
	}
	if (isset($fields['reside'])) {
		$fields['resideprovince'] = $fields['reside']['province'];
		$fields['residecity'] = $fields['reside']['city'];
		$fields['residedist'] = $fields['reside']['district'];
	}
	unset($fields['reside'], $fields['birth']);
	foreach ($fields as $field => $value) {
		if (!in_array($field, $struct) || is_array($value)) {
			unset($fields[$field]);
		}
	}
	if (!empty($fields['avatar'])) {
		if (strexists($fields['avatar'], 'attachment/images/global/avatars/avatar_')) {
			$fields['avatar'] = str_replace($_W['attachurl'], '', $fields['avatar']);
		}
	}
	$isexists = pdo_fetchcolumn("SELECT uid FROM " . tablename('mc_members') . " WHERE uid = :uid", array(':uid' => $uid));
	$condition = '';
	if (!empty($isexists)) {
		$condition = ' AND uid != ' . $uid;
	}
	//判断email,mobile是否唯一
	if (!empty($fields['email'])) {
		$emailexists = pdo_fetchcolumn("SELECT email FROM " . tablename('mc_members') . " WHERE uniacid = :uniacid AND email = :email " . $condition, array(':uniacid' => $_W['uniacid'], ':email' => trim($fields['email'])));
		if ($emailexists) {
			unset($fields['email']);
		}
	}
	if (!empty($fields['mobile'])) {
		$mobilexists = pdo_fetchcolumn("SELECT mobile FROM " . tablename('mc_members') . " WHERE uniacid = :uniacid AND mobile = :mobile " . $condition, array(':uniacid' => $_W['uniacid'], ':mobile' => trim($fields['mobile'])));
		if ($mobilexists) {
			unset($fields['mobile']);
		}
	}
	if (empty($isexists)) {
		if(empty($fields['mobile']) && empty($fields['email'])) {
			return false;
		}
		$fields['uniacid'] = $_W['uniacid'];
		$fields['createtime'] = TIMESTAMP;
		pdo_insert('mc_members', $fields);
		$insert_id = pdo_insertid();
		if(is_string($uid_temp)) {
			pdo_update('mc_mapping_fans', array('uid' => $insert_id), array('uniacid' => $_W['uniacid'], 'openid' => trim($uid_temp)));
		}
		return $insert_id;
	} else {
		if (!empty($fields)) {
			$result = pdo_update('mc_members', $fields, array('uid' => $uid));
		} else {
			$result = 0;
		}
		return $result > 0;
	}
}

/**
 * 获取一个或多个会员某个或多个字段的信息
 * @param mixed $uid 一个或多个会员 uid
 * @param array $fields 一个、多个或所有字�
 * @return array
 * */
function mc_fetch($uid, $fields = array()) {
	global $_W;
	$uid = mc_openid2uid($uid);
	if (empty($uid)) {
		return array();
	}
	$struct = mc_fields();
	$struct = array_keys($struct);
	if (empty($fields)) {
		$select = '*';
	} else {
		foreach ($fields as $key => $field) {
			if (!in_array($field, $struct)) {
				unset($fields[$key]);
			}

			if ($field == 'birth') {
				$fields[] = 'birthyear';
				$fields[] = 'birthmonth';
				$fields[] = 'birthday';
			}
			if ($field == 'reside') {
				$fields[] = 'resideprovince';
				$fields[] = 'residecity';
				$fields[] = 'residedist';
			}
		}
		unset($fields['birth'], $fields['reside']);
		if (!empty($fields)) {
			$select = '`uid`, `' . implode('`,`', $fields) . '`';
		} else {
			$select = '*';
		}
	}
	if (is_array($uid)) {
		$result = pdo_fetchall("SELECT $select FROM " . tablename('mc_members') . " WHERE uid IN ('" . implode("','", is_array($uid) ? $uid : array($uid)) . "')", array(), 'uid');
		foreach ($result as &$row) {
			if (isset($row['credit1'])) {
				$row['credit1'] = floatval($row['credit1']);
			}
			if (isset($row['credit2'])) {
				$row['credit2'] = floatval($row['credit2']);
			}
			if (isset($row['credit3'])) {
				$row['credit3'] = floatval($row['credit3']);
			}
			if (isset($row['credit4'])) {
				$row['credit4'] = floatval($row['credit4']);
			}
			if (isset($row['credit5'])) {
				$row['credit5'] = floatval($row['credit5']);
			}
			if (isset($row['credit6'])) {
				$row['credit6'] = floatval($row['credit6']);
			}
			if (isset($row['avatar']) && !empty($row['avatar'])) {
				$row['avatar'] = tomedia($row['avatar']);
			}
		}
	} else {
		$result = pdo_fetch("SELECT $select FROM " . tablename('mc_members') . " WHERE `uid` = :uid", array(':uid' => $uid));
		if (isset($result['avatar']) && !empty($result['avatar'])) {
			$result['avatar'] = tomedia($result['avatar']);
		}
		if (isset($result['credit1'])) {
			$result['credit1'] = floatval($result['credit1']);
		}
		if (isset($result['credit2'])) {
			$result['credit2'] = floatval($result['credit2']);
		}
		if (isset($result['credit3'])) {
			$result['credit3'] = floatval($result['credit3']);
		}
		if (isset($result['credit4'])) {
			$result['credit4'] = floatval($result['credit4']);
		}
		if (isset($result['credit5'])) {
			$result['credit5'] = floatval($result['credit5']);
		}
		if (isset($result['credit6'])) {
			$result['credit6'] = floatval($result['credit6']);
		}
	}
	return $result;
}

/**
 * 获取粉丝信息
 * @param mixed $openidOruid 粉丝或会员ID
 * @param int $acid 子公众号ID
 * @param int $uniacid 主公众号ID
 * @return array
 */
function mc_fansinfo($openidOruid, $acid = 0, $uniacid = 0){
	global $_W;
	if (empty($openidOruid)) {
		return array();
	}
	$params = array();
	if (is_numeric($openidOruid)) {
		$condition = '`uid` = :uid';
		$params[':uid'] = $openidOruid;
	} else {
		$condition = '`openid` = :openid';
		$params[':openid'] = $openidOruid;
	}

	if (!empty($acid)) {
		$params[':acid'] = $acid;
		$condition .= " AND `acid` = :acid";
	}
	if (!empty($uniacid)) {
		$params[':uniacid'] = $uniacid;
		$condition .= " AND `uniacid` = :uniacid";
	}
	$sql = 'SELECT * FROM ' . tablename('mc_mapping_fans') . " WHERE $condition";
	$fan = pdo_fetch($sql, $params);
	if (!empty($fan)) {
		if (!empty($fan['tag']) && is_string($fan['tag'])) {
			if (is_base64($fan['tag'])) {
				$fan['tag'] = @base64_decode($fan['tag']);
			}
			if (is_serialized($fan['tag'])) {
				$fan['tag'] = @iunserializer($fan['tag']);
			}
			if (is_array($fan['tag']) && !empty($fan['tag']['headimgurl'])) {
				$fan['tag']['avatar'] = tomedia($fan['tag']['headimgurl']);
				unset($fan['tag']['headimgurl']);
				$fan['nickname'] = $fan['tag']['nickname'];
				$fan['gender'] = $fan['sex'] = $fan['tag']['sex'];
				$fan['avatar'] = $fan['headimgurl'] = $fan['tag']['avatar'];
			}
		} else {
			$fan['tag'] = array();
		}
	}
	if (empty($fan) && $openidOruid == $_W['openid'] && !empty($_SESSION['userinfo'])) {
		$fan['tag'] = unserialize(base64_decode($_SESSION['userinfo']));
		$fan['uid'] = 0;
		$fan['openid'] = $fan['tag']['openid'];
		$fan['follow'] = 0;
		$fan['nickname'] = $fan['tag']['nickname'];
		$fan['gender'] = $fan['sex'] = $fan['tag']['sex'];
		$fan['avatar'] = $fan['headimgurl'] = $fan['tag']['headimgurl'];
		$mc_oauth_fan = mc_oauth_fans($fan['openid']);
		if (!empty($mc_oauth_fan)) {
			$fan['uid'] = $mc_oauth_fan['uid'];
		}
	}
	return $fan;
}

/**
 * 无网页授权的公众�通过借用授权定位 openid �uid
 * @param string $openid 粉丝唯一标志
 * @param int $acid 子公众号ID
 * @return array
 */
function mc_oauth_fans($openid, $acid = 0){
	$condition = array();
	$condition['oauth_openid'] = $openid;
	if (!empty($acid)) {
		$condition['acid'] = $acid;
	}
	$fan = pdo_get('mc_oauth_fans', $condition, array('openid', 'uid'));
	return $fan;
}

/**
 * 调用oauth用户授权获取资料并更新会员信�
 * @param int $acid 子公众号ID
 * @return mixed
 */
function mc_oauth_userinfo($acid = 0) {
	global $_W;
	if (isset($_SESSION['userinfo'])) {
		$userinfo = unserialize(base64_decode($_SESSION['userinfo']));
		if (!empty($userinfo['subscribe']) || !empty($userinfo['nickname'])) {
			return $userinfo;
		}
	}
	if ($_W['container'] != 'wechat') {
		return array();
	}
	// 认证� 静默获取用户信息, 不需要跳转到网页授权获取用户信息.
	if (!empty($_SESSION['openid']) && intval($_W['account']['level']) >= 3) {
		$oauth_account = WeAccount::create($_W['account']['oauth']);
		$userinfo = $oauth_account->fansQueryInfo($_SESSION['openid']);
		if (!is_error($userinfo) && !empty($userinfo) && is_array($userinfo) && !empty($userinfo['nickname'])) {
			$userinfo['nickname'] = stripcslashes($userinfo['nickname']);
			if (!empty($userinfo['headimgurl'])) {
				$userinfo['headimgurl'] = rtrim($userinfo['headimgurl'], '0') . 132;
			}
			$userinfo['avatar'] = $userinfo['headimgurl'];
			$_SESSION['userinfo'] = base64_encode(iserializer($userinfo));

			$fan = mc_fansinfo($_SESSION['openid']);
			if (!empty($fan)) {
				$record = array(
					'updatetime' => TIMESTAMP,
					'nickname' => stripslashes($userinfo['nickname']),
					'follow' => $userinfo['subscribe'],
					'followtime' => $userinfo['subscribe_time'],
					'tag' => base64_encode(iserializer($userinfo))
				);
				pdo_update('mc_mapping_fans', $record, array('openid' => $_SESSION['openid'], 'acid' => $_W['acid'], 'uniacid' => $_W['uniacid']));
			} else {
				$record = array();
				$record['updatetime'] = TIMESTAMP;
				$record['nickname'] = stripslashes($userinfo['nickname']);
				$record['tag'] = base64_encode(iserializer($userinfo));
				$record['openid'] = $_SESSION['openid'];
				$record['acid'] = $_W['acid'];
				$record['uniacid'] = $_W['uniacid'];
				pdo_insert('mc_mapping_fans', $record);
			}
			
			if (!empty($fan['uid']) || !empty($_SESSION['uid'])) {
				$uid = intval($fan['uid']);
				if (empty($uid)) {
					$uid = intval($_SESSION['uid']);
				}
				$member = mc_fetch($uid, array('nickname', 'gender', 'residecity', 'resideprovince', 'nationality', 'avatar'));
				$record = array();
				if (empty($member['nickname']) && !empty($userinfo['nickname'])) {
					$record['nickname'] = stripslashes($userinfo['nickname']);
				}
				if (empty($member['gender']) && !empty($userinfo['sex'])) {
					$record['gender'] = $userinfo['sex'];
				}
				if (empty($member['residecity']) && !empty($userinfo['city'])) {
					$record['residecity'] = $userinfo['city'] . '�;
				}
				if (empty($member['resideprovince']) && !empty($userinfo['province'])) {
					$record['resideprovince'] = $userinfo['province'] . '�;
				}
				if (empty($member['nationality']) && !empty($userinfo['country'])) {
					$record['nationality'] = $userinfo['country'];
				}
				if (empty($member['avatar']) && !empty($userinfo['headimgurl'])) {
					$record['avatar'] = $userinfo['headimgurl'];
				}
				if (!empty($record)) {
					pdo_update('mc_members', $record, array('uid' => intval($uid)));
				}
			}
			return $userinfo;
		}
	}

	if (empty($_W['account']['oauth'])) {
		return error(-1, '未指定网页授权公众号, 无法获取用户信息.');
	}
	if (empty($_W['account']['oauth']['key'])) {
		return error(-2, '公众号未设置 appId �secret.');
	}
	if (intval($_W['account']['oauth']['level']) < 4) {
		return error(-3, '公众号非认证服务� 无法获取用户信息.');
	}

	$state = 'we7sid-' . $_W['session_id'];
	$_SESSION['dest_url'] = urlencode($_W['siteurl']);
	
	$unisetting = uni_setting($_W['uniacid']);
	$str = '';
	if(uni_is_multi_acid()) {
		$str = "&j={$_W['acid']}";
	}
	$url = (!empty($unisetting['oauth']['host']) ? ($unisetting['oauth']['host'] . '/') : $_W['siteroot']) . "app/index.php?i={$_W['uniacid']}{$str}&c=auth&a=oauth&scope=userinfo";
	$callback = urlencode($url);
	
	$oauth_account = WeAccount::create($_W['account']['oauth']);
	$forward = $oauth_account->getOauthUserInfoUrl($callback, $state);
	header('Location: ' . $forward);
	exit;
}

/*
 * 如果某个模块需要获取用户的的一些信�比如:真实姓名�,可使用函数【因生日.地区tpl函数修改,重新改造该函数�
 * 如果用户已经完善�真实姓名),则返回用户的信息(真实姓名)。如果没有完�则显示表�让用户填�
 * @param int $uid 会员ID
 * @param array $fields 会员字段信息
 * @param string $pre 提示信息连接�
 * @return mixed
 */
function mc_require($uid, $fields, $pre = '') {
	global $_W, $_GPC;
	if (empty($fields) || !is_array($fields)) {
		return false;
	}
	$flipfields = array_flip($fields);
	//如果是生日或户籍,只要取其中一个字�则返回其他两�
	if (in_array('birth', $fields) || in_array('birthyear', $fields) || in_array('birthmonth', $fields) || in_array('birthday', $fields)) {
		unset($flipfields['birthyear'], $flipfields['birthmonth'], $flipfields['birthday'], $flipfields['birth']);
		$flipfields['birthyear'] = 'birthyear';
		$flipfields['birthmonth'] = 'birthmonth';
		$flipfields['birthday'] = 'birthday';
	}
	if (in_array('reside', $fields) || in_array('resideprovince', $fields) || in_array('residecity', $fields) || in_array('residedist', $fields)) {
		unset($flipfields['residedist'], $flipfields['resideprovince'], $flipfields['residecity'], $flipfields['reside']);
		$flipfields['resideprovince'] = 'resideprovince';
		$flipfields['residecity'] = 'residecity';
		$flipfields['residedist'] = 'residedist';
	}
	$fields = array_keys($flipfields);
	if (!in_array('uniacid', $fields)) {
		$fields[] = 'uniacid';
	}
	if (!empty($pre)) {
		$pre .= '<br/>';
	}
	if (empty($uid)) {
		foreach ($fields as $field) {
			$profile[$field] = '';
		}
		$uniacid = $_W['uniacid'];
	} else {
		$profile = mc_fetch($uid, $fields);
		$uniacid = $profile['uniacid'];
	}

	$sql = 'SELECT `f`.`field`, `f`.`id` AS `fid`, `mf`.* FROM ' . tablename('profile_fields') . " AS `f` LEFT JOIN " .
		tablename('mc_member_fields') . " AS `mf` ON `f`.`id` = `mf`.`fieldid` WHERE `uniacid` = :uniacid ORDER BY
			`displayorder` DESC";
	$system_fields = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']), 'field');
	if (empty($system_fields)) {
		$sql = 'SELECT `id`, `field`, `title` FROM ' . tablename('profile_fields');
		$system_fields = pdo_fetchall($sql, array(), 'field');
	}

	$titles = array();
	foreach ($system_fields as $field) {
		$titles[$field['field']] = $field['title'];
	}

	$message = '';
	$ks = array();
	foreach ($profile as $k => $v) {
		if (empty($v)) {
			$ks[] = $k;
			$message .= $system_fields[$k]['title'] . ', ';
		}
	}

	if (!empty($message)) {
		$title = '完善资料';
		if (checksubmit('submit')) {
			if (in_array('resideprovince', $fields)) {
				$_GPC['resideprovince'] = $_GPC['reside']['province'];
				$_GPC['residecity'] = $_GPC['reside']['city'];
				$_GPC['residedist'] = $_GPC['reside']['district'];
			}
			if (in_array('birthyear', $fields)) {
				$_GPC['birthyear'] = $_GPC['birth']['year'];
				$_GPC['birthmonth'] = $_GPC['birth']['month'];
				$_GPC['birthday'] = $_GPC['birth']['day'];
			}
			$record = array_elements($fields, $_GPC);
			if (isset($record['uniacid'])) {
				unset($record['uniacid']);
			}

			foreach ($record as $field => $value) {
				if ($field == 'gender') {
					continue;
				}
				if (empty($value)) {
					message('请填写完整所有资�', referer(), 'error');
				}
			}
			if (empty($record['nickname']) && !empty($_W['fans']['nickname'])) {
				$record['nickname'] = $_W['fans']['nickname'];
			}
			if (empty($record['avatar']) && !empty($_W['fans']['tag']['avatar'])) {
				$record['avatar'] = $_W['fans']['tag']['avatar'];
			}
			$condition = " AND uid != {$uid} ";
			if (in_array('email', $fields)) {
				$emailexists = pdo_fetchcolumn("SELECT email FROM " . tablename('mc_members') . " WHERE uniacid = :uniacid AND email = :email " . $condition, array(':uniacid' => $_W['uniacid'], ':email' => trim($record['email'])));
				if (!empty($emailexists)) {
					message('抱歉，您填写的手机号已经被使用，请更新�, 'refresh', 'error');
				}
			}
			if (in_array('mobile', $fields)) {
				$mobilexists = pdo_fetchcolumn("SELECT mobile FROM " . tablename('mc_members') . " WHERE uniacid = :uniacid AND mobile = :mobile " . $condition, array(':uniacid' => $_W['uniacid'], ':mobile' => trim($record['mobile'])));
				if (!empty($mobilexists)) {
					message('抱歉，您填写的手机号已经被使用，请更新�, 'refresh', 'error');
				}
			}
			$insertuid = mc_update($uid, $record);
			if (empty($uid)) {
				pdo_update('mc_oauth_fans', array('uid' => $insertuid), array('oauth_openid' => $_W['openid']));
				pdo_update('mc_mapping_fans', array('uid' => $insertuid), array('openid' => $_W['openid']));
			}
			message('资料完善成功.', 'refresh');
		}
		load()->func('tpl');
		load()->model('activity');
		$filter = array();
		$filter['status'] = 1;
		$coupons = activity_coupon_owned($_W['member']['uid'], $filter);
		$tokens = activity_token_owned($_W['member']['uid'], $filter);

		$setting = uni_setting($_W['uniacid'], array('creditnames', 'creditbehaviors', 'uc'));
		$behavior = $setting['creditbehaviors'];
		$creditnames = $setting['creditnames'];
		$credits = mc_credit_fetch($_W['member']['uid'], '*');
		include template('mc/require', TEMPLATE_INCLUDEPATH);
		exit;
	}
	return $profile;
}

/**
 * 会员积分操作(记录所有的积分变动)
 * @param int $uid 会员ID
 * @param string $credittype 积分类型
 * @param mixed $creditval 积分数量，数量可以为正数或是负数
 * @param array $log 积分操作日志 索引数组,0=>操作管理员id, 1=>备注, 2=>模块标识 3=>店员id 4=>门店id 5=> 变更渠道(clerk_type 操作人类�1: 线上操作 2: 系统后台(公众号管理员和操作员) 3: 店员)
 * @return boolean 
 */
function mc_credit_update($uid, $credittype, $creditval = 0, $log = array()) {
	global $_W;
	$credittype = trim($credittype);
	$credittypes = mc_credit_types();
	$clerk_types = array(
		'1' => '线上操作',
		'2' => '系统后台',
		'3' => '店员',
	);
	if (!in_array($credittype, $credittypes)) {
		return error('-1', "指定的用户积分类�“{$credittype}”不存在.");
	}
	$creditval = floatval($creditval);
	if (empty($creditval)) {
		return true;
	}
	$value = pdo_fetchcolumn("SELECT $credittype FROM " . tablename('mc_members') . " WHERE `uid` = :uid", array(':uid' => $uid));
	if ($creditval > 0 || ($value + $creditval >= 0) || $credittype == 'credit6') {
		pdo_update('mc_members', array($credittype => $value + $creditval), array('uid' => $uid));
	} else {
		return error('-1', "积分类型为“{$credittype}”的积分不够，无法操作�);
	}
	//记录日志(如果没有$log参数或参数不合法,将视为用户自己修改积�;
	if (empty($log) || !is_array($log)) {
		load()->func('logging');
		if (!empty($GLOBALS['site']) && $GLOBALS['site'] instanceof WeModuleSite) {
			$log = array(
				$uid, 
				$GLOBALS['site']->module['title'] . '模块内消� . logging_implode($_GET),
				$GLOBALS['site']->module['name'],
				0,
			);
		} elseif (!empty($GLOBALS['_GPC']['m'])) {
			$modules = uni_modules();
			$log = array(
				$uid,
				$modules[$GLOBALS['_GPC']['m']]['title'] . '模块内消� . logging_implode($_GET),
				$GLOBALS['_GPC']['m'],
				0,
			);
		} else {
			$log = array($uid, '未记�, 0, 0);
		}
	}
	if ($credittype == 'credit1') {
		$credittype_name = '积分';
	} elseif ($credittype == 'credit2') {
		$credittype_name = '�;
	}
	if (empty($log[1])) {
		if ($creditval > 0) {
			$log[1] = $clerk_types[$log[5]] . ': 添加' . $creditval . $credittype_name;
		} else {
			$log[1] = $clerk_types[$log[5]] . ': 减少' . -$creditval . $credittype_name;
		}
		
	}
	$clerk_type = intval($log[5]) ? intval($log[5]) : 1;
	$data = array(
		'uid' => $uid,
		'credittype' => $credittype,
		'uniacid' => $_W['uniacid'],
		'num' => $creditval,
		'createtime' => TIMESTAMP,
		'operator' => intval($log[0]),
		'module' => trim($log[2]),
		'clerk_id' => intval($log[3]),
		'store_id' => intval($log[4]),
		'clerk_type' => $clerk_type,
		'remark' => $log[1],
	);
	pdo_insert('mc_credits_record', $data);

	return true;
}

/*
 * 会员积分|优惠券信息变更操作员
 * */
function mc_account_change_operator($clerk_type, $store_id, $clerk_id) {
	global $stores, $clerks, $_W;
	if(empty($stores) || empty($clerks)) {
		$clerks = pdo_getall('activity_clerks', array('uniacid' => $_W['uniacid']), array('id', 'name'), 'id');
		$stores = pdo_getall('activity_stores', array('uniacid' => $_W['uniacid']), array('id', 'business_name', 'branch_name'), 'id');
	}
	$data = array(
		'clerk_cn' => '',
		'store_cn' => '',
	);
	if($clerk_type == 1) {
		$data['clerk_cn'] = '系统';
	} elseif($clerk_type == 2) {
		$data['clerk_cn'] = pdo_fetchcolumn('SELECT username FROM ' . tablename('users') . ' WHERE uid = :uid', array(':uid' => $clerk_id));
	} elseif($clerk_type == 3) {
		if (empty($clerk_id)) {
			$data['clerk_cn'] = '本人操作';
		} else {
			$data['clerk_cn'] = $clerks[$clerk_id]['name'];
		}	
		$data['store_cn'] = $stores[$store_id]['business_name'] . ' ' . $stores[$store_id]['branch_name'];
	}
	if (empty($data['store_cn'])) {
		$data['store_cn'] = '暂无门店信息';
	}
	if (empty($data['clerk_cn'])) {
		$data['clerk_cn'] = '暂无操作员信�;
	}
	return $data;
}
/**
 * 获取会员积分
 * @param int $uid 会员ID
 * @param array $types 获取积分类型
 * @return array
 */
function mc_credit_fetch($uid, $types = array()) {
	if (empty($types) || $types == '*') {
		$select = 'credit1,credit2,credit3,credit4,credit5,credit6';
	} else {
		$struct = mc_credit_types();
		foreach ($types as $key => $type) {
			if (!in_array($type, $struct)) {
				unset($types[$key]);
			}
		}
		$select = '`' . implode('`,`', $types) . '`';
	}
	return pdo_fetch("SELECT {$select} FROM ".tablename('mc_members').' WHERE uid = :uid LIMIT 1',array(':uid' => $uid));
}

/**
 * 获取会员所有积分类�
 * @return array
 */
function mc_credit_types(){
	static $struct = array('credit1','credit2','credit3','credit4','credit5','credit6');
	return $struct;
}

/**
 * 获取公众号会员组
 * @param int $uniacid 公众号ID
 * @return array 
 */
function mc_groups($uniacid = 0) {
	global $_W;
	$uniacid = intval($uniacid);
	if (empty($uniacid)) {
		$uniacid = $_W['uniacid'];
	}
	$sql = "SELECT * FROM " . tablename('mc_groups') . ' WHERE `uniacid`=:uniacid ORDER BY credit';
	return pdo_fetchall($sql, array(':uniacid' => $uniacid), 'groupid');
}

/**
 * 获取公众号粉丝标签列�
 * @return array
 */
function mc_fans_groups($force_update = false) {
	global $_W;

	$sql = "SELECT `groups` FROM " . tablename('mc_fans_groups') . ' WHERE `uniacid` = :uniacid AND acid = :acid';
	$results = pdo_fetchcolumn($sql, array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']));

	if(!empty($results) && !$force_update) {
		$results = iunserializer($results);
		return $results;
	}
	$account_api = WeAccount::create($_W['acid']);
	if (!$account_api->isTagSupported()) {
		return array();
	}
	$tags = $account_api->fansTagFetchAll();
	if (is_error($tags)) {
		message($tags['message'], '', 'error');
	}
	if (!empty($tags['tags'])) {
		$tags_tmp = array();
		foreach ($tags['tags'] as $da) {
			// '屏蔽� 跳过
			if ($da['id'] == 1) {
				continue;
			}
			$tags_tmp[$da['id']] = $da;
		}
	}
	if (empty($results)) {
		$data = array('acid' => $_W['acid'], 'uniacid' => $_W['uniacid'], 'groups' => iserializer($tags_tmp));
		pdo_insert('mc_fans_groups', $data);
	} else {
		$data = array('groups' => iserializer($tags_tmp));
		pdo_update('mc_fans_groups', $data, array('uniacid' => $_W['uniacid'], 'acid' => $_W['acid']));
	}
	return $tags_tmp;
}

/**
 * 更新当前已登录会员信�
 * @param array $member 会员信息
 * @return boolean
 */
function _mc_login($member) {
	global $_W;

	if (!empty($member) && !empty($member['uid'])) {
		$sql = 'SELECT `uid`,`realname`,`mobile`,`email`,`groupid`,`credit1`,`credit2`,`credit6` FROM ' . tablename('mc_members') . ' WHERE `uid`=:uid AND `uniacid`=:uniacid';
		$member = pdo_fetch($sql, array(':uid' => $member['uid'], ':uniacid' => $_W['uniacid']));
		if (!empty($member) && (!empty($member['mobile']) || !empty($member['email']))) {
			$_W['member'] = $member;
			$_W['member']['groupname'] = $_W['uniaccount']['groups'][$member['groupid']]['title'];
			$_SESSION['uid'] = $member['uid'];
			mc_group_update();
			if (empty($_W['openid'])) {
				$fan = mc_fansinfo($member['uid']);
				if (!empty($fan)) {
					$_SESSION['openid'] = $fan['openid'];
					$_W['openid'] = $fan['openid'];
					$_W['fans'] = $fan;
					$_W['fans']['from_user'] = $_W['openid'];
				} else {
					$_W['openid'] = $member['uid'];
					$_W['fans'] = array(
						'from_user' => $member['uid'],
						'follow' => 0
					);
				}
			}
			isetcookie('logout', '', -60000);
			return true;
		}
	}
	return false;
}

/**
 * 获取会员配置信息所有字�
 * @return array
 */
function mc_fields() {
	$fields = cache_load('usersfields');
	if (empty($fields)) {
		load()->model('cache');
		cache_build_users_struct();
		$fields = cache_load('usersfields');
	}
	return $fields;
}

/*
 * 获取公众号自定义的会员字段信�
 * $is_available 是否只获取开启的字段
 * */
function mc_acccount_fields($uniacid = 0, $is_available = true) {
	global $_W;
	if(!$uniacid) {
		$uniacid = $_W['uniacid'];
	}
	$condition = ' WHERE a.uniacid = :uniacid';
	$params = array(':uniacid' => $uniacid);
	if($is_available) {
		$condition .= ' AND a.available = 1';
	}
	$data = pdo_fetchall('SELECT a.title, b.field FROM ' . tablename('mc_member_fields') . ' AS a LEFT JOIN ' . tablename('profile_fields') . ' as b ON a.fieldid = b.id' . $condition, $params, 'field');
	$fields = array();
	foreach($data as $row) {
		$fields[$row['field']] = $row['title'];
	}
	return $fields;
}

/**
 * UC登录初始�
 * @return boolean
 */
function mc_init_uc() {
	global $_W;
	$setting = uni_setting($_W['uniacid'], array('uc'));
	if (is_array($setting['uc']) && $setting['uc']['status'] == '1') {
		$uc = $setting['uc'];
		define('UC_CONNECT', $uc['connect'] == 'mysql' ? 'mysql' : '');

		define('UC_DBHOST', $uc['dbhost']);
		define('UC_DBUSER', $uc['dbuser']);
		define('UC_DBPW', $uc['dbpw']);
		define('UC_DBNAME', $uc['dbname']);
		define('UC_DBCHARSET', $uc['dbcharset']);
		define('UC_DBTABLEPRE', $uc['dbtablepre']);
		define('UC_DBCONNECT', $uc['dbconnect']);

		define('UC_CHARSET', $uc['charset']);
		define('UC_KEY', $uc['key']);
		define('UC_API', $uc['api']);
		define('UC_APPID', $uc['appid']);
		define('UC_IP', $uc['ip']);

		require IA_ROOT . '/framework/library/uc/client.php';
		return true;
	}
	return false;
}

/**
 * 用户积分赠送模�
 * @param int $touid 赠送积分用户uid
 * @param int $fromuid 赠送积分来源用户uid(系统模块填写-1)
 * @param array $handsel 赠送信�array('module' => '', 'sign' => '', 'action' => '', 'credit_value' =>, 'credit_log' => );
 * @param string $uniacid 公众号ID 默认�_W['uniacid']
 * @return mixed
 */
function mc_handsel($touid, $fromuid, $handsel, $uniacid = '') {
	global $_W;
	$touid = intval($touid);
	$fromuid = intval($fromuid);
	if (empty($uniacid)) {
		$uniacid = $_W['uniacid'];
	}
	$touid_exist = mc_fetch($touid, array('uniacid'));
	if (empty($touid_exist)) {
		return error(-1, '赠送积分用户不存在');
	}
	if (empty($handsel['module'])) {
		return error(-1, '没有填写模块名称');
	}
	if (empty($handsel['sign'])) {
		return error(-1, '没有填写赠送积分对象信�);
	}
	if (empty($handsel['action'])) {
		return error(-1, '没有填写赠送积分动�);
	}
	$credit_value = intval($handsel['credit_value']);

	$sql = 'SELECT id FROM ' . tablename('mc_handsel') . ' WHERE uniacid = :uniacid AND touid = :touid AND fromuid = :fromuid AND module = :module AND sign = :sign AND action = :action';
	$parm = array(':uniacid' => $uniacid, ':touid' => $touid, ':fromuid' => $fromuid, ':module' => $handsel['module'], ':sign' => $handsel['sign'], ':action' => $handsel['action']);
	$handsel_exists = pdo_fetch($sql, $parm);
	if (!empty($handsel_exists)) {
		return error(-1, '已经赠送过积分,每个用户只能赠送一�);
	}

	$creditbehaviors = pdo_fetchcolumn('SELECT creditbehaviors FROM ' . tablename('uni_settings') . ' WHERE uniacid = :uniacid', array(':uniacid' => $uniacid));
	$creditbehaviors = iunserializer($creditbehaviors) ? iunserializer($creditbehaviors) : array();
	if (empty($creditbehaviors['activity'])) {
		return error(-1, '公众号没有配置积分行为参�);
	} else {
		$credittype = $creditbehaviors['activity'];
	}

	$data = array(
		'uniacid' => $uniacid,
		'touid' => $touid,
		'fromuid' => $fromuid,
		'module' => $handsel['module'],
		'sign' => $handsel['sign'],
		'action' => $handsel['action'],
		'credit_value' => $credit_value,
		'createtime' => TIMESTAMP
	);
	pdo_insert('mc_handsel', $data);
	$log = array($fromuid, $handsel['credit_log']);
	mc_credit_update($touid, $credittype, $credit_value, $log);
	return true;
}

/**
 * 将粉丝openid转换为会员uid
 * @param mixed $openid 粉丝Openid
 * @return mixed
 */
function mc_openid2uid($openid) {
	global $_W;
	if (is_numeric($openid)) {
		return $openid;
	}
	if (is_string($openid)) {
		$sql = 'SELECT uid FROM ' . tablename('mc_mapping_fans') . ' WHERE `uniacid`=:uniacid AND `openid`=:openid';
		$pars = array();
		$pars[':uniacid'] = $_W['uniacid'];
		$pars[':openid'] = $openid;
		$uid = pdo_fetchcolumn($sql, $pars);
		return $uid;
	}
	if (is_array($openid)) {
		$uids = array();
		foreach ($openid as $k => $v) {
			if (is_numeric($v)) {
				$uids[] = $v;
			} elseif (is_string($v)) {
				$fans[] = $v;
			}
		}
		if (!empty($fans)) {
			$sql = 'SELECT uid, openid FROM ' . tablename('mc_mapping_fans') . " WHERE `uniacid`=:uniacid AND `openid` IN ('" . implode("','", $fans) . "')";
			$pars = array(':uniacid' => $_W['uniacid']);
			$fans = pdo_fetchall($sql, $pars, 'uid');
			$fans = array_keys($fans);
			$uids = array_merge((array)$uids, $fans);
		}
		return $uids;
	}
	return false;
}

/*
 * 更新会员所在的会员�
 * */
function mc_group_update($uid = 0) {
	global $_W;
	if(!$_W['uniaccount']['grouplevel']) {
		return true;
	}
	$uid = intval($uid);
	if($uid <= 0) {
		$uid = $_W['member']['uid'];
		$user = $_W['member'];
		$user['openid'] = $_W['openid'];
	} else {
		$user = pdo_fetch('SELECT uid, realname, credit1, credit6, groupid FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid AND uid = :uid', array(':uniacid' => $_W['uniacid'], ':uid' => $uid));
		$user['openid'] = pdo_fetchcolumn('SELECT openid FROM ' . tablename('mc_mapping_fans') . ' WHERE acid = :acid AND uid = :uid', array(':acid' => $_W['acid'], ':uid' => $uid));
	}
	if(empty($user)) {
		return false;
	}
	$groupid = $user['groupid'];
	$credit = $user['credit1'] + $user['credit6'];
	$groups = $_W['uniaccount']['groups'];
	if(empty($groups)) {
		return false;
	}
	$data = array();
	foreach($groups as $group) {
		$data[$group['groupid']] = $group['credit'];
	}
	asort($data);
	if($_W['uniaccount']['grouplevel'] == 1) {
		//根据积分多少变更
		foreach($data as $k => $da) {
			if($credit >= $da) {
				$groupid = $k;
			}
		}
	} else {
		//只升不降
		$now_group_credit = $data[$user['groupid']];
		if($now_group_credit < $credit) {
			foreach($data as $k => $da) {
				if($credit >= $da) {
					$groupid = $k;
				}
			}
		}
	}
	if($groupid > 0 && $groupid != $user['groupid']) {
		pdo_update('mc_members', array('groupid' => $groupid), array('uniacid' => $_W['uniacid'], 'uid' => $uid));
		mc_notice_group($user['openid'], $_W['uniaccount']['groups'][$user['groupid']]['title'], $_W['uniaccount']['groups'][$groupid]['title']);
	}
	$user['groupid'] = $groupid;
	$_W['member']['groupid'] = $groupid;
	$_W['member']['groupname'] = $_W['uniaccount']['groups'][$groupid]['title'];
	return $user['groupid'];
}

function mc_notice_init() {
	global $_W;
	if(empty($_W['account'])) {
		$_W['account'] = uni_fetch($_W['uniacid']);
	}
	if(empty($_W['account'])) {
		return error(-1, '创建公众号操作类失败');
	}
	if($_W['account']['level'] < 3) {
		return error(-1, '公众号没有经过认证，不能使用模板消息和客服消�);
	}
	$acc = WeAccount::create();
	if(is_null($acc)) {
		return error(-1, '创建公众号操作对象失�);
	}
	$setting = uni_setting();
	$noticetpl = $setting['tplnotice'];
	$acc->noticetpl = $noticetpl;
	if(!is_array($acc->noticetpl)) {
		return error(-1, '微信通知参数错误');
	}
	return $acc;
}

/*
 * 公用模板消息
 * $title 消息标题,eg:职位审核通知
 * $sender 发件�
 * $content 消息内容
 * $url 跳转链接
 * $remark 备注
 * */
function mc_notice_public($openid, $title, $sender, $content, $url = '', $remark = '') {
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	$data = array(
		'first' => array(
			'value' => $title,
			'color' => '#ff510'
		),
		'keyword1' => array(
			'value' => $sender,
			'color' => '#ff510'
		),
		'keyword2' => array(
			'value' => $content,
			'color' => '#ff510'
		),
		'remark' => array(
			'value' => $remark,
			'color' => '#ff510'
		),
	);
	$status = $acc->sendTplNotice($openid, $acc->noticetpl['public'], $data, $url);
	return $status;
}

/*
 * 会员余额充值微信模板消息和客服消息通知
 * $openid  粉丝openid
 * $uid     会员uid 默认为当前会�
 * $num     充值金额。单位（元）
 * $url     跳转链接
 * $remark  操作备注
 * */
function mc_notice_recharge($openid, $uid = 0, $num = 0, $url = '', $remark = '') {
	global $_W;
	if(!$uid) {
		$uid = $_W['member']['uid'];
	}
	if(!$uid || !$num || empty($openid)) {
		return error(-1, '参数错误');
	}
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['recharge']['tpl'])) {
		return error(-1, '未开启通知');
	}
	$credit = mc_credit_fetch($uid);
	$time = date('Y-m-d H:i');
	if(empty($url)) {
		$url = murl('mc/bond/credits', array('credittype' => 'credit2', 'type' => 'record', 'period' => '1'), true, true);
	}
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您在{$time}进行会员余额充值，充值金额{$num}元，充值后余额为{$credit['credit2']}�,
				'color' => '#ff510'
			),
			'accountType' => array(
				'value' => '会员UID',
				'color' => '#ff510'
			),
			'account' => array(
				'value' => $uid,
				'color' => '#ff510'
			),
			'amount' => array(
				'value' => $num . '�,
				'color' => '#ff510'
			),
			'result' => array(
				'value' => '充值成�,
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['recharge']['tpl'], $data, $url);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}】充值通知\n";
		$info .= "您在{$time}进行会员余额充值，充值金额【{$num}】元，充值后余额【{$credit['credit2']}】元。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}

/*
 * 会员余额变更通知
 * $openid          粉丝openid
 * $uid             会员uid 默认为当前会�
 * $credit2_num     消费金额。单位（元）
 * $credit1_num     获得积分
 * $store           消费门店
 * $url             跳转链接
 * $remark          备注
 * */
function mc_notice_credit2($openid, $uid, $credit2_num, $credit1_num = 0, $store = '线下消费', $url = '', $remark = '谢谢惠顾，点击查看详�) {
	global $_W;
	if(!$uid) {
		$uid = $_W['member']['uid'];
	}
	if(!$uid || !$credit2_num || empty($openid)) {
		return error(-1, '参数错误');
	}
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['credit2']['tpl'])) {
		return error(-1, '未开启通知');
	}
	$credit = mc_credit_fetch($uid);
	$time = date('Y-m-d H:i');
	if(empty($url)) {
		$url = murl('mc/bond/credits', array('credittype' => 'credit2', 'type' => 'record', 'period' => '1'), true, true);
	}
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您在{$time}有余额消�,
				'color' => '#ff510'
			),
			'keyword1' => array(
				'value' => abs($credit2_num) . '�,
				'color' => '#ff510'
			),
			'keyword2' => array(
				'value' => floatval($credit1_num) . '积分',
				'color' => '#ff510'
			),
			'keyword3' => array(
				'value' => trim($store),
				'color' => '#ff510'
			),
			'keyword4' => array(
				'value' => $credit['credit2'] . '�,
				'color' => '#ff510'
			),
			'keyword5' => array(
				'value' => $credit['credit1'] . '积分',
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['credit2']['tpl'], $data, $url);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}】消费通知\n";
		$info .= "您在{$time}进行会员余额消费，消费金额【{$credit2_num}】元，获得积分【{$credit1_num}�消费后余额【{$credit['credit2']}】元，消费后积分【{$credit['credit1']}】。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}

/*
 * 会员积分变更通知
 * $openid          粉丝openid
 * $uid             会员uid 默认为当前会�
 * $credit1_num     积分数量。如果是消耗积分，使用负数即可。即�1000
 * $tip             积分变更原因
 * $url             跳转链接
 * $remark          备注
 * */
function mc_notice_credit1($openid, $uid, $credit1_num, $tip, $url = '', $remark = '谢谢惠顾，点击查看详�) {
	global $_W;
	if(!$uid) {
		$uid = $_W['member']['uid'];
	}
	if(!$uid || !$credit1_num || empty($tip)) {
		return error(-1, '参数错误');
	}
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['credit1']['tpl'])) {
		return error(-1, '未开启通知');
	}
	$credit = mc_credit_fetch($uid);
	$time = date('Y-m-d H:i');
	if(empty($url)) {
		$url = murl('mc/bond/credits', array('credittype' => 'credit1', 'type' => 'record', 'period' => '1'), true, true);
	}
	$credit1_num = floatval($credit1_num);
	$type = '消费';
	if($credit1_num > 0) {
		$type = '到账';
	}
	$username = $_W['member']['realname'];
	if(empty($username)) {
		$username = $_W['member']['nickname'];
	}
	if(empty($username)) {
		$username = $uid;
	}
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您在{$time}有积分变�,
				'color' => '#ff510'
			),
			'account' => array(
				'value' => $username,
				'color' => '#ff510'
			),
			'time' => array(
				'value' => $time,
				'color' => '#ff510'
			),
			'type' => array(
				'value' => $tip,
				'color' => '#ff510'
			),
			'creditChange' => array(
				'value' => $type,
				'color' => '#ff510'
			),
			'number' => array(
				'value' => abs($credit1_num) . '积分',
				'color' => '#ff510'
			),
			'creditName' => array(
				'value' => '账户积分',
				'color' => '#ff510'
			),
			'amount' => array(
				'value' => abs($credit['credit1']) . '积分',
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['credit1']['tpl'], $data, $url);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}】积分变更通知\n";
		$info .= "您在{$time}有积分{$type}，{$type}积分【{$credit1_num}】，变更原因：【{$tip}�消费后账户积分余额【{$credit['credit1']}】。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}

function mc_notice_group($openid, $old_group, $now_group, $url = '', $remark = '点击查看详情') {
	global $_W;
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['group']['tpl'])) {
		return error(-1, '未开启通知');
	}
	$time = date('Y-m-d H:i');
	if(empty($url)) {
		$url = murl('mc/home', array(), true, true);
	}
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您的会员组变更为{$now_group}",
				'color' => '#ff510'
			),
			'grade1' => array(
				'value' => $old_group,
				'color' => '#ff510'
			),
			'grade2' => array(
				'value' => $now_group,
				'color' => '#ff510'
			),
			'time' => array(
				'value' => $time,
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}",
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['group']['tpl'], $data, $url);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}】会员组变更通知\n";
		$info .= "您的会员等级在{$time}由{$old_group}变更为{$now_group}。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}

/*
 * 会员卡计次充�
 * $openid          粉丝openid
 * $uid             会员uid
 * $type            冲次项目（eg:理发计次�
 * $num             本次充值次�
 * $total_num       总剩余次�
 * $endtime         服务截至日期
 * $remark          备注
 * */
function mc_notice_nums_plus($openid, $type, $num, $total_num, $remark = '感谢您的支持，祝您生活愉快！') {
	global $_W;
	if(empty($num) || empty($total_num) || empty($type)) {
		return error(-1, '参数错误');
	}
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['nums_plus']['tpl'])) {
		return error(-1, '未开启通知');
	}
	$time = date('Y-m-d H:i');
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您的{$type}已充次成�,
				'color' => '#ff510'
			),
			'keyword1' => array(
				'value' => $time,
				'color' => '#ff510'
			),
			'keyword2' => array(
				'value' => $num . '�,
				'color' => '#ff510'
			),
			'keyword3' => array(
				'value' => $total_num . '�,
				'color' => '#ff510'
			),
			'keyword4' => array(
				'value' => '用完为止',
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['nums_plus']['tpl'], $data);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}�【{$type}】充值通知\n";
		$info .= "您的{$type}已充值成功，本次充次【{$num}】次，总剩余【{$total_num}】次。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}

/*
 * 会员卡计次消�
 * $openid          粉丝openid
 * $uid             会员uid
 * $card_id         卡号
 * $type            消费项目（eg:理发计次�
 * $num             剩余次数
 * $endtime         服务截至日期
 * $remark          备注
 * */
function mc_notice_nums_times($openid, $card_id, $type, $num, $remark = '感谢您对本店的支持，欢迎下次再来�) {
	global $_W;
	if(empty($num) || empty($type) || empty($card_id)) {
		return error(-1, '参数错误');
	}
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['nums_times']['tpl'])) {
		return error(-1, '未开启通知');
	}
	$time = date('Y-m-d H:i');
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您的{$type}已成功使用了�】次�,
				'color' => '#ff510'
			),
			'keyword1' => array(
				'value' => $card_id,
				'color' => '#ff510'
			),
			'keyword2' => array(
				'value' => $time,
				'color' => '#ff510'
			),
			'keyword3' => array(
				'value' => $num . '�,
				'color' => '#ff510'
			),
			'keyword4' => array(
				'value' => '用完为止',
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['nums_times']['tpl'], $data);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}�【{$type}】消费通知\n";
		$info .= "您的{$type}已成功使用了一次，总剩余【{$num}】次，消费时间【{$time}】。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}

/*
 * 会员卡计时充�
 * $openid          粉丝openid
 * $uid             会员uid
 * $card_id         卡号
 * $type            消费项目（eg:理发计次�
 * $num             剩余次数
 * $endtime         服务截至日期
 * $remark          备注
 * */
function mc_notice_times_plus($openid, $card_id, $type, $fee, $days, $endtime = '', $remark = '感谢您对本店的支持，欢迎下次再来�) {
	global $_W;
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['times_plus']['tpl'])) {
		return error(-1, '未开启通知');
	}
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您的{$type}已续费成功�,
				'color' => '#ff510'
			),
			'keynote1' => array(
				'value' => $type,
				'color' => '#ff510'
			),
			'keynote2' => array(
				'value' => $card_id,
				'color' => '#ff510'
			),
			'keynote3' => array(
				'value' => $fee . '�,
				'color' => '#ff510'
			),
			'keynote4' => array(
				'value' => $days . '�,
				'color' => '#ff510'
			),
			'keynote5' => array(
				'value' => $endtime,
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['times_plus']['tpl'], $data);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}�【{$type}】续费通知\n";
		$info .= "您的{$type}已成功续费，续费时长【{$days}】天，续费金额【{$fee}】元，有效期至【{$endtime}】。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}

/*
 * 会员服务到期提醒
 * $openid          粉丝openid
 * $title           服务说明（eg:您好，您有商品即将到期，请您注意。）
 * $type            服务项目（eg:游泳时长�
 * $endtime         服务截至日期
 * $remark          备注
 * */
function mc_notice_times_times($openid, $title, $type, $endtime = '', $remark = '请注意时间，防止服务失效�) {
	global $_W;
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['times_times']['tpl'])) {
		return error(-1, '未开启通知');
	}

	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => $title,
				'color' => '#ff510'
			),
			'name' => array(
				'value' => $type,
				'color' => '#ff510'
			),
			'expDate' => array(
				'value' => $endtime,
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['times_times']['tpl'], $data);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}�【{$type}】服务到期通知\n";
		$info .= "您的{$type}即将到期，有效期至【{$endtime}】。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}

/*
 * 会员消费通知
 * */
function mc_notice_consume($openid, $title, $content, $url = '') {
	global $_W;
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if($_W['account']['level'] == 4) {
		mc_notice_credit2($openid, $content['uid'], $content['credit2_num'], $content['credit1_num'], $content['store'], '', $content['remark']);
	}
	if($_W['account']['level'] == 3) {
		mc_notice_custom_text($openid, $title, $content);
	}
	return true;
}
/*
 * 发送文本类型的客服消息
 * */
function mc_notice_custom_text($openid, $title, $info) {
	global $_W;
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	$custom = array(
		'msgtype' => 'text',
		'text' => array('content' => urlencode($title . '\n' . $info)),
		'touser' => $openid,
	);
	$status = $acc->sendCustomNotice($custom);
	return $status;
}

function mc_plugins() {
	$plugins = array(
		'mc_card_manage' => array(
			'title' => '会员�,
			'name' => 'mc_card_manage',
			'description' => '提供粉丝可开通会员卡并可以设置充值、消费金额及积分的增减策�,
		),
		'activity_discount_manage' => array(
			'title' => '兑换中心',
			'name' => 'activity_discount_manage',
			'description' => '提供粉丝可通过积分进行代金劵、折扣劵或是真实物品的兑�,
		),
		'wechat_card_manage' => array(
			'title' => '微信卡券',
			'name' => 'wechat_card_manage',
			'description' => '提供粉丝可通过积分进行代金劵、折扣劵或是真实物品的兑�,
		),

	);
	return $plugins;
}

/**
 * 初始化粉丝个人信�
 * @param 	array 		$old_fan_info 	粉丝之前信息
 */
function mc_init_fans_info($old_fan_info){
	static $account_api;
	if (empty($account_api)) {
		$account_api = WeAccount::create();
	}
	$fan = $account_api->fansQueryInfo($old_fan_info['openid'], true);
	if (!is_error($fan) && $fan['subscribe'] == 1) {
		$fan['nickname'] = stripcslashes($fan['nickname']);
		$fan['remark'] = !empty($fan['remark']) ? stripslashes($fan['remark']) : '';
		$record = array(
			'updatetime' => TIMESTAMP,
			'followtime' => $fan['subscribe_time'],
			'nickname' => stripcslashes($fan['nickname']),
			'tag' => base64_encode(iserializer($fan)),
			'unionid' => $fan['unionid'],
			'groupid' => !empty($fan['tagid_list']) ? (','.join(',', $fan['tagid_list']).',') : '',
		);
		if (!empty($fan['tagid_list'])) {
			$tagid_arr = $fan['tagid_list'];
			sort($tagid_arr, SORT_NATURAL);
			mc_insert_fanstag_mapping($old_fan_info['fanid'], $tagid_arr);
		}
		pdo_update('mc_mapping_fans', $record, array('fanid' => $old_fan_info['fanid']));
		
		if (!empty($old_fan_info['uid'])) {
			$user = mc_fetch($old_fan_info['uid'], array('nickname', 'gender', 'residecity', 'resideprovince', 'nationality', 'avatar'));
			$rec = array();
			if (empty($user['nickname']) && !empty($fan['nickname'])) {
				$rec['nickname'] = stripslashes($fan['nickname']);
			}
			if (empty($user['gender']) && !empty($fan['sex'])) {
				$rec['gender'] = $fan['sex'];
			}
			if (empty($user['residecity']) && !empty($fan['city'])) {
				$rec['residecity'] = $fan['city'] . '�;
			}
			if (empty($user['resideprovince']) && !empty($fan['province'])) {
				$rec['resideprovince'] = $fan['province'] . '�;
			}
			if (empty($user['nationality']) && !empty($fan['country'])) {
				$rec['nationality'] = $fan['country'];
			}
			if (empty($user['avatar']) && !empty($fan['headimgurl'])) {
				$rec['avatar'] = rtrim($fan['headimgurl'], '0') . 132;
			}
			if (!empty($rec)) {
				pdo_update('mc_members', $rec, array('uid' => $old_fan_info['uid']));
			}
		}
	} elseif (!is_error($fan) && empty($fan['subscribe'])) {
		pdo_update('mc_mapping_fans', array('follow' => 0, 'unfollowtime' => TIMESTAMP), array('fanid' => $old_fan_info['fanid']));
	}
	return true;
}

/**
 * 插入 mc_fans_tag_mapping，fanid-tagid 对应关系
 * @param 	int 		$fanid 		粉丝id
 * @param 	array|int 	$tagid_arr 	标签id列表
 */
function mc_insert_fanstag_mapping($fanid, $tagid_arr){
	$tagid_arr = (array) $tagid_arr;
	foreach ($tagid_arr as $tagid) {
		$record_mapping = array(
			'fanid' => $fanid,
			'tagid' => $tagid
		);
		pdo_insert('mc_fans_tag_mapping', $record_mapping, true);
	}
}

/**
 * 批量插入 mc_fans_tag_mapping，fanid-tagid 对应关系
 * @param 	array 		$fanid_list 		粉丝id列表
 * @param 	array 		$tagid_list 		标签id列表
 */
function mc_batch_insert_fanstag_mapping($fanid_list, $tagid_list){
	$fanid_list = (array) $fanid_list;
	$tagid_list = (array) $tagid_list;
	$sql = '';
	foreach ($fanid_list as $fanid) {
		foreach ($tagid_list as $tagid) {
			$sql .= "REPLACE INTO " . tablename('mc_fans_tag_mapping') . "(`fanid`, `tagid`) values('$fanid', '$tagid');";
		}
	}
	pdo_query($sql);
}

/**
 * 展示粉丝标签
 * @param 	string 		$groupid 			粉丝groupid字段
 * @return 	string 		$tag_show
 */
function mc_show_tag($groupid){
	if ($groupid) {
		$fans_tag = mc_fans_groups();
		$tagid_arr = explode(',', trim($groupid, ','));
		foreach ($tagid_arr as $tagid) {
			$tag_show .= $fans_tag[$tagid]['name'] . ', ';
		}
		$tag_show = rtrim($tag_show, ', ');
	} else {
		$tag_show = '无标�;
	}
	return $tag_show;
}
/**
 * 会员卡个人资料设置是否显�
 * @param 	personal_info, contact_method, education_info, jobedit
 * @return 	boolean
 */
function mc_card_settings_hide($item = '') {
	$mcFields = mc_acccount_fields();
	if ($item == 'personal_info') {
		if (empty($mcFields['idcard']) && empty($mcFields['height']) && empty($mcFields['weight']) && empty($mcFields['bloodtype']) && empty($mcFields['zodiac']) && empty($mcFields['constellation']) && empty($mcFields['site']) && empty($mcFields['affectivestatus']) && empty($mcFields['lookingfor']) && empty($mcFields['bio']) && empty($mcFields['interest'])) {
			return true;
		}
	} elseif ($item == 'contact_method') {
		if (empty($mcFields['telephone']) && empty($mcFields['qq']) && empty($mcFields['msn']) && empty($mcFields['taobao']) && empty($mcFields['alipay'])) {
			return true;
		}
	} elseif ($item == 'education_info') {
		if (empty($mcFields['education']) && empty($mcFields['graduateschool']) && empty($mcFields['studentid'])) {
			return true;
		}
	} elseif ($item == 'jobedit') {
		if (empty($mcFields['company']) && empty($mcFields['occupation']) && empty($mcFields['position']) && empty($mcFields['revenue'])) {
			return true;
		}
	} elseif (empty($item)) {
		if (empty($mcFields['idcard']) && empty($mcFields['height']) && empty($mcFields['weight']) 
		&& empty($mcFields['bloodtype']) && empty($mcFields['zodiac']) && empty($mcFields['constellation']) 
		&& empty($mcFields['site']) && empty($mcFields['affectivestatus']) && empty($mcFields['lookingfor']) 
		&& empty($mcFields['bio']) && empty($mcFields['interest']) && empty($mcFields['telephone']) 
		&& empty($mcFields['qq']) && empty($mcFields['msn']) && empty($mcFields['taobao']) 
		&& empty($mcFields['alipay']) && empty($mcFields['education']) && empty($mcFields['graduateschool']) 
		&& empty($mcFields['studentid']) && empty($mcFields['company']) && empty($mcFields['occupation']) 
		&& empty($mcFields['position']) && empty($mcFields['revenue']) && empty($mcFields['avatar']) 
		&& empty($mcFields['nickname']) && empty($mcFields['realname']) && empty($mcFields['gender']) 
		&& empty($mcFields['birthyear']) && empty($mcFields['resideprovince'])) {
			return true;
		}
	}
	return false;
}

/**
 * 用户消费返积�
 * @param 	string 		$openid 		粉丝openid字段
 * @param 	string 		$card_fee 		core_paylog表card_fee 使用卡券后的价格
 * @param 	string 		$storeid 		消费门店id
 */
function mc_card_grant_credit($openid, $card_fee, $storeid = 0) {
	global $_W;
	$setting = uni_setting($_W['uniacid'], array('creditbehaviors'));
	load()->model('card');
	$recharges_set = card_params_setting('cardRecharge');
	$card_settings = card_setting();
	$grant_rate = $card_settings['grant_rate'];
	$grant_rate_switch = intval($recharges_set['params']['grant_rate_switch']);
	$grant_credit1_enable = false;
	if (!empty($grant_rate)) {
		if (empty($recharges_set['params']['recharge_type'])) {
			$grant_credit1_enable = true;
		} else {
			if ($grant_rate_switch == '1') {
				$grant_credit1_enable = true;
			}
		}
	}
	if (!empty($grant_credit1_enable)) {
		$num = $card_fee * $grant_rate;
		$tips .= "用户消费{$card_fee}元，余额支付{$card_fee}，积分赠送比率为:�：{$grant_rate}�共赠送【{$num}】积�;
		mc_credit_update($openid, 'credit1', $num, array('0', $tip, 'paycenter', 0, $storeid, 3));
		return error(0, $num);
	} else {
		return error(-1, '');
	}
}
/**
 * 用户消费返积�
 * @param 	string 		$openid 		粉丝openid字段
 * @param 	string 		$card_fee 		core_paylog表card_fee 使用卡券后的价格
 * @param 	string 		$storeid 		消费门店id
 */
function mc_card_grant_credit($openid, $card_fee, $storeid = 0) {
	global $_W;
	$setting = uni_setting($_W['uniacid'], array('creditbehaviors'));
	load()->model('card');
	$recharges_set = card_params_setting('cardRecharge');
	$card_settings = card_setting();
	$grant_rate = $card_settings['grant_rate'];
	$grant_rate_switch = intval($recharges_set['params']['grant_rate_switch']);
	$grant_credit1_enable = false;
	if (!empty($grant_rate)) {
		if (empty($recharges_set['params']['recharge_type'])) {
			$grant_credit1_enable = true;
		} else {
			if ($grant_rate_switch == '1') {
				$grant_credit1_enable = true;
			}
		}
	}
	if (!empty($grant_credit1_enable)) {
		$num = $card_fee * $grant_rate;
		$tips .= "用户消费{$card_fee}元，余额支付{$card_fee}，积分赠送比率为:�：{$grant_rate}�共赠送【{$num}】积�;
		mc_credit_update($openid, 'credit1', $num, array('0', $tip, 'paycenter', 0, $storeid, 3));
		return error(0, $num);
	} else {
		return error(-1, '');
	}
}