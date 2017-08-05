<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('goods_info', 'info', 'order', 'goods_comments');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'error';

check_params();
mload()->model('activity');
mload()->model('card');
mload()->model('clerk');
mload()->model('order');
$uid = mc_openid2uid($_W['openid']);
$store_id = intval($_GPC['id']);
$goodsid = intval($_GPC['goodsid']);
$max_room = 8;

//获取某个商品的详细信息
if ($op == 'goods_info') {
	$store_info = get_store_info($store_id);
	$condition = array('weid' => intval($_W['uniacid']), 'id' => $goodsid, 'status' => 1);
	if ($store_info['store_type'] == 1) {
		$condition['hotelid'] = $store_id;
		$table = 'storex_room';
	} else {
		$condition['store_base_id'] = $store_id;
		$table = 'storex_goods';
	}
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
		$tags = store_goods_tags($store_info['id']);
		$goods_info['tag'] = get_goods_tag($tags, $goods_info['tag']);
	}
	if (!empty($goods_info['express_set'])) {
		$goods_info['express_set'] = iunserializer($goods_info['express_set']);
	}
	$share_data = array(
		'title' => $goods_info['title'],
		'desc' => $goods_info['title'] . '--' . $store_info['title'],
		'link' => murl('entry', array('do' => 'display', 'id' => $store_id, 'm' => 'wn_storex', 'type' => 'goods_info', 'goodsid' => $goodsid), true, true),
		'imgUrl' => tomedia($goods_info['thumb'])
	);
	$goods_info['defined'] = get_goods_defined($store_id, $goodsid);
	$single_comment = array();
	$single_comment = pdo_get('storex_comment', array('uniacid' => $_W['uniacid'], 'hotelid' => $store_id, 'goodsid' => $goodsid, 'comment <>' => ''), array('createtime', 'comment', 'nickname', 'thumb'));
	if (!empty($single_comment)) {
		$single_comment['thumb'] = tomedia($single_comment['thumb']);
		$single_comment['createtime'] = date('Y-m-d', $single_comment['createtime']);
	}
	$goods_info['comment'] = $single_comment;
	wmessage(error(0, $goods_info), $share_data, 'ajax');
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
	$condition = array('weid' => intval($_W['uniacid']), 'id' => $goodsid, 'status' => 1);
	if ($store_info['store_type'] == 1) {
		$condition['hotelid'] = $store_id;
		$table = 'storex_room';
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
		$condition['store_base_id'] = $store_id;
		$table = 'storex_goods';
		$goods_info = pdo_get($table, $condition);
	}
	if (!empty($goods_info['express_set'])) {
		$goods_info['express_set'] = iunserializer($goods_info['express_set']);
	}
	$goods_info['defined'] = get_goods_defined($store_id, $goodsid);
	$address = pdo_getall('mc_member_address', array('uid' => $uid, 'uniacid' => intval($_W['uniacid'])));
	$infos['info'] = $info;
	$infos['goods_info'] = $goods_info;
	$infos['address'] = $address;
	if ($store_info['market_status'] == 1) {
		$infos['market'] = get_store_market($store_id);
	} else {
		$paycenter_couponlist = activity_paycenter_get_coupon();
		$infos['coupon_list'] = $paycenter_couponlist;
		$card_activity_info = card_return_credit_info();
		$infos['card_disounts_info'] = array();
		if (!empty($card_activity_info)) {
			$user_group = card_group_id($uid);
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
		$store_id = $order['hotelid'];
		$goodsid = $order['roomid'];
		$order_info['hotelid'] = $store_id;
		$order_info['contact_name'] = trim($order['contact_name']);
		$order_info['roomid'] = $goodsid;
		$order_info['mobile'] = $order['mobile'];
		$order_info['remark'] = $order['remark'];
		$order_info['nums'] = 1;
		$order_info['roomitemid'] = $order['roomitemid'];
	} else {
		//销售员
		$salesman = $_GPC['salesman'];
		
		$order_info['hotelid'] = $store_id;
		$order_info['contact_name'] = trim($_GPC['order']['contact_name']);
		$order_info['roomid'] = $goodsid;
		$order_info['mobile'] = trim($_GPC['order']['mobile']);
		$order_info['remark'] = trim($_GPC['order']['remark']);
		$order_info['nums'] = intval($_GPC['order']['nums']);
		$order_info['salesman'] = $salesman;
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
	$condition = array('weid' => intval($_W['uniacid']), 'id' => $goodsid, 'status' => 1);
	if ($store_info['store_type'] == 1) {
		$table = 'storex_room';
		$condition['hotelid'] = $store_id;
	} else {
		$table = 'storex_goods';
		$condition['store_base_id'] = $store_id;
	}
	$goods_info = pdo_get($table, $condition);
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
	);
	if ($goods_info['cprice'] == 0) {
		wmessage(error(-1, '商品价格不能是0，请联系管理员!'), '', 'ajax');
	}
	$today_start = strtotime(date('Y-m-d'), TIMESTAMP);
	$today_end = $today_start + 86399;
	$param = array(
		':hotelid' => $order_info['hotelid'],
		':roomid' => $order_info['roomid'],
		':openid' => $_W['openid'],
		':today_start' => $today_start,
		':today_end' => $today_end,
	);
	$order_exist = pdo_fetch("SELECT id FROM " . tablename('storex_order') . "WHERE hotelid = :hotelid AND roomid = :roomid AND openid = :openid AND paystatus = 0 AND time >= :today_start AND time < :today_end AND status != -1 AND status != 2", $param);
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
			if ($max_room < $order_info['nums']) {
				wmessage(error(-1, '订单购买数量超过最大限制'), '', 'ajax');
			}
			if (!empty($orderid)) {
				$status = check_room_assign($order_info, $order_info['roomitemid']);
				if (empty($status)) {
					wmessage(error(-1, '续订该房间已被分配了，请联系管理员'), '', 'ajax');
				}
			}
			$btime = $order_info['btime'];
			$bdate = date('Y-m-d', $order_info['btime']);
			$days = $order_info['day'];
			$etime = $order_info['etime'];
			$edate = date('Y-m-d', $order_info['etime']);
			$dates = get_dates($bdate, $days);
			//酒店信息
			$sql = 'SELECT `id`, `roomdate`, `num`, `status` FROM ' . tablename('storex_room_price') . ' WHERE `roomid` = :roomid
				AND `roomdate` >= :btime AND `roomdate` < :etime AND `status` = :status';
			$params = array(':roomid' => $goodsid, ':btime' => $btime, ':etime' => $etime, ':status' => '1');
			$room_date_list = pdo_fetchall($sql, $params);
			$flag = intval($room_date_list);
			$list = array();
			if ($flag == 1) {
				for($i = 0; $i < $days; $i++) {
					$k = $dates[$i]['time'];
					foreach ($room_date_list as $p_key => $p_value) {
						// 判断价格表中是否有当天的数据
						if ($p_value['roomdate'] == $k) {
							if ($p_value['num'] == -1) {
								$max_room = 8;
							} else {
								$room_num = $p_value['num'];
								if (empty($room_num)) {
									$max_room = 0;
									$list['num'] = 0;
									$list['date'] =  $dates[$i]['date'];
								} elseif ($room_num > 0 && $room_num <= $max_room) {
									$max_room = $room_num;
									$list['num'] =  $room_num;
									$list['date'] =  $dates[$i]['date'];
								} elseif ($room_num > 0 && $room_num > $max_room) {
									$list['num'] =  $max_room;
									$list['date'] =  $dates[$i]['date'];
								} else {
									$max_room = 0;
								}
							}
							break;
						}
					}
					if ($max_room == 0 || $max_room < $order_info['nums']) {
						wmessage(error(-1, '房间数量不足,请选择其他房型或日期!'), '', 'ajax');
					}
				}
			}
			
			$search_data = array(
				'btime' => $bdate,
				'etime' => $edate,
				'nums' => $_GPC['order']['nums']
			);
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
		$stock = check_goods_stock($goodsid, $order_info['nums']);
		if (is_error($stock)) {
			wmessage($stock, '', 'ajax');
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
	}
	
	$insert['sum_price'] = sprintf ('%1.2f', $insert['sum_price']);
	$post_total = trim($_GPC['order']['total']);
	if ($post_total != $insert['sum_price']) {
		wmessage(error(-1, '价格错误'), '', 'ajax');
	}
	if ($insert['sum_price'] <= 0) {
		wmessage(error(-1, '总价为零，请联系管理员'), '', 'ajax');
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
			stock_control($goodsid, $insert['nums'], 'order');
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