<?php
//检查每个文件的传值是否为空
function check_params() {
	global $_W, $_GPC;
	if (!empty($_GPC['wxapp']) && $_GPC['wxapp'] == 'wxapp') {
		$acid = $_GPC['acid'];
		$_W['account'] = account_fetch($acid);
		$user_info = pdo_get('mc_mapping_fans', array('openid' => $_GPC['u_openid']), array('openid', 'uid'));
		load()->model('cache');
		$cachekey = cache_system_key("uid:{$user_info['openid']}");
		cache_write($cachekey, $user_info);
		$_W['openid'] = $user_info['openid'];
	}
	$permission_lists = array(
		'common' => array(
			'uniacid' => intval($_W['uniacid'])
		),
		'store' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid'])
			),
			'store_list' => array(),
			'store_detail' => array(
				'store_id' => intval($_GPC['store_id'])
			),
			'store_comment' => array(
				'id' => intval($_GPC['id']),
			),
		),
		'category' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'id' => intval($_GPC['id'])
			),
			'category_list' => array(),
			'goods_list' => array(
				'first_id' => intval($_GPC['first_id'])
			),
			'more_goods' => array(
				'id' => intval($_GPC['id']),
			),
			'class' => array(
				'id' => intval($_GPC['id']),
			),
			'sub_class' => array(
				'id' => intval($_GPC['id']),
			),
		),
		'goods' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
 				'openid' => $_W['openid']
			),
			'goods_info' => array(
				'id' => intval($_GPC['id']),
				'goodsid' => intval($_GPC['goodsid'])
			),
			'info' => array(),
			'order' => array(
				'id' => intval($_GPC['id']),
				'goodsid' => intval($_GPC['goodsid']),
				'action' => trim($_GPC['action'])
			)
		),
		'orders' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
 				'openid' => $_W['openid']
			),
			'order_list' => array(),
			'order_detail' => array(
				'id' => intval($_GPC['id']),
			),
			'orderpay' => array(
				'id' => intval($_GPC['id']),
			),
			'cancel' => array(
				'id' => intval($_GPC['id']),
			),
			'confirm_goods' => array(
				'id' => intval($_GPC['id']),
			),
			'order_comment' => array(
				'id' => intval($_GPC['id']),
			)
		),
		'usercenter' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'openid' => $_W['openid']
			),
			'personal_info' => array(),
			'personal_update' => array(),
			'credits_record' => array(
				'credittype' => $_GPC['credittype']
			),
			'address_lists' => array(),
			'current_address' => array(
				'id' => intval($_GPC['id'])
			),
			'address_post' => array(),
			'address_default' => array(
				'id' => intval($_GPC['id'])
			),
			'address_delete' => array(
				'id' => intval($_GPC['id'])
			),
			'extend_switch' => array(),
		),
		'clerk' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'openid' => $_W['openid'],
			),
			'order' => array(),
			'order_info' => array(
				'orderid' => $_GPC['orderid'],
			),
			'edit_order' => array(
				'orderid' => $_GPC['orderid'],
			),
			'room' => array(),
			'room_info' => array(
				'room_id' => $_GPC['room_id'],
			),
			'edit_room' => array(
				'room_id' => $_GPC['room_id'],
			),
			'permission_storex' => array(
				'type' => $_GPC['type'],
			),
			'assign_room' => array(
				'orderid' => $_GPC['orderid'],
				'roomids' => $_GPC['roomids'],
			),
			'goods' => array(),
			'status' => array(
				'goodsid' => $_GPC['goodsid'],
				'storeid' => $_GPC['storeid'],
			),
		),
		'sign' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'openid' => $_W['openid'],
			),
			'sign_info' => array(),
			'sign' => array(
				'day' => intval($_GPC['day']),
			),
			'remedy_sign' => array(
				'day' => intval($_GPC['day']),
			),
		),
		'notice' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'openid' => $_W['openid'],
			),
			'notice_list' => array(),
			'read_notice' => array(
				'id' => intval($_GPC['id']),
			),
			'get_info' => array(),
		),
		'membercard' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'openid' => $_W['openid'],
			),
			'receive_info' => array(),
			'receive_card' => array(),
		),
		'coupon' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'openid' => $_W['openid'],
			),
			'exchange' => array(
				'id' => intval($_GPC['id']),
			),
			'mine' => array(),
			'detail' => array(
				'couponid' => intval($_GPC['couponid']),
				'id' => intval($_GPC['recid']),
			),
		),
		'recharge' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'openid' => $_W['openid']
			),
		),
		'agent' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'openid' => $_W['openid']
			),
		),
		'package' => array(
			'common' => array(
				'uniacid' => intval($_W['uniacid']),
				'openid' => $_W['openid']
			),
		)
	);
	$do = trim($_GPC['do']);
	$op = trim($_GPC['op']);
	if (!empty($permission_lists[$do])) {
		if (!empty($permission_lists[$do]['common'])) {
			foreach ($permission_lists[$do]['common'] as $key => $val) {
				if (empty($val)) {
					if ($key == 'openid') {
						if ($_GPC['wxapp'] == 'wxapp') {
							wmessage(error(41009, '未登录！'), '', 'ajax');
						} else {
							wmessage(error(41009, '请先关注公众号' . $_W['account']['name']), '', 'ajax');
						}
					}
					wmessage(error(-1, '参数错误'), '', 'ajax');
				}
			}
		}
		if (!empty($permission_lists[$do][$op])) {
			foreach ($permission_lists[$do][$op] as $val) {
				if (empty($val)) {
					wmessage(error(-1, '参数错误'), '', 'ajax');
				}
			}
		}
	}
}

/**格式化图片的路径
 * $urls  url数组
 */
