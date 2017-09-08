<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('goods_info', 'info', 'order', 'goods_comments', 'package_info', 'spec_info');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'error';

check_params();
mload()->model('activity');
mload()->model('card');
mload()->model('clerk');
mload()->model('order');
$uid = mc_openid2uid($_W['openid']);
$store_id = intval($_GPC['id']);
$goodsid = intval($_GPC['goodsid']);
$store_info = get_store_info($store_id);

//获取某个商品的详细信息
if ($op == 'goods_info') {
	$condition = array('weid' => intval($_W['uniacid']), 'id' => $goodsid, 'status' => 1, 'store_base_id' => $store_id);
	$table = gettablebytype($store_info['store_type']);
	$goods_info = pdo_get($table, $condition);
	if (empty($goods_info)) {
		wmessage(error(-1, '商品不存在'), '', 'ajax');
	} else {
		if ($goods_info['status'] == 0) {
			wmessage(error(-1, '店铺已隐藏'), '', 'ajax');
		}
	}
	$goods_info['store_type'] = $store_info['store_type'];
	$goods_info['thumbs'] = iunserializer($goods_info['thumbs']);
	if (!empty($goods_info['thumb'])) {
		$goods_info['thumb'] = tomedia($goods_info['thumb']);
	}
	if (!empty($goods_info['thumbs'])) {
		$goods_info['thumbs'] = format_url($goods_info['thumbs']);
	}
	if (!empty($goods_info['device'])) {
		$goods_info['device'] = htmlspecialchars_decode($goods_info['device']);
	}
	if ($store_info['store_type'] == 1) {
		if ($goods_info['is_house'] == 1) {
			$goods_info = get_room_params($goods_info);
			$days = ceil((strtotime($_GPC['etime']) - strtotime($_GPC['btime'])) / 86400);
			$dates = get_dates($_GPC['btime'], $days);
			$search_data = array(
				'btime' => $_GPC['btime'],
				'etime' => $_GPC['etime'],
				'nums' => $_GPC['nums'],
			);
			$goods_info = calcul_roon_sumprice($dates, $search_data, $goods_info);
		}
	} else {
		$tags = store_goods_tags($store_info['id']);
		$goods_info['tag'] = get_goods_tag($tags, $goods_info['tag']);
	}
	if (!empty($goods_info['express_set'])) {
		$goods_info['express_set'] = iunserializer($goods_info['express_set']);
	}
	$agent_info = pdo_get('storex_agent_apply', array('uniacid' => $_W['uniacid'], 'storeid' => $store_id, 'uid' => $uid, 'status' => 2), array('id', 'level'));
	$agent_str = '';
	if (!empty($agent_info)) {
		$agent_str = '&from=' . md5('wn_storex_52111').$agent_info['id'];
	}
	$agent_ratio = iunserializer($goods_info['agent_ratio']);
	if (empty($agent_ratio['level'])) {
		$default_level = pdo_get('storex_agent_level', array('storeid' => $store_id, 'isdefault' => 1));
		if (!empty($default_level)) {
			$agent_info['level'] = $default_level['id'];
			pdo_update('storex_agent_apply', array('level' => $default_level['id']), array('id' => $agent_info['id']));
		}
	}
	$ratio = $agent_ratio[$agent_info['level']];
	if (!empty($ratio)) {
		$goods_info['agent']['is_agent'] = 1;
		$goods_info['agent']['ratio'] = $ratio;
	} else {
		$goods_info['agent']['is_agent'] = 2;
	}
	load()->model('mc');
	$card_credit = card_return_credit_info($uid);
	$goods_info['give_score'] = 0;
	if (!empty($card_credit['grant_rate'])) {
		$goods_info['give_score'] = intval($goods_info['cprice'] * $card_credit['grant_rate']);
	}
	$share_data = array(
		'title' => $goods_info['title'],
		'desc' => $goods_info['title'] . '--' . $store_info['title'],
		'link' => murl('entry', array('do' => 'display', 'id' => $store_id, 'm' => 'wn_storex', 'type' => 'goods_info', 'goodsid' => $goodsid), true, true) . $agent_str,
		'imgUrl' => tomedia($goods_info['thumb'])
	);
	$goods_info['defined'] = get_goods_defined($store_id, $goodsid, $this->inMobile);
	$single_comment = array();
	$single_comment = pdo_get('storex_comment', array('uniacid' => $_W['uniacid'], 'hotelid' => $store_id, 'goodsid' => $goodsid, 'comment <>' => ''), array('createtime', 'comment', 'nickname', 'thumb'));
	if (!empty($single_comment)) {
		$single_comment['thumb'] = tomedia($single_comment['thumb']);
		$single_comment['createtime'] = date('Y-m-d', $single_comment['createtime']);
	}
	$goods_info['comment'] = $single_comment;
	//套餐信息
	if ($store_info['store_type'] != 1) {
		$goods_package = pdo_getall('storex_goods_package', array('uniacid' => $_W['uniacid'], 'storeid' => $store_id, 'goodsid' => $goodsid), array('packageid'));
		if (!empty($goods_package) && is_array($goods_package)) {
			foreach ($goods_package as $value) {
				$packageids[] = $value['packageid'];
			}
		}
		if (!empty($packageids) && is_array($packageids)) {
			$sales_packages = pdo_getall('storex_sales_package', array('id' => $packageids, 'status' => 1), array('id', 'title', 'sub_title', 'price', 'thumb', 'express', 'goodsids'));
			$package_list = pdo_getall('storex_goods_package', array('packageid' => $packageids), array('goodsid', 'packageid'));
		}
		if (!empty($package_list) && is_array($package_list)) {
			foreach ($package_list as $value) {
				$goodsids[] = $value['goodsid'];
			}
			$goodsids = array_unique($goodsids);
		}
		$goods_list = pdo_getall('storex_goods', array('id' => $goodsids), array('id', 'title', 'oprice', 'thumb'), 'id');
		if (!empty($goods_list) && is_array($goods_list)) {
			foreach ($goods_list as &$goods) {
				$goods['thumb'] = tomedia($goods['thumb']);
			}
			unset($goods);
		}
		if (!empty($sales_packages) && is_array($sales_packages)) {
			foreach ($sales_packages as $key => &$package) {
				$package['goodsids'] = iunserializer($package['goodsids']);	
				if (!empty($package['goodsids'])) {
					foreach ($package['goodsids'] as $key => $goodsid) {
						if (!empty($goods_list[$goodsid])) {
							$package['goodsids'][$key] = $goods_list[$goodsid];
						}
					}
				}
			}
			unset($package);
		}
		$goods_info['is_package'] = !empty($sales_packages) ? 1 : 2;
		$goods_info['packages'] = $sales_packages;
	}
	//规格列表
	if ($store_info['store_type'] != 1) {
		$spec_list = pdo_getall('storex_spec_goods', array('goodsid' => $goodsid, 'storeid' => $store_id, 'uniacid' => $_W['uniacid']));
		$goods_info['has_spec'] = 2;
		if (!empty($spec_list)) {
			$goods_info['has_spec'] = 1;
			$spec_goods_list = array();
			if (!empty($spec_list) && is_array($spec_list)) {
				foreach ($spec_list as $k => $val) {
					$goods_info['sp_name'] = iunserializer($val['sp_name']);
					$goods_info['sp_val'] = iunserializer($val['sp_val']);
					$goods_val = iunserializer($val['goods_val']);
					if (!empty($goods_val) && is_array($goods_val)) {
						foreach ($goods_val as $key => $value) {
							$goods_val_keys = array_keys($goods_val);
							$goods_val_keys = implode('|', $goods_val_keys);
							$spec_goods_list[$goods_val_keys] = $val['id'];
						}
					}
				}
			}
			$goods_info['spec_list'] = $spec_goods_list;
		}
	}
	wmessage(error(0, $goods_info), $share_data, 'ajax');
}

