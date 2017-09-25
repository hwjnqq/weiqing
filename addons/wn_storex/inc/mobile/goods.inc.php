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
$condition = array('status' => 1,'storeid' => $store_id, 'uniacid' => $_W['uniacid'], 'goodsid' => $goodsid, 'starttime <=' => TIMESTAMP, 'endtime >' => TIMESTAMP);
if (!empty($_GPC['specid'])) {
	$condition['specid'] = intval($_GPC['specid']);
}
$activity = pdo_get('storex_goods_activity', $condition, array(), '', 'starttime ASC');

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
		'imgUrl' => tomedia($goods_info['thumb']),
		'agent_str' => md5('wn_storex_52111').$agent_info['id'],
	);
	$share_data = get_share_data('goods', array('storeid' => $store_id, 'goodsid' => $goodsid), $share_data);
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
					foreach ($package['goodsids'] as $key => $val) {
						if (!empty($goods_list[$val])) {
							$package['goodsids'][$key] = $goods_list[$val];
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
	$goods_info['activity_exist'] = 2;
	$goods_info['activity'] = $activity;
	if (!empty($activity)) {
		$goods_info['activity_exist'] = 1;
		$goods_info['activity']['oprice'] = $goods_info['oprice'];
		$goods_info['activity']['cprice'] = $activity['price'];
		$goods_info['activity']['endtime'] = date('Y-m-d H:i:s', $goods_info['activity']['endtime']);
		
	}
	wmessage(error(0, $goods_info), $share_data, 'ajax');
}

if ($op == 'spec_info') {
	$specid = intval($_GPC['specid']);
	$spec_info = pdo_get('storex_goods', array('recycle' => 2, 'weid' => $_W['uniacid'], 'store_base_id' => $store_id, 'id' => $goodsid), array('oprice', 'cprice', 'stock'));
	$spec_goods = pdo_get('storex_spec_goods', array('uniacid' => $_W['uniacid'], 'id' => $specid));
	if (!empty($spec_goods)) {
		$spec_info = array(
			'oprice' => $spec_goods['oprice'],
			'cprice' => $spec_goods['cprice'],
			'stock' => $spec_goods['stock'],
			'thumb' => tomedia($spec_goods['thumb']),
		);
	}
	$spec_info['activity_exist'] = 2;
	$spec_info['activity'] = array();
	if (!empty($activity) && $specid == $activity['specid']) {
		$spec_info['activity_exist'] = 1;
		$spec_info['activity'] = $activity;
		$spec_info['activity']['oprice'] = $spec_info['oprice'];
		$spec_info['oprice'] = $spec_info['oprice'];
		$spec_info['activity']['cprice'] = $activity['price'];
		$spec_info['cprice'] = $activity['price'];
		$spec_info['activity']['endtime'] = date('Y-m-d H:i:s', $spec_info['activity']['endtime']);
	
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
	$order_goods = array();
	if ($store_info['store_type'] == STORE_TYPE_HOTEL) {
		$condition = array('weid' => intval($_W['uniacid']), 'status' => 1, 'store_base_id' => $store_id);
		$table = gettablebytype($store_info['store_type']);
		$condition['id'] = $goodsid;
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
		$order_goods = $goods_info;
	} else {
		$goods = order_goodsids();
		if (!empty($goods) && is_array($goods)) {
			$order_goods = get_order_goods($store_info, $goods);
		} else {
			wmessage(error(-1, '商品错误'), '', 'ajax');
		}
		if (count($order_goods) > 1) {
			$infos['express'] = $store_info['express'];
		} else {
			$infos['express'] = $order_goods[0]['express_set']['express'];
		}
	}
	$address = pdo_getall('mc_member_address', array('uid' => $uid, 'uniacid' => intval($_W['uniacid'])));
	$infos['info'] = $info;
	$infos['goods_info'] = $order_goods;
	$infos['address'] = $address;
	$infos['pick_up_mode'] = array();
	if (!empty($store_info['pick_up_mode']) && is_array($store_info['pick_up_mode'])) {
		foreach ($store_info['pick_up_mode'] as $mode => $val) {
			$infos['pick_up_mode'][$mode] = array(
				'value' => $store_info['pick_up_mode'][$mode],
				'type' => $mode,
			);
			if ($mode == 'express') {
				$infos['pick_up_mode'][$mode]['name'] = '配送';
			} elseif ($mode == 'self_lift') {
				$infos['pick_up_mode'][$mode]['name'] = '自提';
			}
		}
	}
	$infos['market'] = array();
	if ($store_info['market_status'] == 1) {
		$infos['market'] = get_store_market($store_id);
	} else {
		$paycenter_couponlist = activity_paycenter_get_coupon();
		$infos['coupon_list'] = $paycenter_couponlist;
		$user_group = card_group_id($uid);
		if (!empty($user_group)) {
			$card_activity_info = card_return_credit_info();
			if (!empty($card_activity_info) && !empty($card_activity_info['discounts'][$user_group['groupid']])) {
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
	}
	$infos['credit_replace'] = get_credit_replace($store_id, $uid);
	if (!empty($infos['credit_replace']['max_replace']) && $infos['goods_info']['cprice'] < $infos['credit_replace']['max_replace']) {
		$infos['credit_replace']['credit_pay'] = 2;
	}
	wmessage(error(0, $infos), '', 'ajax');
}

//预定提交预定信息
if ($op == 'order') {
	$insert = array(
		'weid' => intval($_W['uniacid']),
		'openid' => $_W['openid'],
		'time' => TIMESTAMP,
		'action' => 2,
		'roomid' => $goodsid,
		'hotelid' => $store_id,
	);
	if (intval($_GPC['order']['nums']) <= 0) {
		wmessage(error(-1, '数量不能是零'), '', 'ajax');
	}
	//卡券使用
	$selected_coupon = $_GPC['order']['coupon'];
	if (!empty($selected_coupon) && $selected_coupon['type'] == 3) {
		$coupon_info = activity_get_coupon_info($selected_coupon['couponid']);
		if (empty($coupon_info)) {
			wmessage(error(-1, '卡券信息有误'), '', 'ajax');
		}
		$selected_coupon['coupon_info'] = $coupon_info;
	}
	if ($store_info['store_type'] == STORE_TYPE_HOTEL) {
		goods_hotel_order($insert, $store_info, $uid, $selected_coupon);
	} else {
		goods_common_order($insert, $store_info, $uid, $selected_coupon);
	}
}

function order_goodsids() {
	global $_GPC;
	$goods = array();
	if ($store_info['store_type'] != STORE_TYPE_HOTEL) {
		//goods  商品或规格id|数量|是不是规格,商品或规格id|数量|是不是规格1规格，2普通，3套餐
		$goods = trim($_GPC['goods'], ',');
		if (empty($goods)) {
			wmessage(error(-1, '商品不能是空'), '', 'ajax');
		}
		$goods = explode(',', $goods);
		foreach ($goods as &$g) {
			$g = explode('|', $g);
		}
		unset($g);
	}
	return $goods;
}

//购物车获取商品信息
function get_order_goods($store_info, $goods) {
	global $_W, $_GPC;
	$condition = array('weid' => intval($_W['uniacid']), 'status' => 1, 'store_base_id' => $store_info['id']);
	$table = gettablebytype($store_info['store_type']);
	$order_goods = array();
	foreach ($goods as $g) {
		if (!empty($g[2])) {
			$a_condition = array('status' => 1,'storeid' => $store_info['id'], 'uniacid' => $_W['uniacid'], 'starttime <=' => TIMESTAMP, 'endtime >' => TIMESTAMP);
			if ($g[2] == 3) {//套餐
				$goods_info = format_package_goods($store_info['id'], $g[0]);
				$a_condition['goodsid'] = $g[0];
			} elseif ($g[2] == 2) {//普通
				$a_condition['goodsid'] = $condition['id'] = $g[0];
				$goods_info = pdo_get($table, $condition);
				$goods_info['defined'] = get_goods_defined($store_info['id'], $g[0]);
			} elseif ($g[2] == 1) {//有规格
				$spec_goods = pdo_get('storex_spec_goods', array('id' => $g[0]));
				$a_condition['specid'] = $g[0];
				$a_condition['goodsid'] = $condition['id'] = $spec_goods['goodsid'];
				$goods_info = pdo_get($table, $condition);
				$goods_info['defined'] = get_goods_defined($store_info['id'], $spec_goods['goodsid']);
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
			$goods_info['buynums'] = $g[1];
			//活动
			$activity = pdo_get('storex_goods_activity', $a_condition, array(), '', 'starttime ASC');
			if (!empty($activity)) {
				$goods_info['cprice'] = $activity['price'];
			}
			//运费
			if (!empty($goods_info['express_set'])) {
				$goods_info['express_set'] = iunserializer($goods_info['express_set']);
			}
			if (!empty($goods_info['thumb'])) {
				$goods_info['thumb'] = tomedia($goods_info['thumb']);
			}
			if (!empty($goods_info['thumbs'])) {
				$goods_info['thumbs'] = iunserializer($goods_info['thumbs']);
				$goods_info['thumbs'] = format_url($goods_info['thumbs']);
			}
			$order_goods[] = $goods_info;
		}
	}
	return $order_goods;
}

//酒店订单
function goods_hotel_order($insert, $store_info, $uid, $selected_coupon = array()) {
	global $_GPC, $_W;
	$store_id = $store_info['id'];
	$goodsid = $insert['roomid'];
	$orderid = $_GPC['orderid'];
	if (!empty($orderid)) {
		$insert = check_order_renew($insert, $orderid);
	} else {
		$insert = get_order_info($insert);
	}
	$goods_info = get_goods_info($goodsid, $store_info);
	$error = check_order_info($store_info, $insert, $goods_info);
	if (!empty($error) && is_error($error)) {
		wmessage($error, '', 'ajax');
	}
	$insert['ordersn'] = date('md') . sprintf("%04d", $_W['fans']['fanid']) . random(4, 1);
	$insert['style'] = $goods_info['title'];
	$insert['oprice'] = $goods_info['oprice'];
	$insert['cprice'] = $goods_info['cprice'];
	check_order_repeat($insert);
	if ($goods_info['is_house'] == 1) {
		$insert = room_order($insert, $goods_info, $orderid);
	} else {
		$insert = general_goods_order($insert, $goods_info);
	}
	$insert = calcul_discounts_price($insert, $store_info, $uid, $selected_coupon);
	$insert['static_price'] = $insert['sum_price'];
	$insert['sum_price'] = sprintf ('%1.2f', $insert['sum_price']);
	$post_total = trim($_GPC['order']['total']);
	if ($post_total != $insert['sum_price']) {
		wmessage(error(-1, '价格错误' . $insert['sum_price']), '', 'ajax');
	}
	if ($insert['sum_price'] <= 0) {
		wmessage(error(-1, '总价为零，请联系管理员'), '', 'ajax');
	}
	if (!empty($_GPC['order']['use_credit'])) {
		$insert = calcul_credit_replace($insert, $uid);
	}
	pdo_insert('storex_order', $insert);
	$order_id = pdo_insertid();
	if (!empty($order_id)) {
		check_member_exist($insert, $uid);
		insert_order_success($order_id, $uid);
		if ($store_info['market_status'] != 1) {
			if ($selected_coupon['type'] == 3) {
				$result = activity_coupon_consume($selected_coupon['couponid'], $selected_coupon['recid'], $store_info['id']);
				if (is_error($result)) {
					wmessage($result, '', 'ajax');
				}
			}
		}
		storex_send_sms($insert, $store_info);
		storex_send_email($store_info, $insert, $goods_info);
		if (empty($_GPC['wxapp']) && false) {
			storex_send_notice($store_info, $orderid);
			storex_send_notice_touser($insert, $store_info, $goods_info);
		}
		//房型订单下单成功减库存
		if ($store_info['store_type'] == 1 && $goods_info['is_house'] == 1) {
			$starttime = $insert['btime'];
			for ($i = 0; $i < $insert['day']; $i++) {
				$day = pdo_get('storex_room_price', array('weid' => intval($_W['uniacid']), 'roomid' => $insert['roomid'], 'roomdate' => $starttime));
				if ($day && $day['num'] != -1) {
					pdo_update('storex_room_price', array('num' => $day['num'] - $insert['nums']), array('id' => $day['id']));
				}
				$starttime += 86400;
			}
		}
		wmessage(error(0, $order_id), '', 'ajax');
	} else {
		wmessage(error(-1, '下单失败'), '', 'ajax');
	}
}

//普通商品下单
function goods_common_order($insert, $store_info, $uid, $selected_coupon = array(), $activity = array()) {
	global $_GPC, $_W;
	$goods = order_goodsids();
	$order_goods = get_order_goods($store_info, $goods);
	
// 	$goods_type = !empty($_GPC['gtype']) ? intval($_GPC['gtype']) : 1;
// 	$store_id = $store_info['id'];
// 	$goodsid = $insert['roomid'];
	
	$insert = get_order_info($insert);
	
// 	$spec = get_spec_goods($goodsid);

	$goods_info = get_goods_info($goodsid, $store_info, $spec);
	
	
	$error = check_order_info($store_info, $insert, $goods_info);
	if (!empty($error) && is_error($error)) {
		wmessage($error, '', 'ajax');
	}
	
	$insert['ordersn'] = date('md') . sprintf("%04d", $_W['fans']['fanid']) . random(4, 1);
	$insert['style'] = $goods_info['title'];
	$insert['oprice'] = $goods_info['oprice'];
	$insert['cprice'] = $goods_info['cprice'];
	$insert['spec_id'] = $spec['spec_id'];
	$insert['spec_info'] = !empty($spec['spec_info']) ? iserializer($spec['spec_info']) : '';
	check_order_repeat($insert, $spec);
	if ($goods_type != 2) {
		if (!empty($activity)) {
			$goods_info['cprice'] = $activity['price'];
			if ($activity['type'] == ACTIVITY_SECKILL) {
				if ($insert['nums'] > ($activity['nums'] - $activity['sell_nums'])) {
					wmessage(error(-1, '库存不足'), '', 'ajax');
				}
			}
		} else {
			$stock = check_goods_stock($goodsid, $insert['nums'], $spec['spec_goods']);
			if (is_error($stock)) {
				wmessage($stock, '', 'ajax');
			}
		}
	}
	$insert = general_goods_order($insert, $goods_info);
	$insert = calcul_discounts_price($insert, $store_info, $uid, $selected_coupon);
	$insert['static_price'] = $insert['sum_price'];
	//计算运费
	$insert = calculate_express($goods_info, $insert);
	if ($goods_type == 2) {
		$goods_info['express_set'] = iunserializer($goods_info['express_set']);
		$insert['sum_price'] += $goods_info['express_set']['express'];
		$insert['is_package'] = 2;
	}
	$insert['sum_price'] = sprintf ('%1.2f', $insert['sum_price']);
	$post_total = trim($_GPC['order']['total']);
	if ($post_total != $insert['sum_price']) {
		wmessage(error(-1, '价格错误' . $insert['sum_price']), '', 'ajax');
	}
	if ($insert['sum_price'] <= 0) {
		wmessage(error(-1, '总价为零，请联系管理员'), '', 'ajax');
	}
	if (!empty($_GPC['order']['use_credit'])) {
		$insert = calcul_credit_replace($insert, $uid);
	}
	check_goods_activity($insert, $activity);
	pdo_insert('storex_order', $insert);
	$order_id = pdo_insertid();
	if (!empty($order_id)) {
		check_goods_activity($insert, $activity, $order_id);
		check_member_exist($insert, $uid);
		insert_order_success($order_id, $uid);
		stock_control($insert, 'order');
		if ($store_info['market_status'] != 1) {
			if ($selected_coupon['type'] == 3) {
				$result = activity_coupon_consume($selected_coupon['couponid'], $selected_coupon['recid'], $store_info['id']);
				if (is_error($result)) {
					wmessage($result, '', 'ajax');
				}
			}
		}
		storex_send_sms($insert, $store_info);
		storex_send_email($store_info, $insert, $goods_info);
		if (empty($_GPC['wxapp']) && false) {
			storex_send_notice($store_info, $orderid);
			storex_send_notice_touser($insert, $store_info, $goods_info);
		}
		wmessage(error(0, $order_id), '', 'ajax');
	} else {
		wmessage(error(-1, '下单失败'), '', 'ajax');
	}
}

//插入下单成功日志
function insert_order_success($order_id, $uid) {
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
}

//检查用户
function check_member_exist($insert, $uid) {
	global $_W;
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
}

//给用户发送提醒
function storex_send_notice_touser($insert, $store_info, $goods_info) {
	global $_W;
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
	$setInfo = pdo_get('storex_set', array('weid' => $_W['uniacid']), array('template', 'confirm_templateid'));
	if (!empty($setInfo['template'])) {
		$tpl_params['tpl_status'] = true;
		$tpl_params['confirm_templateid'] = $setInfo['confirm_templateid'];
	}
	order_confirm_notice($tpl_params);
}

//酒店获取续订订单的信息
function check_order_renew($insert, $orderid) {
	global $_W, $_GPC;
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
	$insert['contact_name'] = trim($order['contact_name']);
	$insert['mobile'] = $order['mobile'];
	$insert['remark'] = $order['remark'];
	$insert['nums'] = intval($_GPC['order']['nums']);
	$insert['roomitemid'] = intval($_GPC['roomid']);
	return $insert;
}

//获取普通订单的填写信息
function get_order_info($insert) {
	global $_W, $_GPC;
	if (!empty($_GPC['from'])) {
		$id = intval(substr($_GPC['from'], strlen(md5('wn_storex_52111'))));
		if (!empty($id)) {
			$insert['agentid'] = $id;
		}
	}
	$insert['contact_name'] = trim($_GPC['order']['contact_name']);
	$insert['mobile'] = trim($_GPC['order']['mobile']);
	$insert['remark'] = trim($_GPC['order']['remark']);
	$insert['nums'] = intval($_GPC['order']['nums']);
	return $insert;
}

//获取商品的信息 -- 酒店和普通
function get_goods_info($goodsid, $store_info, $spec = array()) {
	global $_GPC, $_W;
	$goods_type = !empty($_GPC['gtype']) ? intval($_GPC['gtype']) : 1;
	$condition = array('weid' => intval($_W['uniacid']), 'id' => $goodsid, 'status' => 1, 'store_base_id' => $store_info['id']);
	$table = gettablebytype($store_info['store_type']);
	if ($goods_type == 2) {
		$goods_info = format_package_goods($store_info['id'], $goodsid);
	} else {
		$goods_info = pdo_get($table, $condition);
		if (!empty($spec) && !empty($spec['spec_goods'])) {
			$goods_info['oprice'] = $spec['spec_goods']['oprice'];
			$goods_info['cprice'] = $spec['spec_goods']['cprice'];
			$goods_info['stock'] = $spec['spec_goods']['stock'];
		}
	}
	if (empty($goods_info)) {
		wmessage(error(-1, '商品未找到, 请联系管理员!'), '', 'ajax');
	}
	if ($goods_info['can_buy'] != 1) {
		wmessage(error(-1, '该商品不能购买'), '', 'ajax');
	}
	if ($goods_info['cprice'] == 0) {
		wmessage(error(-1, '商品价格不能是0，请联系管理员!'), '', 'ajax');
	}
	return $goods_info;
}

//获取商品的规格
function get_spec_goods($goodsid) {
	global $_GPC, $_W;
	$spec = array();
	$spec['spec_id'] = intval($_GPC['spec_id']);
	$spec['spec_info'] = array();
	if (!empty($spec['spec_id'])) {
		$spec_goods = pdo_get('storex_spec_goods', array('uniacid' => $_W['uniacid'], 'id' => $spec['spec_id'], 'goodsid' => $goodsid));
		if (empty($spec_goods)) {
			wmessage(error(-1, '所选规格不存在'), '', 'ajax');
		}
		$spec['spec_info'] = array(
			'sp_name' => iunserializer($spec_goods['sp_name']),
			'sp_val' => iunserializer($spec_goods['sp_val']),
			'goods_val' => iunserializer($spec_goods['goods_val']),
		);
		$spec['spec_goods'] = $spec_goods;
	}
	return $spec;
}

//检查订单填写信息
function check_order_info($store_info, $insert, $goods_info) {
	if ($store_info['store_type'] != STORE_TYPE_HOTEL || ($store_info['store_type'] == STORE_TYPE_HOTEL && $goods_info['is_house'] == 1)) {
		if (empty($insert['mobile'])) {
			return error(-1, '手机号码不能为空');
		}
		if (!preg_match(REGULAR_MOBILE, $insert['mobile'])) {
			return error(-1, '手机号码格式不正确');
		}
		if (empty($insert['contact_name'])) {
			return error(-1, '联系人不能为空!');
		}
	}
}

//检查订单是否重复
function check_order_repeat($insert, $spec = array()) {
	global $_W;
	$today_start = strtotime(date('Y-m-d'), TIMESTAMP);
	$today_end = $today_start + 86399;
	$where = 'hotelid = :hotelid AND roomid = :roomid AND openid = :openid AND paystatus = 0 AND time >= :today_start AND time < :today_end AND status != -1 AND status != 2 ';
	$param = array(
		':hotelid' => $insert['hotelid'],
		':roomid' => $insert['roomid'],
		':openid' => $_W['openid'],
		':today_start' => $today_start,
		':today_end' => $today_end,
	);
	if (!empty($spec) && !empty($spec['spec_id'])) {
		$where .= ' AND spec_id = :spec_id';
		$param[':spec_id'] = $spec['spec_id'];
	}
	$order_exist = pdo_fetch("SELECT id FROM " . tablename('storex_order') . "WHERE " . $where, $param);
	if (!empty($order_exist)) {
		wmessage(error(-1, "您有未支付该类订单,不要重复下单"), '', 'ajax');
	}
}

//房型订单
function room_order($insert, $goods_info, $orderid = '') {
	global $_W, $_GPC;
	$insert['btime'] = strtotime($_GPC['order']['btime']);
	$insert['etime'] = strtotime($_GPC['order']['etime']);
	if (!empty($_GPC['order']['day'])) {
		$insert['day'] = intval($_GPC['order']['day']);
	} else {
		$insert['day'] = ceil(($insert['etime'] - $insert['btime'])/86400);
	}
	if ($insert['day'] <= 0) {
		wmessage(error(-1, '天数不能是零'), '', 'ajax');
	}
	if ($insert['btime'] < strtotime('today')) {
		wmessage(error(-1, '预定的开始日期不能小于当日的日期'), '', 'ajax');
	}
	if (8 < $insert['nums']) {
		wmessage(error(-1, '订单购买数量超过最大限制'), '', 'ajax');
	}
	if (!empty($orderid)) {
		$status = check_room_assign($insert, array($insert['roomitemid']), true);
		if (empty($status)) {
			wmessage(error(-1, '续订该房间已被分配了，请联系管理员'), '', 'ajax');
		}
	}
	$bdate = date('Y-m-d', $insert['btime']);
	$days = $insert['day'];
	$edate = date('Y-m-d', $insert['etime']);
	$dates = get_dates($bdate, $days);
	$search_data = array(
		'btime' => $bdate,
		'etime' => $edate,
		'nums' => $insert['nums'],
	);
	check_room_nums($dates, $search_data, $goods_info);
	$goods_info = calcul_roon_sumprice($dates, $search_data, $goods_info);
	if (!empty($goods_info['price_list']) && is_array($goods_info['price_list'])) {
		foreach ($goods_info['price_list'] as $info) {
			if ($info['date'] == $bdate) {
				$insert['cprice'] = $info['cprice'];
				break;
			}
		}
	}
	$insert['sum_price'] = $goods_info['sum_price'];
	pdo_query('UPDATE ' . tablename('storex_order') . " SET status = -1, newuser = 0 WHERE time < :time AND weid = :weid AND paystatus = '0' AND status <> '1' AND status <> '3'", array(':time' => time() - 86400, ':weid' => $_W['uniacid']));
	return $insert;
}

//计算优惠后的价格  店铺营销和会员卡优惠（会员卡和卡券用一个）
function calcul_discounts_price($insert, $store_info, $uid, $selected_coupon = array()) {
	if ($store_info['market_status'] == 1) {
		$markets = get_store_market($store_info['id']);
		if (!empty($markets) && is_array($markets)) {
			$market_types = array();
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
			$extra_info = $selected_coupon['coupon_info']['extra'];
			if ($selected_coupon['coupon_info']['type'] == COUPON_TYPE_DISCOUNT) {
				$insert['sum_price'] = $insert['sum_price'] * $extra_info['discount'] / 100;
			} elseif ($selected_coupon['coupon_info']['type'] == COUPON_TYPE_CASH) {
				$least_cost = $extra_info['least_cost'] * 0.01;
				$reduce_cost = $extra_info['reduce_cost'] * 0.01;
				if ($insert['sum_price'] >= $least_cost) {
					$insert['sum_price'] = $insert['sum_price'] - $reduce_cost;
				}
			}
			$insert['coupon'] = $selected_coupon['recid'];
		} elseif ($selected_coupon['type'] == 2) {
			$user_group = card_group_id($uid);
			if (!empty($user_group)) {
				$insert['sum_price'] = card_discount_price($uid, $insert['sum_price']);
			}
		}
	}
	return $insert;
}

//计算积分抵扣后的金额
function calcul_credit_replace($insert, $uid) {
	$credit_replace = get_credit_replace($insert['hotelid'], $uid);
	if ($credit_replace['credit_pay'] == 1) {
		if ($credit_replace['cost_credit'] > $credit_replace['credit1']) {
			wmessage(error(-1, '积分不足'), '', 'ajax');
		}
		if ($insert['sum_price'] > $credit_replace['max_replace']) {
			$insert['cost_credit'] = $credit_replace['cost_credit'];
			$insert['replace_money'] = $credit_replace['max_replace'];
			$insert['sum_price'] -= $credit_replace['max_replace'];
			return $insert;
		} else {
			wmessage(error(-1, '总价小于抵扣价格，不能使用抵扣'), '', 'ajax');
		}
	}
}

//检查商品是否有活动
function check_goods_activity($insert, $activity, $order_id = '') {
	if (!empty($activity) && $activity['type'] == ACTIVITY_SECKILL) {
		$check_activity = pdo_get('storex_goods_activity', array('id' => $activity['id']), array('nums', 'sell_nums'));
		if ($insert['nums'] > ($check_activity['nums'] - $check_activity['sell_nums'])) {
			if (!empty($order_id)) {
				pdo_delete('storex_order', array('id' => $order_id));
			}
			wmessage(error(-1, '库存不足,下单失败'), '', 'ajax');
		}
		pdo_update('storex_goods_activity', array('sell_nums +=' => $insert['nums']), array('id' => $activity['id']));
	}
}

//发送邮件
function storex_send_email($store_info, $insert, $goods_info) {
	global $_W;
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
				$body .= "到店时间: " . date('Y-m-d', $insert['btime']) . "<br/>";
				$body .= "离店时间: " . date('Y-m-d', $insert['etime']) . "<br/><br/>";
			}
			load()->func('communication');
			ihttp_email($mail, $subject, $body);
		}
	}
}

//发送短信
function storex_send_sms($insert, $store_info) {
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
}

//发送微信提醒
function storex_send_notice($store_info, $orderid = '') {
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
}