function format_url($urls) {
	foreach ($urls as $k => $url) {
		$urls[$k] = tomedia($url);
	}
	return $urls;
}
//获取店铺信息
function get_store_info($id) {
	global $_W;
	$store_info = pdo_get('storex_bases', array('weid' => $_W['uniacid'], 'id' => $id), array('id', 'store_type', 'status', 'title', 'phone', 'thumb', 'emails', 'phones', 'openids', 'mail', 'refund', 'market_status', 'max_replace', 'pick_up_mode'));
	if (empty($store_info)) {
		wmessage(error(-1, '店铺不存在'), '', 'ajax');
	} else {
		if ($store_info['status'] == 0) {
			wmessage(error(-1, '店铺已隐藏'), '', 'ajax');
		} else {
			$store_info['emails'] = iunserializer($store_info['emails']);
			$store_info['phones'] = iunserializer($store_info['phones']);
			$store_info['openids'] = iunserializer($store_info['openids']);
			$store_info['pick_up_mode'] = iunserializer($store_info['pick_up_mode']);
			return $store_info;
		}
	}
}
//根据坐标计算距离
function distanceBetween($longitude1, $latitude1, $longitude2, $latitude2) {
	$radLat1 = radian($latitude1);
	$radLat2 = radian($latitude2);
	$a = radian($latitude1) - radian($latitude2);
	$b = radian($longitude1) - radian($longitude2);
	$s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
	$s = $s * 6378.137; //乘上地球半径，单位为公里
	$s = round($s * 10000) / 10000; //单位为公里(km)
	return $s * 1000; //单位为m
}
function radian($d) {
	return $d * 3.1415926535898 / 180.0;
}
//支付
function pay_info($order_id) {
	global $_W;
	$order_info = pdo_get('storex_order', array('id' => $order_id, 'weid' => intval($_W['uniacid']), 'openid' => $_W['openid']));
	if (!empty($order_info)) {
		$params = array(
			'ordersn' => $order_info['ordersn'],
			'tid' => $order_info['id'],//支付订单编号, 应保证在同一模块内部唯一
			'title' => $order_info['style'],
			'fee' => $order_info['sum_price'],//总费用, 只能大于 0
			'user' => $_W['openid']//付款用户, 付款的用户名(选填项)
		);
		return $params;
	} else {
		wmessage(error(-1, '获取订单信息失败'), '', 'ajax');
	}
}

//获取一二级分类下的商品信息
function category_store_goods($table, $condition, $fields, $limit = array()) {
	$goods = pdo_getall($table, $condition, $fields, '', 'sortid DESC', $limit);
	foreach ($goods as $k => $info) {
		if (!empty($info['thumb'])) {
			$goods[$k]['thumb'] = tomedia($info['thumb']);
		}
		if (!empty($info['thumbs'])) {
			foreach ($info['thumbs'] as $key => $url) {
				$goods[$k]['thumbs'][$key] = tomedia($url);
			}
		}
	}
	if ($table == 'storex_room') {
		$goods = room_special_price($goods, array(), true);
	}
	return $goods;
}

//根据日期和数量获取可预定的房型
function category_room_status($goods_list, $search_data) {
	global $_GPC,$_W;
	$btime = $search_data['btime'];
	$etime = $search_data['etime'];
	$num = $search_data['nums'];
	if (!empty($btime) && !empty($etime) && !empty($num)) {
		if ($num <= 0 || strtotime($etime) < strtotime($btime) || strtotime($btime) < strtotime('today')) {
			wmessage(error(-1, '数量不能是零'), '', 'ajax');
		}
		if (strtotime($etime) < strtotime($btime)) {
			wmessage(error(-1, '结束时间不能小于开始时间'), '', 'ajax');
		}
		if (strtotime($btime) < strtotime('today')) {
			wmessage(error(-1, '开始时间不能小于当天'), '', 'ajax');
		}
	} else {
		$num = 1;
		$btime = date('Y-m-d');
		$etime = date('Y-m-d', time() + 86400);
	}
	$days = ceil((strtotime($etime) - strtotime($btime)) / 86400);
	$sql = "SELECT * FROM " . tablename('storex_room_price') . " WHERE weid = :weid AND roomdate >= :btime AND roomdate <= :etime ORDER BY roomdate ASC";
	$modify_recored = pdo_fetchall($sql, array(':weid' => intval($_W['uniacid']), ':btime' => strtotime($btime), ':etime' => strtotime($etime)));
	if (!empty($modify_recored)) {
		foreach ($modify_recored as $value) {
			foreach ($goods_list as &$info) {
				if ($value['roomid'] == $info['id'] && $value['hotelid'] == $info['store_base_id']) {
					if (isset($info['max_room']) && $info['max_room'] == 0) {
						$info['room_counts'] = 0;
						continue;
					}
					if ($value['status'] == 1) {
						if ($value['num'] == -1) {
							if (empty($info['max_room']) && $info['max_room'] != 0) {
								$info['max_room'] = 8;
								$info['room_counts'] = '不限';
							}
						} else {
							if ($value['num'] > 8 && $value['num'] > $info['max_room']) {
								$info['max_room'] = 8;
							} elseif ($value['num'] < $info['max_room'] || !isset($info['max_room'])) {
								$info['max_room'] = $value['num'];
							}
							$info['room_counts'] = $value['num'];
						}
					} else {
						$info['max_room'] = 0;
						$info['room_counts'] = 0;
					}
				}
			}
		}
	}
	foreach ($goods_list as $k => $val) {
		if (!isset($val['max_room'])) {
			$val['max_room'] = 8;
			$val['room_counts'] = '不限';
		} elseif (!empty($num) && $val['max_room'] < $num) {
			unset($goods_list[$k]);
			continue;
		}
		$goods_list[$k] = get_room_params($val);
	}
	return $goods_list;
}
function get_room_params($info) {
	$info['params'] = '';
	if ($info['bed_show'] == 1) {
		$info['params'] = "床位(" . $info['bed'] . ")";
	}
	if ($info['floor_show'] == 1) {
		if (!empty($info['params'])) {
			$info['params'] .= " | 楼层(" . $info['floor'] . ")";
		} else {
			$info['params'] = "楼层(" . $info['floor'] . ")";
		}
	}
	return $info;
}
//获取日期格式
function get_dates($btime, $days) {
	$dates = array();
	$dates[0]['date'] = $btime;
	$dates[0]['day'] = date('j', strtotime($btime));
	$dates[0]['time'] = strtotime($btime);
	$dates[0]['month'] = date('m',strtotime($btime));
	if ($days > 1) {
		for ($i = 1; $i < $days; $i++) {
			$dates[$i]['time'] = $dates[$i - 1]['time'] + 86400;
			$dates[$i]['date'] = date('Y-m-d', $dates[$i]['time']);
			$dates[$i]['day'] = date('j', $dates[$i]['time']);
			$dates[$i]['month'] = date('m', $dates[$i]['time']);
		}
	}
	return $dates;
}