if ($op == 'spec_info') {
	$specid = intval($_GPC['specid']);
	$spec_info = pdo_get('storex_goods', array('weid' => $_W['uniacid'], 'store_base_id' => $store_id, 'id' => $goodsid), array('oprice', 'cprice', 'stock'));
	$spec_goods = pdo_get('storex_spec_goods', array('uniacid' => $_W['uniacid'], 'id' => $specid));
	if (!empty($spec_goods)) {
		$spec_info = array(
			'oprice' => $spec_goods['oprice'],
			'cprice' => $spec_goods['cprice'],
			'stock' => $spec_goods['stock'],
		);
	}
	wmessage(error(0, $spec_info), '', 'ajax');
}

if ($op == 'package_info') {
	if ($store_info['store_type'] == 1) {
		wmessage(error(-1, '参数错误'), '', 'ajax');
	}
	$package_info = pdo_get('storex_sales_package', array('id' => $_GPC['pid'], 'storeid' => $store_id));
	$goods_id = pdo_getall('storex_goods_package', array('packageid' => $_GPC['pid'], 'storeid' => $store_id), array('goodsid'), 'goodsid');
	if (!empty($goods_id) && is_array($goods_id)) {
		$goodsids = array_keys($goods_id);
		$goods_list = pdo_getall('storex_goods', array('id' => $goodsids, 'store_type' => 0), array('title', 'sub_title', 'thumb', 'oprice', 'cprice', 'id'));
	}
	if (!empty($goods_list) && is_array($goods_list)) {
		foreach ($goods_list as &$value) {
			$value['thumb'] = tomedia($value['thumb']);
		}
		unset($value);
	}
	$package = array();
	if (!empty($package_info)) {
		$package_info['goodsids'] = $goods_list;
		$package = $package_info;
	}
	wmessage(error(0, $package), '', 'ajax');
}

