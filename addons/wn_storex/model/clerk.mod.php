<?php
function storex_clerk_permission_list() {
	$data = array(
		'mc' => array(
			'title' => '快捷交易',
			'permission' => 'mc_manage',
			'items' => array(
				array(
					'title' => '积分充值',
					'permission' => 'mc_credit1',
					'icon' => 'fa fa-money',
					'type' => 'modal',
					'modal' => 'modal-trade',
					'data' => 'credit1',
				),
				array(
					'title' => '余额充值',
					'permission' => 'mc_credit2',
					'icon' => 'fa fa-cny',
					'type' => 'modal',
					'modal' => 'modal-trade',
					'data' => 'credit2',
				),
				array(
					'title' => '消费',
					'permission' => 'mc_consume',
					'icon' => 'fa fa-usd',
					'type' => 'modal',
					'modal' => 'modal-trade',
					'data' => 'consume',
				),
				array(
					'title' => '发放会员卡',
					'permission' => 'mc_card',
					'icon' => 'fa fa-credit-card',
					'type' => 'modal',
					'modal' => 'modal-trade',
					'data' => 'card',
				),
			)
		),

		'stat' => array(
			'title' => '数据统计',
			'permission' => 'stat_manage',
			'items' => array(
				array(
					'title' => '积分统计',
					'permission' => 'stat_credit1',
					'icon' => 'fa fa-bar-chart',
					'type' => 'url',
					'url' => './index.php?c=stat&a=credit1'
				),
				array(
					'title' => '余额统计',
					'permission' => 'stat_credit2',
					'icon' => 'fa fa-bar-chart',
					'type' => 'url',
					'url' => './index.php?c=stat&a=credit2'
				),
				array(
					'title' => '现金消费统计',
					'permission' => 'stat_cash',
					'icon' => 'fa fa-bar-chart',
					'type' => 'url',
					'url' => './index.php?c=stat&a=cash'
				),
				array(
					'title' => '会员卡统计',
					'permission' => 'stat_card',
					'icon' => 'fa fa-bar-chart',
					'type' => 'url',
					'url' => './index.php?c=stat&a=card'
				),
				array(
					'title' => '收银台收款统计',
					'permission' => 'stat_paycenter',
					'icon' => 'fa fa-bar-chart',
					'type' => 'url',
					'url' => './index.php?c=stat&a=paycenter'
				),
			)
		),

		'activity' => array(
			'title' => '系统优惠券核销',
			'permission' => 'activity_card_manage',
			'items' => array(
				array(
					'title' => '折扣券核销',
					'permission' => 'activity_consume_coupon',
					'icon' => 'fa fa-money',
					'type' => 'url',
					'url' => './index.php?c=activity&a=consume&do=display&type=1'
				),
				array(
					'title' => '代金券核销',
					'permission' => 'activity_consume_token',
					'icon' => 'fa fa-money',
					'type' => 'url',
					'url' => './index.php?c=activity&a=consume&do=display&type=2'
				),
			)
		),

		'wechat' => array(
			'title' => '微信卡券核销',
			'permission' => 'wechat_card_manage',
			'items' => array(
				array(
					'title' => '卡券核销',
					'permission' => 'wechat_consume',
					'icon' => 'fa fa-money',
					'type' => 'url',
					'url' => './index.php?c=wechat&a=consume'
				)
			)
		),

		'paycenter' => array(
			'title' => '收银台',
			'permission' => 'paycenter_manage',
			'items' => array(
				array(
					'title' => '微信刷卡收款',
					'permission' => 'paycenter_wxmicro_pay',
					'icon' => 'fa fa-money',
					'type' => 'url',
					'url' => './index.php?c=paycenter&a=wxmicro&do=pay'
				)
			)
		),
	);
	return $data;
}

/**
 * 获取单条用户信息，如果查询参数多于一个字段，则查询满足所有字段的用户
 * PS:密码字段不要加密
 * @param array $user_or_uid 要查询的用户字段，可以包括  uid, username, password, status
 * @return array 完整的用户信息
 */
function storex_user_single($user_or_uid) {
	$user = $user_or_uid;
	if (empty($user)) {
		return false;
	}
	if (is_numeric($user)) {
		$user = array('uid' => $user);
	}
	if (!is_array($user)) {
		return false;
	}
	$where = ' WHERE 1 ';
	$params = array();
	if (!empty($user['uid'])) {
		$where .= ' AND `uid` = :uid';
		$params[':uid'] = intval($user['uid']);
	}
	if (!empty($user['username'])) {
		$where .= ' AND `username` = :username';
		$params[':username'] = $user['username'];
	}
	if (!empty($user['email'])) {
		$where .= ' AND `email` = :email';
		$params[':email'] = $user['email'];
	}
	if (!empty($user['status'])) {
		$where .= " AND `status` = :status";
		$params[':status'] = intval($user['status']);
	}
	if (empty($params)) {
		return false;
	}
	$sql = 'SELECT * FROM ' . tablename('users') . " $where LIMIT 1";
	$record = pdo_fetch($sql, $params);
	if (empty($record)) {
		return false;
	}
	if (!empty($user['password'])) {
		$password = user_hash($user['password'], $record['salt']);
		if ($password != $record['password']) {
			return false;
		}
	}
	if ($record['type'] == ACCOUNT_OPERATE_CLERK) {
		$clerk = pdo_get('storex_activity_clerks', array('uid' => $record['uid']));
		if (!empty($clerk)) {
			$record['name'] = $clerk['name'];
			$record['clerk_id'] = $clerk['id'];
			$record['store_id'] = $clerk['storeid'];
			$record['store_name'] = pdo_fetchcolumn('SELECT business_name FROM ' . tablename('storex_activity_stores') . ' WHERE id = :id', array(':id' => $clerk['storeid']));
			$record['clerk_type'] = '3';
			$record['uniacid'] = $clerk['uniacid'];
		}
	} else {
		//clerk_type 操作人类型,1: 线上操作 2: 系统后台(公众号管理员和操作员) 3: 店员
		$record['name'] = $user['username'];
		$record['clerk_id'] = $user['uid'];
		$record['store_id'] = 0;
		$record['clerk_type'] = '2';
	}
	return $record;
}