//计算所选房型的总价
function calcul_roon_sumprice($dates, $search_data, $goods_info) {
	$prices = array('oprice' => $goods_info['oprice'], 'cprice' => $goods_info['cprice']);
	$goods_info = room_special_price($goods_info, $search_data, false);
	$sumprice = 0;
	$noexist_date = 0;
	$exist_date = 0;
	$price_detail = array();
	if (!empty($dates) && is_array($dates)) {
		foreach ($dates as $date) {
			if (!empty($goods_info['price_list']) && !empty($goods_info['price_list'][$date['date']])) {
				$sumprice += $goods_info['price_list'][$date['date']]['cprice'];
				$exist_date += 1;
				$price_detail[] = array(
					'date' => $date['date'],
					'oprice' => $goods_info['price_list'][$date['date']]['oprice'],
					'cprice' => $goods_info['price_list'][$date['date']]['cprice'],
				);
			} else {
				$noexist_date += 1;
				$price_detail[] = array(
					'date' => $date['date'],
					'oprice' => $prices['oprice'],
					'cprice' => $prices['cprice'],
				);
			}
		}
	}
	if (($exist_date + $noexist_date) < count($dates)) {
		$noexist_date += count($dates) - ($exist_date + $noexist_date);
	}
	$sumprice += $noexist_date * $prices['cprice'];
	if (empty($search_data['nums'])) {
		$search_data['nums'] = 1;
	}
	$goods_info['sum_price'] = ($sumprice + $goods_info['service'] * count($dates)) * $search_data['nums'];
	$goods_info['price_list'] = $price_detail;
	return $goods_info;
}

//根据信息获取房型的某一天的价格
function room_special_price($goods, $search_data = array(), $plural = true) {
	global $_W;
	if (!empty($goods)) {
		if (!empty($search_data) && !empty($search_data['btime']) && !empty($search_data['etime']) && !empty($search_data['nums'])) {
			$btime = strtotime($search_data['btime']);
			$etime = strtotime($search_data['etime']);
		} else {
			$search_data['btime'] = date('Y-m-d');
			$search_data['etime'] = date('Y-m-d', TIMESTAMP + 86400);
			$search_data['nums'] = 1;
			$btime = strtotime(date('Y-m-d'));
			$etime = $btime + 86400;
		}
		$condition = '';
		$params = array(':weid' => $_W['uniacid'], ':btime' => $btime, ':etime' => $etime);
		if (empty($plural)) {
			$condition = ' AND roomid = :roomid ';
			$params[':roomid'] = $goods['id'];
		}
		$sql = "SELECT * FROM " . tablename('storex_room_price') . " WHERE `weid` = :weid AND `roomdate` >= :btime AND `roomdate` < :etime {$condition} ORDER BY roomdate ASC";
		$room_price_list = pdo_fetchall($sql, $params);
		$edit_price_list = array();
		if (!empty($room_price_list) && is_array($room_price_list)) {
			foreach ($room_price_list as $val) {
				$edit_price_list[$val['roomid']][$val['thisdate']] = $val;
			}
		}
		if (!empty($plural)) {
			foreach ($goods as $key => $val) {
				$goods[$key]['price_list'] = array();
				if (!empty($edit_price_list[$val['id']]) && !empty($edit_price_list[$val['id']][$search_data['btime']])) {
					$goods[$key]['oprice'] = $edit_price_list[$val['id']][$search_data['btime']]['oprice'];
					$goods[$key]['cprice'] = $edit_price_list[$val['id']][$search_data['btime']]['cprice'];
					if ($edit_price_list[$val['id']][$search_data['btime']]['num'] == -1) {
						$goods[$key]['max_room'] = 8;
					} else {
						$goods[$key]['max_room'] = $edit_price_list[$val['id']][$search_data['btime']]['num'];
					}
					$goods[$key]['price_list'] = $edit_price_list[$val['id']];
				} else {
					$goods[$key]['max_room'] = 8;
				}
			}
		} else {
			$goods['price_list'] = array();
			if (!empty($edit_price_list[$goods['id']])) {
				$goods['oprice'] = $edit_price_list[$goods['id']][$search_data['btime']]['oprice'];
				$goods['cprice'] = $edit_price_list[$goods['id']][$search_data['btime']]['cprice'];
				if ($edit_price_list[$goods['id']][$search_data['btime']]['num'] == -1) {
					$goods['max_room'] = 8;
				} else {
					$goods['max_room'] = $edit_price_list[$goods['id']][$search_data['btime']]['num'];
				}
				$goods['price_list'] = $edit_price_list[$goods['id']];
			} else {
				$goods['max_room'] = 8;
			}
		}
	}
	return $goods;
}