if ($op == 'goods_comments') {
	$comment_list = array();
	$comment_list = pdo_getall('storex_comment', array('uniacid' => $_W['uniacid'], 'hotelid' => $store_id, 'goodsid' => $goodsid), array(), 'id');
	if (!empty($comment_list) && is_array($comment_list)) {
		foreach ($comment_list as $key => &$value) {
			$value['thumb'] = tomedia($value['thumb']);
			$value['createtime'] = date('Y-m-d', $value['createtime']);
			if ($value['type'] == 3) {
				$comment_list[$value['cid']]['reply'][] = $value;
				unset($comment_list[$key]);
			}
		}
	}
	$comment_list = array_values($comment_list);
	wmessage(error(0, $comment_list), '', 'ajax');
}

//进入预定页面的信息
if ($op == 'info') {
	$goods_type = !empty($_GPC['gtype']) ? intval($_GPC['gtype']) : 1;
	$store_info = get_store_info($store_id);
	$member = array();
	$member['from_user'] = $_W['openid'];
	$member['weid'] = intval($_W['uniacid']);
	$record = hotel_member_single($member);
	$info = array();
	if (!empty($record)) {
		$info['name'] = $record['realname'];
		$info['mobile'] = $record['mobile'];
		$info['contact_name'] = $record['realname'];
	}
	$condition = array('weid' => intval($_W['uniacid']), 'id' => $goodsid, 'status' => 1, 'store_base_id' => $store_id);
	$table = gettablebytype($store_info['store_type']);
	if ($store_info['store_type'] == 1) {
		$goods_info = pdo_get($table, $condition);
		if (isset($goods_info['express_set'])) {
			unset($goods_info['express_set']);
		}
		if ($goods_info['is_house'] == 1) {
			$days = ceil((strtotime($_GPC['etime']) - strtotime($_GPC['btime']))/86400);
			$dates = get_dates($_GPC['btime'], $days);
			$search_data = array(
				'btime' => $_GPC['btime'],
				'etime' => $_GPC['etime'],
				'nums' => $_GPC['nums'],
			);
			$goods_info = calcul_roon_sumprice($dates, $search_data, $goods_info);
		}
	} else {
		if ($goods_type == 2) {
			$goods_info = format_package_goods($store_id, $goodsid);
		} else {
			$goods_info = pdo_get($table, $condition);
		}
	}
	if (!empty($goods_info['express_set'])) {
		$goods_info['express_set'] = iunserializer($goods_info['express_set']);
	}
	$spec_id = intval($_GPC['spec_id']);
	if (!empty($spec_id)) {
		$spec_goods = pdo_get('storex_spec_goods', array('id' => $spec_id, 'goodsid' => $goodsid));
		if (!empty($spec_goods['goods_val'])) {
			$spec_goods['goods_val'] = iunserializer($spec_goods['goods_val']);
			$goods_info['title'] .= ' ' . implode(' ', $spec_goods['goods_val']);
		}
		if (!empty($spec_goods)) {
			$goods_info['cprice'] = $spec_goods['cprice'];
			$goods_info['oprice'] = $spec_goods['oprice'];
			$goods_info['stock'] = $spec_goods['stock'];
		}
	}
	if (!empty($goods_info['thumb'])) {
		$goods_info['thumb'] = tomedia($goods_info['thumb']);
	}
	if (!empty($goods_info['thumbs'])) {
		$goods_info['thumbs'] = iunserializer($goods_info['thumbs']);
		$goods_info['thumbs'] = format_url($goods_info['thumbs']);
	}
	$goods_info['defined'] = get_goods_defined($store_id, $goodsid);
	$address = pdo_getall('mc_member_address', array('uid' => $uid, 'uniacid' => intval($_W['uniacid'])));
	$infos['info'] = $info;
	$infos['goods_info'] = $goods_info;
	$infos['address'] = $address;
	$infos['market'] = array();
	if ($store_info['market_status'] == 1) {
		$infos['market'] = get_store_market($store_id);
	} else {
		$paycenter_couponlist = activity_paycenter_get_coupon();
		$infos['coupon_list'] = $paycenter_couponlist;
		$card_activity_info = card_return_credit_info();
		$user_group = card_group_id($uid);
		if (!empty($card_activity_info) && !empty($user_group) && !empty($card_activity_info['discounts'][$user_group['groupid']])) {
			$infos['card_disounts_info'] = array();
			if ($card_activity_info['discount_type'] == 1) {
				$discount_info['discount_type'] = 1;
				$discount_info['condition'] = $card_activity_info['discounts'][$user_group['groupid']]['condition_1'];
				$discount_info['discount'] = $card_activity_info['discounts'][$user_group['groupid']]['discount_1'];
			} elseif ($card_activity_info['discount_type'] == 2) {
				$discount_info['discount_type'] = 2;
				$discount_info['condition'] = $card_activity_info['discounts'][$user_group['groupid']]['condition_2'];
				$discount_info['discount'] = $card_activity_info['discounts'][$user_group['groupid']]['discount_2'];
			}
			$infos['card_disounts_info'] = $discount_info;
		}
	}
	wmessage(error(0, $infos), '', 'ajax');
}

