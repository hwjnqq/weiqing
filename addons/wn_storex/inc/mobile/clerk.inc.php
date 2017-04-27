<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');
mload()->model('card');

$ops = array('clerkindex', 'order', 'room');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

check_params();

if ($op == 'clerkindex') {
	$id = intval($_GPC['id']);
	$clerk_info = get_clerk_permission($id);
	message(error(0, $clerk_info), '', 'ajax');
}
if ($op == 'order') {
	$id = intval($_GPC['id']);
	$clerk_info = get_clerk_permission($id);
	check_clerk_permission($clerk_info, 'wn_storex_permission_'.$op);
	$store_info = get_store_info($id);
	$table = get_goods_table($store_info['store_type']);
	$ac = $_GPC['ac'];
	if ($ac == 'list' || $ac == '') {
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		pdo_query('UPDATE ' . tablename('storex_order') . " SET status = '-1' WHERE time <  :time AND weid = '{$_W['uniacid']}' AND paystatus = '0' AND status <> '1' AND status <> '3'", array(':time' => time() - 86400));
		$list = pdo_fetchall("SELECT o.*,h.title as hoteltitle,r.title as roomtitle FROM " . tablename('hotel2_order') . " o left join " . tablename('hotel2') .
				"h on o.hotelid=h.id left join " . tablename($table) . " r on r.id = o.roomid  WHERE o.weid = '{$_W['uniacid']}' $condition ORDER BY o.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM  ' . tablename('hotel2_order') . " o left join " . tablename('hotel2') .
				"h on o.hotelid=h.id left join " . tablename($table) . " r on r.id = o.roomid  WHERE o.weid = '{$_W['uniacid']}' $condition", $params);
		$page_array = get_page_array($total, $pindex, $psize);
		$page_array['lists'] = $list;
		message(error(0, $page_array), '', 'ajax');
	} elseif($ac == 'info') {
		$orderid = $_GPC['orderid'];
		if (!empty($orderid)) {
			$item = pdo_get('storex_order', array('id' => $orderid));
			if (!empty($item)) {
				$status = array();
				if ($item['status'] == -1 || $item['status'] == 3 || $item['status'] == 2) {
					$status = array();
				} elseif($item['status'] == 1) {
					if ($store_info['store_type'] == 1) {
						$status['status']['4'] = "已入住";
					} else {
						if ($item['mode_distribute'] == 2) {//配送
							if ($item['goods_status'] == 1 || empty($item['goods_status'])) {
								$status['goods_status']['2'] = '已发货';
							}
						}
					}
					$status['status']['3'] = "已完成";
				} elseif($item['status'] == 4){
					$status['status'] = '已完成';
				}
				else {
					$status['status']['-1'] = '取消订单';
					$status['status']['1'] = '确认订单';
					$status['status']['2'] = '拒绝订单';
				}
				$item['ac'] = $status;
				message(error(0, $item), '', 'ajax');
			}
		}
		message(error(-1, '抱歉，订单不存在或是已经删除！'), '', 'ajax');
	} elseif ($ac == 'edit') {
		$orderid = $_GPC['orderid'];
		if (empty($orderid)) {
			message(error(-1, '参数错误！'), '', 'ajax');
		}
		$item = pdo_get('storex_order', array('id' => $orderid));
		if (empty($item)) {
			message(error(-1, '抱歉，订单不存在或是已经删除'), '', 'ajax');
		}
		$goodsid = intval($item['roomid']);
		$goods_info = pdo_get($table, array('id' => $goodsid), array('id', 'title'));
		$setting = pdo_get('storex_set', array('weid' => $_W['uniacid']));
		$data = array(
			'status' => intval($_GPC['status']),
			'msg' => $_GPC['msg'],
			'goods_status' => intval($_GPC['goods_status']),
		);
		if ($item['status'] == -1) {
			message(error(-1, '订单状态已经取消，不能操做！'), '', 'ajax');
		}
		if ($item['status'] == 3) {
			message(error(-1, '订单状态已经完成，不能操做！'), '', 'ajax');
		}
		if ($item['status'] == 2) {
			message(error(-1, '订单状态已拒绝，不能操做！'), '', 'ajax');
		}
		if ($data['status'] == $item['status']){
			message(error(-1, '订单状态已经是该状态了，不要重复操作！'), '', 'ajax');
		}
		if (!empty($data['goods_status']) && $data['goods_status'] == 2 && $item['status'] != 1) {
			message(error(-1, '订单不能发货！'), '', 'ajax');
		} else {
			$data['status'] = '';
		}
		//订单取消
		if ($data['status'] == -1 || $data['status'] == 2) {
			if ($store_info['store_type'] == 1) {
				$params = array();
				$sql = "SELECT id, roomdate, num FROM " . tablename('storex_room_price');
				$sql .= " WHERE 1 = 1";
				$sql .= " AND roomid = :roomid";
				$sql .= " AND roomdate >= :btime AND roomdate < :etime";
				$sql .= " AND status = 1";
				$params[':roomid'] = $item['roomid'];
				$params[':btime'] = $item['btime'];
				$params[':etime'] = $item['etime'];
				$room_date_list = pdo_fetchall($sql, $params);
				if ($room_date_list) {
					foreach ($room_date_list as $key => $value) {
						if ($value['num'] >= 0) {
							$now_num = $value['num'] + $item['nums'];
							pdo_update('storex_room_price', array('num' => $now_num), array('id' => $value['id']));
						}
					}
				}
			}
		}
		if ($data['status'] != $item['status']) {
			//订单退款
			if ($data['status'] == 2) {
				$acc = WeAccount::create();
				$info = '您在'.$store_info['title'].'预订的'.$goods_info['title']."已不足。已为您取消订单";
				$custom = array(
					'msgtype' => 'text',
					'text' => array('content' => urlencode($info)),
					'touser' => $item['openid'],
				);
				if (!empty($setting['template']) && !empty($setting['refuse_templateid'])) {
					$tplnotice = array(
						'first' => array('value'=>'尊敬的宾客，非常抱歉的通知您，您的预订订单被拒绝。'),
						'keyword1' => array('value' => $item['ordersn']),
						'keyword3' => array('value' => $item['nums']),
						'keyword4' => array('value' => $item['sum_price']),
						'keyword5' => array('value' => '商品不足'),
					);
					if ($store_info['store_type'] == 1) {
						$tplnotice['keyword2'] = array('value' => date('Y.m.d', $item['btime']). '-'. date('Y.m.d', $item['etime']));
					}
					$acc->sendTplNotice($item['openid'], $setting['refuse_templateid'], $tplnotice);
				} else {
					$status = $acc->sendCustomNotice($custom);
				}
			}
			//订单确认提醒
			if ($data['status'] == 1) {
				$acc = WeAccount::create();
				$info = '您在'.$store_info['title'].'预订的'.$goods_info['title']."已预订成功";
				$custom = array(
					'msgtype' => 'text',
					'text' => array('content' => urlencode($info)),
					'touser' => $item['openid'],
				);
				//TM00217
				if (!empty($setting['template']) && !empty($setting['templateid'])) {
					$tplnotice = array(
						'first' => array('value' => '您好，您已成功预订'.$store_info['title'].'！'),
						'order' => array('value' => $item['ordersn']),
						'Name' => array('value' => $item['name']),
						'datein' => array('value' => date('Y-m-d', $item['btime'])),
						'dateout' => array('value' => date('Y-m-d', $item['etime'])),
						'number' => array('value' => $item['nums']),
						'room type' => array('value' => $item['style']),
						'pay' => array('value' => $item['sum_price']),
						'remark' => array('value' => '预订成功')
					);
					$result = $acc->sendTplNotice($item['openid'], $setting['templateid'],$tplnotice);
				} else {
					$status = $acc->sendCustomNotice($custom);
				}
			}
			//已入住提醒
			if ($data['status'] == 4) {
				$acc = WeAccount::create();
				$info = '您已成功入住'.$store_info['title'].'预订的'.$goods_info['title'];
				$custom = array(
					'msgtype' => 'text',
					'text' => array('content' => urlencode($info)),
					'touser' => $item['openid'],
				);
				//TM00058
				if (!empty($setting['template']) && !empty($setting['check_in_templateid'])) {
					$tplnotice = array(
						'first' =>array('value' =>'您好,您已入住'.$store_info['title'].$goods_info['title']),
						'hotelName' => array('value' => $store_info['title']),
						'roomName' => array('value' => $goods_info['title']),
						'date' => array('value' => date('Y-m-d', $item['btime'])),
						'remark' => array('value' => '如有疑问，请咨询'.$store_info['phone'].'。'),
					);
					$result = $acc->sendTplNotice($item['openid'], $setting['check_in_templateid'],$tplnotice);
				} else {
					$status = $acc->sendCustomNotice($custom);
				}
			}
	
			//订单完成提醒
			if ($data['status'] == 3) {
				$uid = mc_openid2uid(trim($item['openid']));
				//订单完成后增加积分
				card_give_credit($item['weid'], $uid, $item['sum_price'] ,$item['hotelid']);
				//增加出售货物的数量
				add_sold_num($goods_info);
				$acc = WeAccount::create();
				$info = '您在'.$store_info['title'].'预订的'.$goods_info['title']."订单已完成,欢迎下次光临";
				$custom = array(
					'msgtype' => 'text',
					'text' => array('content' => urlencode($info)),
					'touser' => $item['openid'],
				);
				//OPENTM203173461
				if (!empty($setting['template']) && !empty($setting['finish_templateid']) && $store_info['store_type'] == 1) {
					$tplnotice = array(
						'first' => array('value' =>'您已成功办理离店手续，您本次入住酒店的详情为'),
						'keyword1' => array('value' =>date('Y-m-d', $item['btime'])),
						'keyword2' => array('value' =>date('Y-m-d', $item['etime'])),
						'keyword3' => array('value' =>$item['sum_price']),
						'remark' => array('value' => '欢迎您的下次光临。')
					);
					$result = $acc->sendTplNotice($item['openid'], $setting['finish_templateid'],$tplnotice);
				} else {
					$status = $acc->sendCustomNotice($custom);
				}
			}
			//发货设置
			if ($data['goods_status'] == 2) {
				$data['status'] = 1;
				$acc = WeAccount::create();
				$info = '您在'.$store_info['title'].'预订的'.$goods_info['title']."已发货";
				$custom = array(
						'msgtype' => 'text',
						'text' => array('content' => urlencode($info)),
						'touser' => $item['openid'],
				);
				$status = $acc->sendCustomNotice($custom);
			}
			pdo_update('storex_order', $data, array('id' => $orderid));
			message(error(0, '订单信息处理完成！'), '', 'ajax');
		}
	}
}
if ($op == 'room') {
	$id = intval($_GPC['id']);//酒店id
	$clerk_info = get_clerk_permission($id);
	check_clerk_permission($clerk_info, 'wn_storex_permission_'.$op);
	$store_info = get_store_info($id);
	if ($store_info['store_type'] != 1) {
		message(error(-1, '该店铺没有房型'), '', 'ajax');
	}
	$table = get_goods_table($store_info['store_type']);
	$ac = $_GPC['ac'];
	if ($ac == 'getdate') {
		$type = trim($_GPC['type']);
		if (empty($type) || !in_array($type, array('status','price'))) {
			message(error(-1, '类型错误！'), '', 'ajax');
		}
		$pagesize = 1;
		$page = intval($_GPC['page']);
		$totalpage = 20;
		if ($page > $totalpage) {
			$page = $totalpage;
		} elseif ($page <= 1) {
			$page = 1;
		}
		$currentindex = ($page - 1) * $pagesize;
		$start = date('Y-m-d', strtotime(date('Y-m-d') . "+$currentindex day"));
		$btime = strtotime($start);
		$etime = strtotime(date('Y-m-d', strtotime("$start +$pagesize day")));
		$list = pdo_getall('storex_room', array('hotelid' => $id, 'weid' => intval($_W['uniacid']), 'is_house' => 1));
		$dates = array();
		if ($type == 'status') {
			$dates['date'] = $start;
			$dates['day'] = date('j', $btime);
			$dates['time'] = $btime;
			$dates['month'] = date('m', $btime);
			foreach ($list as $key => $value) {
				$list[$key]['thumb'] = tomedia($value['thumb']);
				$item = pdo_get('storex_room_price', array('roomid' => $value['id'], 'roomdate' => $dates['time'], 'weid' => intval($_W['uniacid'])));
				if (!empty($item)) {
					$flag = 1;
				} else {
					$flag = 0;
				}
				$list[$key]['price_list'] = array();
				if ($flag == 1) {
					$k = $dates['time'];
					//判断价格表中是否有当天的数
					$list[$key]['price_list']['status'] = $item['status']; //有房，没房
					if (empty($item['num'])) {
						$list[$key]['price_list']['num'] = "无房";
						$list[$key]['price_list']['status'] = 0;
					} elseif ($item['num'] == -1) {
						$list[$key]['price_list']['num'] = "不限";
					} else {
						$list[$key]['price_list']['num'] = $item['num'];
					}
					$list[$key]['price_list']['roomid'] = $value['id'];
					$list[$key]['price_list']['hotelid'] = $id;
					//价格表中没有当天数据
					if (empty($list[$key]['price_list'])) {
						$list[$key]['price_list']['num'] = "不限";
						$list[$key]['price_list']['status'] = 1;
						$list[$key]['price_list']['roomid'] = $value['id'];
						$list[$key]['price_list']['hotelid'] = $id;
					}
				} else {
					//价格表中没有数据
					$list[$key]['price_list']['num'] = "不限";
					$list[$key]['price_list']['status'] = 1;
					$list[$key]['price_list']['roomid'] = $value['id'];
					$list[$key]['price_list']['hotelid'] = $id;
				}
			}
		} elseif ($type == 'price') {
			$dates = get_dates($start, $pagesize);
			foreach ($list as $key => $value) {
				$sql = "SELECT * FROM " . tablename('storex_room_price');
				$sql .= " WHERE 1 = 1";
				$sql .= " AND roomid = " . $value['id'];
				$sql .= " AND roomdate >= " . $btime;
				$sql .= " AND roomdate < " . ($etime + 86400);
				$item = pdo_fetchall($sql);
				if ($item) {
					$flag = 1;
				} else {
					$flag = 0;
				}
				$list[$key]['price_list'] = array();
				if ($flag == 1) {
					for ($i = 0; $i < $pagesize; $i++) {
						$k = $dates[$i]['date'];
						foreach ($item as $p_key => $p_value) {
							//判断价格表中是否有当天的数据
							if ($p_value['roomdate'] == $k) {
								$list[$key]['price_list'][$k]['oprice'] = $p_value['oprice'];
								$list[$key]['price_list'][$k]['cprice'] = $p_value['cprice'];
								$list[$key]['price_list'][$k]['roomid'] = $value['id'];
								$list[$key]['price_list'][$k]['hotelid'] = $id;
								break;
							}
						}
						//价格表中没有当天数据
						if (empty($list[$key]['price_list'][$k]['oprice'])) {
							$list[$key]['price_list'][$k]['oprice'] = $value['oprice'];
							$list[$key]['price_list'][$k]['cprice'] = $value['cprice'];
							$list[$key]['price_list'][$k]['roomid'] = $value['id'];
							$list[$key]['price_list'][$k]['hotelid'] = $id;
						}
					}
				} else {
					//价格表中没有数据
					for ($i = 0; $i < $pagesize; $i++) {
						$k = $dates[$i]['date'];
						$list[$key]['price_list'][$k]['oprice'] = $value['oprice'];
						$list[$key]['price_list'][$k]['cprice'] = $value['cprice'];
						$list[$key]['price_list'][$k]['roomid'] = $value['id'];
						$list[$key]['price_list'][$k]['hotelid'] = $id;
					}
				}
			}
		}
		message(error(0, $list), '', 'ajax');
	} elseif ($ac == 'edit_status') {
		$roomid = intval($_GPC['roomid']);
		$num = intval($_GPC['num'])>=0 ? intval($_GPC['num']) : 0;
		$status = empty($_GPC['status']) ? 1 : 0;
		$pricetype = $_GPC['pricetype'];
		$date = empty($_GPC['date']) ? date('Y-m-d') : $_GPC['date'];
		$roomprice = getRoomPrice($id, $roomid, $date);
		if ($pricetype == 'num') {
			$roomprice['num'] = $num;
		} else {
			$roomprice['status'] = $status;
		}
		if (empty($roomprice['id'])) {
			pdo_insert("storex_room_price", $roomprice);
		} else {
			pdo_update("storex_room_price", $roomprice, array("id" => $roomprice['id']));
		}
		message(error(0, '更新房态成功！'), '', 'ajax');
	} elseif ($ac == 'edit_price') {
		$roomid = intval($_GPC['roomid']);
		$price = intval($_GPC['price'])<0 ? 0 : intval($_GPC['price']);
		$pricetype = $_GPC['pricetype'];
		$date = empty($_GPC['date']) ? date('Y-m-d') : $_GPC['date'];
		$roomprice = getRoomPrice($id, $roomid, $date);
		if ($pricetype == 'oprice') {
			$roomprice['oprice'] = $price;
		} else {
			$roomprice['cprice'] = $price;
		}
		if (empty($roomprice['id'])) {
			pdo_insert("storex_room_price", $roomprice);
		} else {
			pdo_update("storex_room_price", $roomprice, array("id" => $roomprice['id']));
		}
		message(error(0, '更新房价成功！'), '', 'ajax');
	} else {
		$data = array(
			'getdate' => array(
				'status',
				'price',
			),
			'edit_status',
			'edit_price',
		);
		message(error(0, $data), '', 'ajax');
	}
}