function check_room_nums($dates, $search_data, $goods_info) {
	$sql = 'SELECT `id`, `roomdate`, `num`, `status` FROM ' . tablename('storex_room_price') . ' WHERE `roomid` = :roomid AND `roomdate` >= :btime AND `roomdate` < :etime AND `status` = :status';
	$params = array(':roomid' => $goods_info['roomid'], ':btime' => strtotime($search_data['btime']), ':etime' => strtotime($search_data['etime']), ':status' => '1');
	$room_date_list = pdo_fetchall($sql, $params);
	$max_room = 8;
	$list = array();
	if (!empty($room_date_list) && is_array($room_date_list)) {
		for($i = 0; $i < $days; $i++) {
			$k = $dates[$i]['time'];
			foreach ($room_date_list as $p_key => $p_value) {
				// 判断价格表中是否有当天的数据
				if ($p_value['roomdate'] == $k) {
					if ($p_value['num'] == -1) {
						$max_room = 8;
					} else {
						$room_num = $p_value['num'];
						$list['date'] =  $dates[$i]['date'];
						if (empty($room_num) || $room_num < 0) {
							$max_room = 0;
							$list['num'] = 0;
						} elseif ($room_num > 0 && $room_num <= $max_room) {
							$max_room = $room_num;
							$list['num'] =  $room_num;
						} elseif ($room_num > 0 && $room_num > $max_room) {
							$list['num'] =  $max_room;
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
}

function send_custom_notice($msgtype, $text, $touser) {
	if (!check_wxapp()) {
		$account_api = WeAccount::create();
		$custom = array(
			'msgtype' => $msgtype,
			'text' => $text,
			'touser' => $touser,
		);
		$status = $account_api->sendCustomNotice($custom);
		return $status;
	}
}
function code2city($code) {
	$city_json = '{"A":[{"id":"152900","name":"阿拉善盟"},{"id":"210300","name":"鞍山市"},{"id":"340800","name":"安庆市"},{"id":"410500","name":"安阳市"},{"id":"513200","name":"阿坝藏族羌族自治州"},{"id":"520400","name":"安顺市"},{"id":"542500","name":"阿里地区"},{"id":"610900","name":"安康市"},{"id":"652900","name":"阿克苏地区"},{"id":"654300","name":"阿勒泰地区"},{"id":"820100","name":"澳门半岛"},{"id":"659002","name":"阿拉尔市"}],"B":[{"id":"110100","name":"北京市"},{"id":"130600","name":"保定市"},{"id":"150200","name":"包头市"},{"id":"150800","name":"巴彦淖尔市"},{"id":"210500","name":"本溪市"},{"id":"220600","name":"白山市"},{"id":"220800","name":"白城市"},{"id":"340300","name":"蚌埠市"},{"id":"341600","name":"亳州市"},{"id":"371600","name":"滨州市"},{"id":"450500","name":"北海市"},{"id":"451000","name":"百色市"},{"id":"511900","name":"巴中市"},{"id":"522400","name":"毕节地区"},{"id":"530500","name":"保山市"},{"id":"610300","name":"宝鸡市"},{"id":"620400","name":"白银市"},{"id":"652700","name":"博尔塔拉蒙古自治州"},{"id":"652800","name":"巴音郭楞蒙古自治州"}],"C":[{"id":"130800","name":"承德市"},{"id":"130900","name":"沧州市"},{"id":"140400","name":"长治市"},{"id":"150400","name":"赤峰市"},{"id":"220100","name":"长春市"},{"id":"320400","name":"常州市"},{"id":"341100","name":"滁州市"},{"id":"341400","name":"巢湖市"},{"id":"341700","name":"池州市"},{"id":"430100","name":"长沙市"},{"id":"430700","name":"常德市"},{"id":"431000","name":"郴州市"},{"id":"445100","name":"潮州市"},{"id":"451400","name":"崇左市"},{"id":"500100","name":"重庆市"},{"id":"510100","name":"成都市"},{"id":"532300","name":"楚雄彝族自治州"},{"id":"542100","name":"昌都地区"},{"id":"652300","name":"昌吉回族自治州"}],"D":[{"id":"140200","name":"大同市"},{"id":"210200","name":"大连市"},{"id":"210600","name":"丹东市"},{"id":"230600","name":"大庆市"},{"id":"232700","name":"大兴安岭地区"},{"id":"370500","name":"东营市"},{"id":"371400","name":"德州市"},{"id":"441900","name":"东莞市"},{"id":"510600","name":"德阳市"},{"id":"511700","name":"达州市"},{"id":"532900","name":"大理白族自治州"},{"id":"533100","name":"德宏傣族景颇族自治州"},{"id":"533400","name":"迪庆藏族自治州"},{"id":"621100","name":"定西市"},{"id":"469003","name":"儋州市"}],"E":[{"id":"150600","name":"鄂尔多斯市"},{"id":"420700","name":"鄂州市"},{"id":"422800","name":"恩施土家族苗族自治州"}],"F":[{"id":"210400","name":"抚顺市"},{"id":"210900","name":"阜新市"},{"id":"341200","name":"阜阳市"},{"id":"350100","name":"福州市"},{"id":"361000","name":"抚州市"},{"id":"440600","name":"佛山市"},{"id":"450600","name":"防城港市"}],"G":[{"id":"360700","name":"赣州市"},{"id":"440100","name":"广州市"},{"id":"450300","name":"桂林市"},{"id":"450800","name":"贵港市"},{"id":"510800","name":"广元市"},{"id":"511600","name":"广安市"},{"id":"513300","name":"甘孜藏族自治州"},{"id":"520100","name":"贵阳市"},{"id":"623000","name":"甘南藏族自治州"},{"id":"632600","name":"果洛藏族自治州"},{"id":"640400","name":"固原市"},{"id":"710200","name":"高雄市"},{"id":"712300","name":"高雄县"}],"H":[{"id":"130400","name":"邯郸市"},{"id":"131100","name":"衡水市"},{"id":"150100","name":"呼和浩特市"},{"id":"150700","name":"呼伦贝尔市"},{"id":"211400","name":"葫芦岛市"},{"id":"230100","name":"哈尔滨市"},{"id":"230400","name":"鹤岗市"},{"id":"231100","name":"黑河市"},{"id":"320800","name":"淮安市"},{"id":"330100","name":"杭州市"},{"id":"330500","name":"湖州市"},{"id":"340100","name":"合肥市"},{"id":"340400","name":"淮南市"},{"id":"340600","name":"淮北市"},{"id":"341000","name":"黄山市"},{"id":"371700","name":"菏泽市"},{"id":"410600","name":"鹤壁市"},{"id":"420200","name":"黄石市"},{"id":"421100","name":"黄冈市"},{"id":"430400","name":"衡阳市"},{"id":"431200","name":"怀化市"},{"id":"441300","name":"惠州市"},{"id":"441600","name":"河源市"},{"id":"451100","name":"贺州市"},{"id":"451200","name":"河池市"},{"id":"460100","name":"海口市"},{"id":"532500","name":"红河哈尼族彝族自治州"},{"id":"610700","name":"汉中市"},{"id":"632100","name":"海东地区"},{"id":"632200","name":"海北藏族自治州"},{"id":"632300","name":"黄南藏族自治州"},{"id":"632500","name":"海南藏族自治州"},{"id":"632800","name":"海西蒙古族藏族自治州"},{"id":"652200","name":"哈密地区"},{"id":"653200","name":"和田地区"},{"id":"712600","name":"花莲县"}],"J":[{"id":"140500","name":"晋城市"},{"id":"140700","name":"晋中市"},{"id":"210700","name":"锦州市"},{"id":"220200","name":"吉林市"},{"id":"230300","name":"鸡西市"},{"id":"230800","name":"佳木斯市"},{"id":"330400","name":"嘉兴市"},{"id":"330700","name":"金华市"},{"id":"360200","name":"景德镇市"},{"id":"360400","name":"九江市"},{"id":"360800","name":"吉安市"},{"id":"370100","name":"济南市"},{"id":"370800","name":"济宁市"},{"id":"410800","name":"焦作市"},{"id":"420800","name":"荆门市"},{"id":"421000","name":"荆州市"},{"id":"440700","name":"江门市"},{"id":"445200","name":"揭阳市"},{"id":"620200","name":"嘉峪关市"},{"id":"620300","name":"金昌市"},{"id":"620900","name":"酒泉市"},{"id":"710500","name":"金门县"},{"id":"710700","name":"基隆市"},{"id":"710900","name":"嘉义市"},{"id":"810200","name":"九龙"},{"id":"410881","name":"济源市"},{"id":"711900","name":"嘉义县"}],"K":[{"id":"410200","name":"开封市"},{"id":"530100","name":"昆明市"},{"id":"650200","name":"克拉玛依"},{"id":"653000","name":"克孜勒苏柯尔克孜自治州"},{"id":"653100","name":"喀什地区"}],"L":[{"id":"131000","name":"廊坊市"},{"id":"141000","name":"临汾市"},{"id":"141100","name":"吕梁市"},{"id":"211000","name":"辽阳市"},{"id":"220400","name":"辽源市"},{"id":"320700","name":"连云港市"},{"id":"331100","name":"丽水市"},{"id":"341500","name":"六安市"},{"id":"350800","name":"龙岩市"},{"id":"371200","name":"莱芜市"},{"id":"371300","name":"临沂市"},{"id":"371500","name":"聊城市"},{"id":"410300","name":"洛阳市"},{"id":"431300","name":"娄底市"},{"id":"450200","name":"柳州市"},{"id":"451300","name":"来宾市"},{"id":"510500","name":"泸州市"},{"id":"511100","name":"乐山市"},{"id":"513400","name":"凉山彝族自治州"},{"id":"520200","name":"六盘水市"},{"id":"530700","name":"丽江市"},{"id":"530900","name":"临沧市"},{"id":"540100","name":"拉萨市"},{"id":"542600","name":"林芝地区"},{"id":"620100","name":"兰州市"},{"id":"621200","name":"陇南市"},{"id":"622900","name":"临夏回族自治州"},{"id":"820200","name":"离岛"}],"M":[{"id":"231000","name":"牡丹江"},{"id":"340500","name":"马鞍山"},{"id":"440900","name":"茂名市"},{"id":"441400","name":"梅州市"},{"id":"510700","name":"绵阳市"},{"id":"511400","name":"眉山市"},{"id":"711500","name":"苗栗县"}],"N":[{"id":"320100","name":"南京市"},{"id":"320600","name":"南通市"},{"id":"330200","name":"宁波市"},{"id":"350700","name":"南平市"},{"id":"350900","name":"宁德市"},{"id":"360100","name":"南昌市"},{"id":"411300","name":"南阳市"},{"id":"450100","name":"南宁市"},{"id":"511000","name":"内江市"},{"id":"511300","name":"南充市"},{"id":"533300","name":"怒江傈僳族自治州"},{"id":"542400","name":"那曲地区"},{"id":"710600","name":"南投县"}],"P":[{"id":"211100","name":"盘锦市"},{"id":"350300","name":"莆田市"},{"id":"360300","name":"萍乡市"},{"id":"410400","name":"平顶山市"},{"id":"410900","name":"濮阳市"},{"id":"510400","name":"攀枝花市"},{"id":"530800","name":"普洱市"},{"id":"620800","name":"平凉市"},{"id":"712400","name":"屏东县"},{"id":"712700","name":"澎湖县"}],"Q":[{"id":"130300","name":"秦皇岛市"},{"id":"230200","name":"齐齐哈尔市"},{"id":"230900","name":"七台河市"},{"id":"330800","name":"衢州市"},{"id":"350500","name":"泉州市"},{"id":"370200","name":"青岛市"},{"id":"441800","name":"清远市"},{"id":"450700","name":"钦州市"},{"id":"522300","name":"黔西南布依族苗族自治州"},{"id":"522600","name":"黔东南苗族侗族自治州"},{"id":"522700","name":"黔南布依族苗族自治州"},{"id":"530300","name":"曲靖市"},{"id":"621000","name":"庆阳市"},{"id":"429005","name":"潜江市"}],"R":[{"id":"371100","name":"日照市"},{"id":"542300","name":"日喀则地区"}],"S":[{"id":"130100","name":"石家庄市"},{"id":"140600","name":"朔州市"},{"id":"210100","name":"沈阳市"},{"id":"220300","name":"四平市"},{"id":"220700","name":"松原市"},{"id":"230500","name":"双鸭山市"},{"id":"231200","name":"绥化市"},{"id":"310100","name":"上海市"},{"id":"320500","name":"苏州市"},{"id":"321300","name":"宿迁市"},{"id":"330600","name":"绍兴市"},{"id":"341300","name":"宿州市"},{"id":"350400","name":"三明市"},{"id":"361100","name":"上饶市"},{"id":"411200","name":"三门峡市"},{"id":"411400","name":"商丘市"},{"id":"420300","name":"十堰市"},{"id":"421300","name":"随州市"},{"id":"430500","name":"邵阳市"},{"id":"440200","name":"韶关市"},{"id":"440300","name":"深圳市"},{"id":"440500","name":"汕头市"},{"id":"441500","name":"汕尾市"},{"id":"460200","name":"三亚市"},{"id":"510900","name":"遂宁市"},{"id":"542200","name":"山南地区"},{"id":"611000","name":"商洛市"},{"id":"640200","name":"石嘴山市"},{"id":"429021","name":"神农架林区"},{"id":"659001","name":"石河子市"},{"id":"460300","name":"三沙市"}],"T":[{"id":"120100","name":"天津市"},{"id":"130200","name":"唐山市"},{"id":"140100","name":"太原市"},{"id":"150500","name":"通辽市"},{"id":"211200","name":"铁岭市"},{"id":"220500","name":"通化市"},{"id":"321200","name":"泰州市"},{"id":"331000","name":"台州市"},{"id":"340700","name":"铜陵市"},{"id":"370900","name":"泰安市"},{"id":"411100","name":"漯河市"},{"id":"522200","name":"铜仁地区"},{"id":"610200","name":"铜川市"},{"id":"620500","name":"天水市"},{"id":"652100","name":"吐鲁番地区"},{"id":"654200","name":"塔城地区"},{"id":"710100","name":"台北市"},{"id":"710300","name":"台南市"},{"id":"710400","name":"台中市"},{"id":"429006","name":"天门市"},{"id":"659003","name":"图木舒克市"},{"id":"711100","name":"台北县"},{"id":"711400","name":"桃园县"},{"id":"711600","name":"台中县"},{"id":"712200","name":"台南县"},{"id":"712500","name":"台东县"}],"W":[{"id":"150300","name":"乌海市"},{"id":"150900","name":"乌兰察布市"},{"id":"320200","name":"无锡市"},{"id":"330300","name":"温州市"},{"id":"340200","name":"芜湖市"},{"id":"370700","name":"潍坊市"},{"id":"371000","name":"威海市"},{"id":"420100","name":"武汉市"},{"id":"450400","name":"梧州市"},{"id":"532600","name":"文山壮族苗族自治州"},{"id":"610500","name":"渭南市"},{"id":"620600","name":"武威市"},{"id":"640300","name":"吴忠市"},{"id":"650100","name":"乌鲁木齐市"},{"id":"659004","name":"五家渠市"}],"X":[{"id":"130500","name":"邢台市"},{"id":"140900","name":"忻州市"},{"id":"152200","name":"兴安盟"},{"id":"152500","name":"锡林郭勒盟"},{"id":"320300","name":"徐州市"},{"id":"341800","name":"宣城市"},{"id":"350200","name":"厦门市"},{"id":"360500","name":"新余市"},{"id":"410700","name":"新乡市"},{"id":"411000","name":"许昌市"},{"id":"411500","name":"信阳市"},{"id":"420600","name":"襄樊市"},{"id":"420900","name":"孝感市"},{"id":"421200","name":"咸宁市"},{"id":"430300","name":"湘潭市"},{"id":"433100","name":"湘西土家族苗族自治州"},{"id":"532800","name":"西双版纳傣族自治州"},{"id":"610100","name":"西安市"},{"id":"610400","name":"咸阳市"},{"id":"630100","name":"西宁市"},{"id":"710800","name":"新竹市"},{"id":"810100","name":"香港岛"},{"id":"810300","name":"新界市"},{"id":"429004","name":"仙桃市"},{"id":"711300","name":"新竹县"}],"Y":[{"id":"140300","name":"阳泉市"},{"id":"140800","name":"运城市"},{"id":"210800","name":"营口市"},{"id":"222400","name":"延边朝鲜族自治州"},{"id":"230700","name":"伊春市"},{"id":"320900","name":"盐城市"},{"id":"321000","name":"扬州市"},{"id":"360600","name":"鹰潭市"},{"id":"360900","name":"宜春市"},{"id":"370600","name":"烟台市"},{"id":"420500","name":"宜昌市"},{"id":"430600","name":"岳阳市"},{"id":"430900","name":"益阳市"},{"id":"431100","name":"永州市"},{"id":"441700","name":"阳江市"},{"id":"445300","name":"云浮市"},{"id":"450900","name":"玉林市"},{"id":"511500","name":"宜宾市"},{"id":"511800","name":"雅安市"},{"id":"530400","name":"玉溪市"},{"id":"610600","name":"延安市"},{"id":"610800","name":"榆林市"},{"id":"632700","name":"玉树藏族自治州"},{"id":"640100","name":"银川市"},{"id":"654000","name":"伊犁哈萨克自治州"},{"id":"711200","name":"宜兰县"},{"id":"712100","name":"云林县"}],"Z":[{"id":"130700","name":"张家口"},{"id":"211300","name":"朝阳市"},{"id":"321100","name":"镇江市"},{"id":"330900","name":"舟山市"},{"id":"350600","name":"漳州市"},{"id":"370300","name":"淄博市"},{"id":"370400","name":"枣庄市"},{"id":"410100","name":"郑州市"},{"id":"411600","name":"周口市"},{"id":"411700","name":"驻马店市"},{"id":"430200","name":"株洲市"},{"id":"430800","name":"张家界市"},{"id":"440400","name":"珠海市"},{"id":"440800","name":"湛江市"},{"id":"441200","name":"肇庆市"},{"id":"442000","name":"中山市"},{"id":"510300","name":"自贡市"},{"id":"512000","name":"资阳市"},{"id":"520300","name":"遵义市"},{"id":"530600","name":"昭通市"},{"id":"620700","name":"张掖市"},{"id":"640500","name":"中卫市"},{"id":"711700","name":"彰化县"}]}';
	$name = '';
	$city_lists = json_decode($city_json, true);
	foreach ($city_lists as $k => $v) {
		foreach ($v as $key => $value) {
			if ($value['id'] == $code) {
				$name = $value['name'];
			}
		}
	}
	return $name;
}

function extend_switch_fetch() {
	global $_W;
	$cachekey = "wn_storex_switch:{$_W['uniacid']}";
	$cache = cache_load($cachekey);
	if (!empty($cache)) {
		return $cache;
	}
	$card_info = pdo_get('storex_mc_card', array('uniacid' => intval($_W['uniacid'])), array('status'));
	$sign_info = pdo_get('storex_sign_set', array('uniacid' => intval($_W['uniacid'])), array('status'));
	$switchs['card'] = !empty($card_info['status']) ? $card_info['status'] : 2;
	$switchs['sign'] = !empty($sign_info['status']) ? $sign_info['status'] : 2;
	cache_write($cachekey, $switchs);
	return $switchs;
}

function general_goods_order($insert, $goods_info) {
	global $_GPC;
	if ($goods_info['store_type'] != STORE_TYPE_HOTEL) {
		$store_info = get_store_info($insert['hotelid']);
		if (!empty($store_info['pick_up_mode'])) {
			$insert['mode_distribute'] = '';
			$mode_distribute = intval($_GPC['order']['mode_distribute']);
			if (empty($_GPC['order']['order_time'])) {
				wmessage(error(-1, '请选择时间！'), '', 'ajax');
			}
			$insert['order_time'] = strtotime(intval($_GPC['order']['order_time']));
			if ($mode_distribute == 2) {//配送
				if (!empty($store_info['pick_up_mode']['express'])) {
					if (empty($_GPC['order']['addressid'])) {
						wmessage(error(-1, '地址不能为空！'), '', 'ajax');
					}
					$insert['mode_distribute'] = $mode_distribute;
					$insert['addressid'] = intval($_GPC['order']['addressid']);
					$insert['goods_status'] = 1; //到货确认  1未发送， 2已发送 ，3已收货
				}
			} elseif ($mode_distribute == 1) {
				if (!empty($store_info['pick_up_mode']['self_lift'])) {
					$insert['mode_distribute'] = $mode_distribute;
				}
			}
		}
	}
	$insert['sum_price'] = $goods_info['cprice'] * $insert['nums'];
	return $insert;
}

function calculate_express($goods_info, $insert) {
	if ($insert['mode_distribute'] == 2) {
		if (!empty($goods_info['express_set'])) {
			$express_set = iunserializer($goods_info['express_set']);
			if ($insert['sum_price'] < $express_set['full_free'] && $express_set['full_free'] != 0) {
				$insert['sum_price'] += $express_set['express'];
			}
		}
	}
	return $insert;
}

function get_plugin_list() {
	load()->model('module');
	$plugin_list = module_get_plugin_list('wn_storex');
	if (!empty($plugin_list) && is_array($plugin_list)) {
		foreach ($plugin_list as $name => $plugin) {
			$plugins[$name] = true;
		}
	}
	return $plugins;
}

function check_wxapp() {
	global $_W;
	if ($_W['account']['type'] == 4 || $_W['account']['uniacid'] != $_W['uniacid']) {
		return true;
	}
	return false;
}

function store_type_info($store_type) {
	$store_type_list = array(
		STORE_TYPE_NORMAL => '普通店铺',
		STORE_TYPE_HOTEL => '酒店'
	);
	return $store_type_list[$store_type];
}

function store_printers($storeid = '') {
	global $_W;
	$condition = array('uniacid' => $_W['uniacid']);
	if (!empty($storeid)) {
		$condition['storeid'] = $storeid;
	}
	$printer_list = pdo_getall('storex_plugin_printer', $condition, array(), 'id');
	$printer_set = pdo_getall('storex_plugin_printer_set', $condition, array('storeid', 'id', 'printerids'));
	if (!empty($storeid)) {
		if (!empty($printer_set) && is_array($printer_set)) {
			foreach ($printer_set as $info) {
				$printer_ids[] = $info['printerids'];
			}
		}
		if (!empty($printer_list) && is_array($printer_list)) {
			foreach ($printer_list as $key => &$value) {
				$value['disabled'] = 2;
				if (!in_array($key, $printer_ids)) {
					$value['disabled'] = 1;
				}
			}
		}
	} else {
		$store_printer_list = array();
		if (!empty($printer_set) && is_array($printer_set)) {
			foreach ($printer_set as $val) {
				if (!empty($printer_list[$val['printerids']])) {
					$store_printer_list[$val['storeid']][] = $printer_list[$val['printerids']];
				}
			}
		}
		return $store_printer_list;
	}
	return $printer_list;
}

/**
 * 二级分类选择器
 * @param string $name 表单名称
 * @param array $parents 父分类,
 * @param array $children 子分类,
 * @param int $parentid 选择的父 id
 * @param int $childid 选择的子id
 * @param int $store_type 店铺类型
 * @return string Html代码
 */
function wn_tpl_category_2level($name, $parents, $children, $parentid, $childid, $store_type = 0) {
	$html = '
		<script type="text/javascript">
			window._' . $name . ' = ' . json_encode($children) . ';
		</script>';
	if (!defined('TPL_INIT_CATEGORY')) {
		$html .= '
		<script type="text/javascript">
			function renderCategory(obj, name){
				var index = obj.options[obj.selectedIndex].value;
				require([\'jquery\', \'util\'], function($, u){
					$selectChild = $(\'#\'+name+\'_child\');
					var html = \'<option value="0">请选择二级分类</option>\';
					if (!window[\'_\'+name] || !window[\'_\'+name][index]) {
						$selectChild.html(html);
						return false;
					}
					for(var i=0; i< window[\'_\'+name][index].length; i++){
						html += \'<option value="\'+window[\'_\'+name][index][i][\'id\']+\'">\'+window[\'_\'+name][index][i][\'name\']+\'</option>\';
					}
					$selectChild.html(html);
				});
			}
		</script>
					';
		define('TPL_INIT_CATEGORY', true);
	}

	$html .=
	'<div class="row row-fix tpl-category-container">
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
				<select class="form-control tpl-category-parent" id="' . $name . '_parent" name="' . $name . '[parentid]" onchange="renderCategory(this,\'' . $name . '\')">
					<option value="0">请选择一级分类</option>';
	$ops = '';
	foreach ($parents as $row) {
		$html .= '
					<option value="' . $row['id'] . '" ' . (($row['id'] == $parentid) ? 'selected="selected"' : '') . '>' . $row['name'] . '</option>';
	}
	$html .= '
				</select>
			</div>';
	if ($store_type != STORE_TYPE_HOTEL) {
		$html .= '
				<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
					<select class="form-control tpl-category-child" id="' . $name . '_child" name="' . $name . '[childid]">
						<option value="0">请选择二级分类</option>';
		if (!empty($parentid) && !empty($children[$parentid])) {
			foreach ($children[$parentid] as $row) {
				$html .= '
						<option value="' . $row['id'] . '"' . (($row['id'] == $childid) ? 'selected="selected"' : '') . '>' . $row['name'] . '</option>';
			}
		}
		$html .= '
					</select>
				</div>';
	}
	$html .= '
		</div>
	';
	return $html;
}

function set_order_statuslog($log_data, $data) {
	$state_fields = array('status', 'paystatus', 'goods_status');
	if (!empty($log_data['orderid']) && !empty($log_data['storeid']) && !empty($log_data['goodsid'])) {
		$state_fields[] = 'id';
		$state_fields[] = 'hotelid';
		$state_fields[] = 'roomid';
		$order_info = pdo_get('storex_order', array('id' => $log_data['orderid']), $state_fields);
		foreach ($state_fields as $field) {
			if (!empty($data[$field])) {
				$log_data['orderid'] = $order_info['id'];
				$log_data['storeid'] = $order_info['hotelid'];
				$log_data['goodsid'] = $order_info['roomid'];
				
				$log_data['old_state'] = $order_info[$field];
				$log_data['new_state'] = $data[$field];
				$log_data['state_type'] = $field;
				$log_data['time'] = TIMESTAMP;
				pdo_insert('storex_order_statuslog', $log_data);
			}
		}
	}
}
//获取店铺下的所有商品标签
function store_goods_tags($storeid) {
	global $_W;
	return pdo_getall('storex_tags', array('storeid' => $storeid, 'uniacid' => $_W['uniacid'], 'status' => 1), array(), 'id');
}
//获取商品标签
function get_goods_tag($tags, $tagid) {
	$tag = '';
	if (!empty($tags[$tagid])) {
		$tag = $tags[$tagid]['title'];
	}
	return $tag;
}
//获取商品自定义字段值
function get_goods_defined($storeid, $goodsid, $is_mobile = false) {
	$defined = array();
	$goods_extend = pdo_get('storex_goods_extend', array('storeid' => $storeid, 'goodsid' => $goodsid));
	if (!empty($goods_extend) && !empty($goods_extend['defined'])) {
		$defined = iunserializer($goods_extend['defined']);
	}
	if (!empty($is_mobile)) {
		$goods = pdo_get('storex_goods', array('store_base_id' => $storeid, 'id' => $goodsid), array('unit', 'weight'));
		if (!empty($defined) && !empty($goods) && is_array($goods)) {
			foreach ($goods as $title => $content) {
				if ($title == 'unit') {
					$defined[] = array(
						'title' => '单位',
						'content' => $content,
					);
				}
				if ($title == 'weight') {
					$defined[] = array(
						'title' => '重量',
						'content' => $content . 'kg',
					);
				}
			}
		}
	}
	return $defined;
}

function check_new_user($storeid) {
	global $_W;
	$order = pdo_get('storex_order', array('hotelid' => $storeid, 'openid' => $_W['openid'], 'newuser' => 1), array('id', 'newuser', 'openid'));
	if (!empty($order)) {
		return false;
	}
	return true;
}

function get_store_market($storeid) {
	$condition = array(
		'storeid' => $storeid,
		'starttime <=' => TIMESTAMP,
		'endtime >' => TIMESTAMP,
		'status' => 1,
	);
	$status = check_new_user($storeid);
	if (empty($status)) {
		$condition['type !='] = 'new';
	}
	$storex_market = pdo_getall('storex_market', $condition, array('storeid', 'type', 'items'), 'type');
	$markets = array();
	$types = array('pickup', 'new', 'cut', 'gift');
	foreach ($types as $type) {
		if (!empty($storex_market[$type])) {
			if ($type == 'new') {
				$markets[$type] = $storex_market[$type];
			} else {
				$storex_market[$type]['items'] = iunserializer($storex_market[$type]['items']);
				$markets[$type] = $storex_market[$type];
			}
		}
	}
	return $markets;
}

function check_room_assign($order, $roomids, $insert = false) {
	global $_W;
	$date= array();
	if (empty($roomids)) {
		return false;
	}
	$roomassign_record = pdo_getall('storex_room_assign', array('storeid' => $order['hotelid'], 'roomid' => $order['roomid'], 'roomitemid' => $roomids, 'time >=' => $order['btime'], 'time <' => $order['etime']));
	$status = true;
	if (!empty($roomassign_record)) {
		return false;
	}
	if (!empty($insert) && !empty($status)) {
		if ($order['day'] > 0 && !empty($roomids)) {
			foreach ($roomids as $roomid) {
				for ($i = 0; $i < $order['day']; $i ++) {
					$insert_data = array(
						'uniacid' => $_W['uniacid'],
						'storeid' => $order['hotelid'],
						'roomid' => $order['roomid'],
						'roomitemid' => $roomid,
						'time' => $order['btime'] + $i * 86400,
					);
					pdo_insert('storex_room_assign', $insert_data);
				}
			}
		}
	}
	return $status;
}

function delete_room_assign($order, $roomitemid = '') {
	if (!empty($order['roomitemid'])) {
		$roomitemid = explode(',', $order['roomitemid']);
	}
	if (!empty($roomitemid)) {
		pdo_delete('storex_room_assign', array('storeid' => $order['hotelid'], 'roomid' => $order['roomid'], 'roomitemid' => $roomitemid, 'time >=' => $order['btime'], 'time <' => $order['etime']));
	}
}

function format_package_goods($store_id, $goodsid) {
	$package_info = pdo_get('storex_sales_package', array('id' => $goodsid, 'storeid' => $store_id));
	$goods_info = array();
	if (!empty($package_info)) {
		$package_info['cprice'] = $package_info['oprice'] = $package_info['price'];
		$package_info['can_buy'] = 1;
		$package_info['score'] = 0;
		$package_info['sold_num'] = 0;
		$package_info['store_type'] = 0;
		$package_info['stock'] = -1;
		$package_info['stock_control'] = 1;
		$package_info['min_buy'] = 1;
		$package_info['max_buy'] = -1;
		$package_info['express_set'] = iserializer(array(
			'express' => $package_info['express'],
			'full_free' => 0
		));
		$goods_info = $package_info;
	}
	return $goods_info;
}

function get_credit_replace($storeid, $uid = '') {
	$store_info = get_store_info($storeid);
	$store_set = get_storex_set();
	$credit_replace = array(
		'credit_pay' => $store_set['credit_pay'],//是否开启积分抵扣
		'credit_ratio' => $store_set['credit_ratio'],
		'max_replace' => $store_info['max_replace'],
		'cost_credit' => sprintf('%.2f', $store_set['credit_ratio'] * $store_info['max_replace']),
		'credit1' => 0,
	);
	if (!empty($uid)) {
		load()->model('mc');
		$credit = mc_credit_fetch($uid);
		$credit_replace['credit1'] = $credit['credit1'];
		if ($credit_replace['cost_credit'] > $credit_replace['credit1']) {
			$credit_replace['credit_pay'] = 2;
		}
	}
	return $credit_replace;
}

function get_code_info() {
	global $_W;
	$code = random(6, 1);
	$code_info = array(
		'weid' => $_W['uniacid'],
		'openid' => $_W['openid'],
		'code' => $code,
		'status' => 1,
		'createtime' => TIMESTAMP,
	);
	return $code_info;
}

function get_member_mode() {
	global $_W;
	$member = pdo_get('storex_member', array('weid' => $_W['uniacid'], 'from_user' => $_W['openid']), array('phone', 'email'));
	$memberinfo = array();
	if (!empty($member) && !empty($member)) {
		foreach ($member as $k => $v) {
			if (!empty($v)) {
				$memberinfo[$k] = $v;
				$memberinfo['type'] = $k;
			}
		}
	}
	return $memberinfo;
}