function storex_user_permission_exist($uid = 0, $uniacid = 0) {
	global $_W;
	$uid = intval($uid) > 0 ? $uid : $_W['uid'];
	$uniacid = intval($uniacid) > 0 ? $uniacid : $_W['uniacid'];
	if ($_W['role'] == 'founder' || $_W['role'] == 'manager') {
		return true;
	}
	$is_exist = pdo_fetch('SELECT id FROM ' . tablename('storex_users_permission') . ' WHERE `uid` = :uid AND `uniacid` = :uniacid', array(':uid' => $uid, ':uniacid' => $uniacid));
	if (empty($is_exist)) {
		if ($_W['role'] != 'clerk') {
			return true;
		} else {
			return error(-1, '');
		}
	} else {
		return error(-1, '');
	}
}
/*
 * 默认获取某个操作员对于某个公众号的权限
* $type => 'system' 获取系统菜单权限
* */
function storex_user_permission($type = 'system', $uid = 0, $uniacid = 0) {
	global $_W;
	$uid = empty($uid) ? $_W['uid'] : intval($uid);
	$uniacid = empty($uniacid) ? $_W['uniacid'] : intval($uniacid);
	$sql = 'SELECT `permission` FROM ' . tablename('storex_users_permission') . ' WHERE `uid` = :uid AND `uniacid` = :uniacid AND `type` = :type';
	$pars = array();
	$pars[':uid'] = $uid;
	$pars[':uniacid'] = $uniacid;
	$pars[':type'] = $type;
	$data = pdo_fetchcolumn($sql, $pars);
	$permission = array();
	if (!empty($data)) {
		$permission = explode('|', $data);
	}
	return $permission;
}

//获取店员权限
function clerk_permission($storeid, $uid) {
	global $_W;
	$clerk_info = pdo_get('storex_clerk', array('weid' => $_W['uniacid'], 'userid' => $uid, 'storeid' => $storeid), array('permission'));
	$current_user_permission_info = pdo_get('users_permission', array('uniacid' => $_W['uniacid'], 'uid' => $uid, 'type' => 'wn_storex'));
	pdo_update('storex_clerk', array('permission' => $current_user_permission_info['permission']), array('weid' => $_W['uniacid'], 'storeid' => $storeid, 'userid' => $uid));
	$permission = !empty($current_user_permission_info['permission']) ? explode('|', $current_user_permission_info['permission']) : '';
	return $permission;
}

/**店员可操作订单的行为
* $order 订单信息
* $store_type 店铺类型
*/
function clerk_order_operation($order, $store_type) {
	$status = array(
		'is_cancel' => false,
		'is_confirm' => false,
		'is_refuse' => false,
		'is_over' => false,
		'is_send' => false,
		'is_access' => false,
		'is_assign' => false,
	);
	if ($order['status'] == ORDER_STATUS_CANCEL || $order['status'] == ORDER_STATUS_REFUSE) {
		$status = array();
	} elseif ($order['status'] == ORDER_STATUS_SURE) {
		if ($order['paystatus'] == PAY_STATUS_PAID) {
			if ($store_type == STORE_TYPE_HOTEL) {
				$room = pdo_get('storex_room', array('id' => $order['roomid']), array('id', 'is_house'));
				if (($order['goods_status'] == GOODS_STATUS_NOT_CHECKED || empty($order['goods_status'])) && $room['is_house'] == 1) {
					$status['is_access'] = true;
					$status['is_assign'] = true;
				}
			} else {
				if ($order['mode_distribute'] == 2) {//配送
					if ($order['goods_status'] == GOODS_STATUS_NOT_SHIPPED || empty($order['goods_status'])) {
						$status['is_send'] = true;
					}
				}
			}
			$status['is_over'] = true;
		}
	} elseif ( $order['status'] == ORDER_STATUS_OVER){
		$status = array();
	}else {
		$status['is_cancel'] = true;
		$status['is_confirm'] = true;
		$status['is_refuse'] = true;
	}
	if (!empty($status)) {
		$op_status = false;
		foreach ($status as $val) {
			if (!empty($val)) {
				$op_status = true;
				break;
			}
		}
		if (empty($op_status)) {
			$status = array();
		}
	}
	//可以执行的操作
	$order['operate'] = $status;
	return $order;
}

function clerk_permission_storex($type, $storeid = '') {
	global $_W;
	$condition = array(
		'from_user' => $_W['openid']
	);
	if (!empty($storeid)) {
		$condition['storeid'] = $storeid;
	}
	$clerks = pdo_getall('storex_clerk', $condition, array(), 'storeid');
	$stores = array();
	if (!empty($clerks) && is_array($clerks)) {
		foreach ($clerks as $k => $v) {
			$permission = clerk_permission($k, $v['userid']);
			if (is_array($permission) && in_array('wn_storex_permission_' . $type, $permission)) {
				$stores[] = $k;
			}
		}
	}
	$manage_storex_lists = pdo_getall('storex_bases', array('weid' => intval($_W['uniacid']), 'id' => $stores), array('id', 'title', 'store_type', 'template'), 'id');
	return $manage_storex_lists;
}