//预定提交预定信息
if ($op == 'order') {
	$orderid = $_GPC['orderid'];
	$goods_type = !empty($_GPC['gtype']) ? intval($_GPC['gtype']) : 1;
	$order_info = array(
		'weid' => intval($_W['uniacid']),
		'openid' => $_W['openid'],
		'time' => TIMESTAMP,
	);
	if (!empty($orderid)) {
		$order = pdo_get('storex_order', array('id' => $orderid, 'paystatus' => 1, 'goods_status' => 5, 'btime <=' => TIMESTAMP, 'etime >' => TIMESTAMP));
		if (empty($order)) {
			wmessage(error(-1, '该订单不能续订'), '', 'ajax');
		}
		if (empty($order['roomitemid'])) {
			wmessage(error(-1, '管理员未分配房间，请联系管理员'), '', 'ajax');
		}
		$order['roomitemid'] = explode(',', $order['roomitemid']);
		if (empty($_GPC['roomid'])) {
			wmessage(error(-1, '请选择要续订房间'), '', 'ajax');
		}
		if (!in_array(intval($_GPC['roomid']), $order['roomitemid'])) {
			wmessage(error(-1, '该订单未分配该房间'), '', 'ajax');
		}
		if (!pdo_get('storex_room_items', array('id' => intval($_GPC['roomid'])))) {
			wmessage(error(-1, '该房间不存在'), '', 'ajax');
		}
		$store_id = $order['hotelid'];
		$goodsid = $order['roomid'];
		$order_info['hotelid'] = $store_id;
		$order_info['contact_name'] = trim($order['contact_name']);
		$order_info['roomid'] = $goodsid;
		$order_info['mobile'] = $order['mobile'];
		$order_info['remark'] = $order['remark'];
		$order_info['nums'] = intval($_GPC['order']['nums']);
		$order_info['roomitemid'] = intval($_GPC['roomid']);
	} else {
		if (!empty($_GPC['from'])) {
			$id = intval(substr($_GPC['from'], strlen(md5('wn_storex_52111'))));
			if (!empty($id)) {
				$order_info['agentid'] = $id;
			}
		}
		$order_info['hotelid'] = $store_id;
		$order_info['contact_name'] = trim($_GPC['order']['contact_name']);
		$order_info['roomid'] = $goodsid;
		$order_info['mobile'] = trim($_GPC['order']['mobile']);
		$order_info['remark'] = trim($_GPC['order']['remark']);
		$order_info['nums'] = intval($_GPC['order']['nums']);
	}
	$store_info = get_store_info($store_id);
	if ($store_info['market_status'] != 1) {
		$selected_coupon = $_GPC['order']['coupon'];
		if ($selected_coupon['type'] == 3) {
			$coupon_info = activity_get_coupon_info($selected_coupon['couponid']);
			if (empty($coupon_info)) {
				wmessage(error(-1, '卡券信息有误'), '', 'ajax');
			}
		}
	}
	if ($order_info['nums'] <= 0) {
		wmessage(error(-1, '数量不能是零'), '', 'ajax');
	}
	$order_info['action'] = 2;
	$paysetting = uni_setting(intval($_W['uniacid']), array('payment', 'creditbehaviors'));
	$_W['account'] = array_merge($_W['account'], $paysetting);
	$condition = array('weid' => intval($_W['uniacid']), 'id' => $goodsid, 'status' => 1, 'store_base_id' => $store_id);
	$table = gettablebytype($store_info['store_type']);
	$spec_goods = array();
	$spec_id = 0;
	$spec_info = '';
	if ($goods_type == 2) {
		$goods_info = format_package_goods($store_id, $goodsid);
	} else {
		$goods_info = pdo_get($table, $condition);
		$spec_id = intval($_GPC['spec_id']);
		if (!empty($spec_id)) {
			$spec_goods = pdo_get('storex_spec_goods', array('uniacid' => $_W['uniacid'], 'id' => $spec_id, 'goodsid' => $goodsid));
			if (empty($spec_goods)) {
				wmessage(error(-1, '所选规格不存在'), '', 'ajax');
			}
			$spec_info = array(
				'sp_name' => iunserializer($spec_goods['sp_name']),
				'sp_val' => iunserializer($spec_goods['sp_val']),
				'goods_val' => iunserializer($spec_goods['goods_val']),
			);
			$goods_info['oprice'] = $spec_goods['oprice'];
			$goods_info['cprice'] = $spec_goods['cprice'];
			$goods_info['stock'] = $spec_goods['stock'];
		}
	}
	if ($store_info['store_type'] != 1 || ($store_info['store_type'] == 1 && $goods_info['is_house'] == 1)) {
		if (empty($order_info['mobile'])) {
			wmessage(error(-1, '手机号码不能为空'), '', 'ajax');
		}
		if (!preg_match(REGULAR_MOBILE, $order_info['mobile'])) {
			wmessage(error(-1, '手机号码格式不正确'), '', 'ajax');
		}
		if (empty($order_info['contact_name'])) {
			wmessage(error(-1, '联系人不能为空!'), '', 'ajax');
		}
	}
	if (empty($goods_info)) {
		wmessage(error(-1, '商品未找到, 请联系管理员!'), '', 'ajax');
	}
	if ($goods_info['can_buy'] != 1) {
		wmessage(error(-1, '该商品不能购买'), '', 'ajax');
	}
	$insert = array(
		'ordersn' => date('md') . sprintf("%04d", $_W['fans']['fanid']) . random(4, 1),
		'style' => $goods_info['title'],
		'oprice' => $goods_info['oprice'],
		'cprice' => $goods_info['cprice'],
		'spec_id' => $spec_id,
		'spec_info' => iserializer($spec_info),
	);
	if ($goods_info['cprice'] == 0) {
		wmessage(error(-1, '商品价格不能是0，请联系管理员!'), '', 'ajax');
	}
	$today_start = strtotime(date('Y-m-d'), TIMESTAMP);
	$today_end = $today_start + 86399;
	$where = 'hotelid = :hotelid AND roomid = :roomid AND openid = :openid AND paystatus = 0 AND time >= :today_start AND time < :today_end AND status != -1 AND status != 2 ';
	$param = array(
		':hotelid' => $order_info['hotelid'],
		':roomid' => $order_info['roomid'],
		':openid' => $_W['openid'],
		':today_start' => $today_start,
		':today_end' => $today_end,
	);
	if (!empty($spec_id)) {
		$where .= ' AND spec_id = :spec_id';
		$param[':spec_id'] = $spec_id;
	}
	$order_exist = pdo_fetch("SELECT id FROM " . tablename('storex_order') . "WHERE " . $where, $param);
	if (!empty($order_exist)) {
		wmessage(error(-1, "您有未支付该类订单,不要重复下单"), '', 'ajax');
	}
	$setInfo = pdo_get('storex_set', array('weid' => $_W['uniacid']), array('template', 'confirm_templateid', 'smscode'));
	if ($store_info['store_type'] == STORE_TYPE_HOTEL) {
		if ($goods_info['is_house'] == 1) {
			$order_info['btime'] = strtotime($_GPC['order']['btime']);
			$order_info['etime'] = strtotime($_GPC['order']['etime']);
			if (!empty($_GPC['order']['day'])) {
				$order_info['day'] = intval($_GPC['order']['day']);
			} else {
				$order_info['day'] = ceil(($order_info['etime'] - $order_info['btime'])/86400);
			}
			if ($order_info['day'] <= 0) {
				wmessage(error(-1, '天数不能是零'), '', 'ajax');
			}
			if ($order_info['btime'] < strtotime('today')) {
				wmessage(error(-1, '预定的开始日期不能小于当日的日期'), '', 'ajax');
			}
			if (8 < $order_info['nums']) {
				wmessage(error(-1, '订单购买数量超过最大限制'), '', 'ajax');
			}
			if (!empty($orderid)) {
				$status = check_room_assign($order_info, array($order_info['roomitemid']), true);
				if (empty($status)) {
					wmessage(error(-1, '续订该房间已被分配了，请联系管理员'), '', 'ajax');
				}
			}
			$bdate = date('Y-m-d', $order_info['btime']);
			$days = $order_info['day'];
			$edate = date('Y-m-d', $order_info['etime']);
			$dates = get_dates($bdate, $days);
			
			$search_data = array(
				'btime' => $bdate,
				'etime' => $edate,
				'nums' => $order_info['nums'],
			);
			check_room_nums($dates, $search_data, $goods_info);
			$goods_info = calcul_roon_sumprice($dates, $search_data, $goods_info);
			$insert['sum_price'] = $goods_info['sum_price'];
			if ($setInfo['smscode'] == 1) {
				$code = pdo_get('storex_code', array('mobile' => $mobile, 'weid' => intval($_W['uniacid'])), array('code'));
				if ($mobilecode != $code['code']) {
					wmessage(error(-1, '您的验证码错误，请重新输入!'), '', 'ajax');
				}
			}
			$insert = array_merge($order_info, $insert);
			pdo_query('UPDATE ' . tablename('storex_order') . " SET status = -1, newuser = 0 WHERE time < :time AND weid = :weid AND paystatus = '0' AND status <> '1' AND status <> '3'", array(':time' => time() - 86400, ':weid' => $_W['uniacid']));
		} else {
			$insert = general_goods_order($order_info, $goods_info, $insert);
		}
	} else {
		if ($goods_type != 2) {
			$stock = check_goods_stock($goodsid, $order_info['nums'], $spec_goods);
			if (is_error($stock)) {
				wmessage($stock, '', 'ajax');
			}
		}
		$insert = general_goods_order($order_info, $goods_info, $insert);
	}
	//根据优惠方式计算总价
	$market_types = array();
	if ($store_info['market_status'] == 1) {
		$markets = get_store_market($store_id);
		if (!empty($markets) && is_array($markets)) {
			foreach ($markets as $info) {
				if ($info['type'] == 'new' && $info['items'] > 0 && $insert['sum_price'] > $info['items']) {
					$market_types[] = 'new';
					$insert['newuser'] = 1;
					$insert['sum_price'] -= $info['items'];
					continue;
				}
				if ($info['items']['condition'] > 0 && $insert['sum_price'] >= $info['items']['condition'] && $info['items']['back'] > 0) {
					if ($info['type'] == 'cut') {
						$market_types[] = $info['type'];
						$insert['sum_price'] -= $info['items']['back'];
					}
					if ($info['type'] == 'gift') {
						$market_types[] = $info['type'];
					}
					if (!empty($insert['mode_distribute']) && $insert['mode_distribute'] == 1 && $info['type'] == 'pickup') {
						$market_types[] = $info['type'];
						$insert['sum_price'] *= $info['items']['back'] * 0.1;
					}
				}
			}
			if (!empty($market_types)) {
				$insert['market_types'] = iserializer($market_types);
			}
		}
	} else {
		if ($selected_coupon['type'] == 3) {
			$extra_info = $coupon_info['extra'];
			if ($coupon_info['type'] == COUPON_TYPE_DISCOUNT) {
				$insert['sum_price'] = $insert['sum_price'] * $extra_info['discount'] / 100;
			} elseif ($coupon_info['type'] == COUPON_TYPE_CASH) {
				$least_cost = $extra_info['least_cost'] * 0.01;
				$reduce_cost = $extra_info['reduce_cost'] * 0.01;
				if ($insert['sum_price'] >= $least_cost) {
					$insert['sum_price'] = $insert['sum_price'] - $reduce_cost;
				}
			}
			$insert['coupon'] = $selected_coupon['recid'];
		} elseif ($selected_coupon['type'] == 2) {
			$insert['sum_price'] = card_discount_price($uid, $insert['sum_price']);
		}
	}
	
	$insert['static_price'] = $insert['sum_price'];
	
	if ($store_info['store_type'] != STORE_TYPE_HOTEL) {
		$insert = calculate_express($goods_info, $insert);
		if ($goods_type == 2) {
			$goods_info['express_set'] = iunserializer($goods_info['express_set']);
			$insert['sum_price'] += $goods_info['express_set']['express'];
		}
	}
	
	$insert['sum_price'] = sprintf ('%1.2f', $insert['sum_price']);
	$post_total = trim($_GPC['order']['total']);
	if ($post_total != $insert['sum_price']) {
		wmessage(error(-1, '价格错误' . $insert['sum_price']), '', 'ajax');
	}
	if ($insert['sum_price'] <= 0) {
		wmessage(error(-1, '总价为零，请联系管理员'), '', 'ajax');
	}
	if ($goods_type == 2) {
		$insert['is_package'] = 2;
	}
	pdo_insert('storex_order', $insert);
	$order_id = pdo_insertid();
	if (!empty($order_id)) {
		$logs = array(
			'table' => 'storex_order_logs',
			'time' => TIMESTAMP,
			'after_change' => 0,
			'type' => 'status',
			'uid' => $uid,
			'clerk_type' => 1,
			'orderid' => $order_id,
			'remark' => '下单成功',
		);
		write_log($logs);
		if ($store_info['store_type'] != STORE_TYPE_HOTEL) {
			stock_control($insert, 'order');
		}
		if ($store_info['market_status'] != 1) {
			if ($selected_coupon['type'] == 3) {
				$result = activity_coupon_consume($selected_coupon['couponid'], $selected_coupon['recid'], $store_info['id']);
				if (is_error($result)) {
					wmessage($result, '', 'ajax');
				}
			}
		}
	}
	
	if (check_plugin_isopen('wn_storex_plugin_sms')) {
		$clerks = pdo_getall('storex_clerk', array('weid' => $_W['uniacid'], 'status' => 1, 'storeid' => $insert['hotelid']), array('id', 'userid', 'mobile'));
		if (!empty($clerks)) {
			mload()->model('sms');
			$content = array(
				'store' => $store_info['title'],
				'price' => $insert['sum_price'],
			);
			foreach ($clerks as $k => $val) {
				if (!preg_match(REGULAR_MOBILE, $val['mobile'])) {
					unset($clerks[$k]);
					continue;
				}
				$permission = clerk_permission($insert['hotelid'], $val['userid']);
				if (!in_array('wn_storex_permission_order', $permission)) {
					unset($clerks[$k]);
					continue;
				}
				sms_send($val['mobile'], $content, 'clerk');
			}
		}
	}
	//如果有接受订单的邮件
	$emails = array();
	if (!empty($store_info['emails'])) {
		$emails = $store_info['emails'];
	}
	if (!empty($store_info['mail'])) {
		$emails[] = $store_info['mail'];
	}
	$emails = array_unique($emails);
	if (!empty($emails) && is_array($emails) && false) {
		foreach ($emails as $mail) {
			$subject = "微信公共帐号 [" . $_W['account']['name'] . "] 万能小店订单提醒.";
			$body = "您后台有一个预定订单: <br/><br/>";
			$body .= "预定店铺: " . $store_info['title'] . "<br/>";
			$body .= "预定商品: " . $goods_info['title'] . "<br/>";
			$body .= "预定数量: " . $insert['nums'] . "<br/>";
			$body .= "预定价格: " . $insert['sum_price'] . "<br/>";
			$body .= "预定人: " . $insert['contact_name'] . "<br/>";
			$body .= "预定电话: " . $insert['mobile'] . "<br/>";
			if ($store_info['store_type'] == 1 && $goods_info['is_house'] == 1) {
				$body .= "到店时间: " . $bdate . "<br/>";
				$body .= "离店时间: " . $edate . "<br/><br/>";
			}
			load()->func('communication');
			ihttp_email($mail, $subject, $body);
		}
	}
	if ($store_info['store_type'] == 1 && $goods_info['is_house'] == 1) {
		//订单下单成功减库存
		$starttime = $insert['btime'];
		for ($i = 0; $i < $insert['day']; $i++) {
			$day = pdo_get('storex_room_price', array('weid' => intval($_W['uniacid']), 'roomid' => $insert['roomid'], 'roomdate' => $starttime));
			if ($day && $day['num'] != -1) {
				pdo_update('storex_room_price', array('num' => $day['num'] - $insert['nums']), array('id' => $day['id']));
			}
			$starttime += 86400;
		}
	}
	
	if (!empty($store_info['openids']) && is_array($store_info['openids'])) {
		foreach ($store_info['openids'] as $openid) {
			if (!empty($orderid)) {
				$info = '店铺有续订的订单,为保证用户体验度，请及时处理!';
			} else {
				$info = '店铺有新的订单,为保证用户体验度，请及时处理!';
			}
			$status = send_custom_notice('text', array('content' => urlencode($info)), $openid);
		}
	}
	
	$member = array(
		'weid' => intval($_W['uniacid']),
		'from_user' => $_W['openid'],
		'userid' => $uid,
		'realname' => $insert['contact_name'],
		'mobile' => $insert['mobile'],
	);
	if (!hotel_member_single($member)) {
		insert_member($member);
	}
	
	//检查结果
	if (!empty($order_id)) {
		mload()->model('order');
		$tpl_params = array(
			'ordersn' => $insert['ordersn'],
			'contact_name' => $insert['contact_name'],
			'phone' => $store_info['phone'],
			'openid' => $_W['openid'],
			'store' => $store_info['title'],
			'room' => $goods_info['title'],
		);
		$tpl_params['tpl_status'] = false;
		if (!empty($setInfo['template'])) {
			$tpl_params['tpl_status'] = true;
			$tpl_params['confirm_templateid'] = $setInfo['confirm_templateid'];
		}
		order_confirm_notice($tpl_params);
		wmessage(error(0, $order_id), '', 'ajax');
	} else {
		wmessage(error(-1, '下单失败'), '', 'ajax');
	}
}