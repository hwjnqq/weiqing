<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');
mload()->model('card');

$ops = array('display', 'edit', 'delete', 'deleteall', 'edit_price', 'print_order', 'check_print_plugin');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_GPC['storeid']);
$store = $_W['wn_storex']['store_info'];
$store_type = $store['store_type'];
$table = gettablebytype($store_type);

$roomid = intval($_GPC['roomid']);
if (!empty($roomid)) {
	$room = pdo_get($table, array('id' => $roomid), array('id', 'title', 'sold_num'));
}

if ($op == 'display') {
	$search_name = $_GPC['search_name'];
	$keyword = trim($_GPC['keyword']);
	$condition = '';
	$params = array();
	if (!empty($search_name) && !empty($keyword)) {
		if ($search_name == 'roomtitle') {
			$condition .= ' AND r.title LIKE :roomtitle';
			$params[':roomtitle'] = "%{$keyword}%";
		} elseif ($search_name == 'realname') {
			$condition .= ' AND o.contact_name LIKE :realname';
			$params[':realname'] = "%{$keyword}%";
		} elseif ($search_name == 'mobile') {
			$condition .= ' AND o.mobile LIKE :mobile';
			$params[':mobile'] = "%{$keyword}%";
		} elseif ($search_name == 'ordersn') {
			$condition .= ' AND o.ordersn LIKE :ordersn';
			$params[':ordersn'] = "%{$keyword}%";
		}
	}
	if (!empty($storeid)) {
		$condition .= " AND o.hotelid=" . $storeid;
	}
	if (!empty($roomid)) {
		$condition .= " AND o.roomid=" . $roomid;
	}
	$status = $_GPC['status'];
	if ($status != '') {
		$condition .= " AND o.status=" . intval($status);
	}
	$paystatus = $_GPC['paystatus'];
	if ($paystatus != '') {
		$condition .= " and o.paystatus=" . intval($paystatus);
	}
	$date = $_GPC['date'];
	if (!empty($date)) {
		$start = strtotime($date['start']);
		if ($date['start'] == $date['end'] || $date['end'] == date('Y-m-d', TIMESTAMP)) {
			$end = strtotime($date['end']) + 86399;
		} else {
			$end = strtotime($date['end']);
		}
		$condition .= " AND o.time > " . $start . " AND o.time < " . $end;
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	pdo_query('UPDATE ' . tablename('storex_order') . " SET status = -1 WHERE time < :time AND weid = '{$_W['uniacid']}' AND paystatus = 0 AND status <> 1 AND status <> 3", array(':time' => time() - 86400));
	$field = '';
	if ($table == 'storex_room') {
		$field = ' , r.is_house ';
	}
	$show_order_lists = pdo_fetchall("SELECT o.*, h.title AS hoteltitle, r.title AS roomtitle, r.thumb " . $field . " FROM " . tablename('storex_order') . " AS o LEFT JOIN " . tablename('storex_bases') . " h ON o.hotelid = h.id LEFT JOIN " . tablename($table) . " AS r ON r.id = o.roomid WHERE o.weid = '{$_W['uniacid']}' $condition ORDER BY o.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
	getOrderUniontid($show_order_lists);
	$version = check_ims_version();
	if (!empty($version)) {
		$printers = store_printers($storeid);
		if (!empty($printers) && is_array($printers)) {
			foreach ($printers as $k => $print) {
				if ($print['disabled'] == 2) {
					if ($print['status'] != 2) {
						unset($printers[$k]);
					}
				} elseif ($print['disabled'] == 1) {
					unset($printers[$k]);
				}
			}
		}
	}

	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_order') . " AS o LEFT JOIN " . tablename('storex_bases') . " AS h on o.hotelid = h.id LEFT JOIN " . tablename($table) . " AS r on r.id = o.roomid  WHERE o.weid = '{$_W['uniacid']}' $condition", $params);
	if ($_GPC['export'] != '') {
		$export_order_lists = pdo_fetchall("SELECT o.*, h.title as hoteltitle, r.title AS roomtitle FROM " . tablename('storex_order') . " o LEFT JOIN " . tablename('storex_bases') . " AS h on o.hotelid = h.id LEFT JOIN " . tablename($table) . " AS r ON r.id = o.roomid  WHERE o.weid = '{$_W['uniacid']}' $condition ORDER BY o.id DESC" . ',' . $psize, $params);
		getOrderUniontid($export_order_lists);
		/* 输入到CSV文件 */
		$html = "\xEF\xBB\xBF";
		/* 输出表头 */
		$filter = array(
			'ordersn' => '订单号',
			'uniontid' => '商户订单号',
			'hoteltitle' => '酒店',
			'roomtitle' => '房型',
			'name' => '预订人',
			'mobile' => '手机',
			'nums' => '预订数量',
			'sum_price' => '总价',
			'paytype' => '支付方式',
			'time' => '订单生成时间',
			'paystatus' => '订单状态'
		);
		if ($store_type == STORE_TYPE_HOTEL) {
			$filter['btime'] = '到店时间';
			$filter['etime'] = '离店时间';
		}
		foreach ($filter as $key => $title) {
			$html .= $title . "\t,";
		}
		$html .= "\n";
		foreach ($export_order_lists as $k => $v) {
			foreach ($filter as $key => $title) {
				if ($key == 'time') {
					$html .= date('Y-m-d H:i:s', $v[$key]) . "\t, ";
				} elseif ($key == 'btime') {
					$html .= date('Y-m-d', $v[$key]) . "\t, ";
				} elseif ($key == 'etime') {
					$html .= date('Y-m-d', $v[$key]) . "\t, ";
				} elseif ($key == 'paytype') {
					$html .= $v['paytype_text'] . "\t, ";
				} elseif ($key == 'paystatus') {
					$html .= $v['status_text'] . "\t, ";
				} else {
					$html .= $v[$key] . "\t, ";
				}
			}
			$html .= "\n";
		}
		/* 输出CSV文件 */
		header("Content-type:text/csv");
		header("Content-Disposition:attachment; filename=全部数据.csv");
		echo $html;
		exit();
	}
	$pager = pagination($total, $pindex, $psize);
	include $this->template('store/shop_orderlist');
}

if ($op == 'edit') {
	$id = $_GPC['id'];
	if (!empty($id)) {
		$item = pdo_get('storex_order', array('id' => $id));
		$paylog = pdo_get('core_paylog', array('uniacid' => $item['weid'], 'tid' => $item['id'], 'module' => 'wn_storex'), array('uniacid', 'uniontid', 'tid'));
		if (empty($item)) {
			message('抱歉，订单不存在或是已经删除！', '', 'error');
		}
		if ($store_type == STORE_TYPE_HOTEL) {
			$good_info = pdo_get('storex_room', array('hotelid' => $storeid, 'id' => $item['roomid']), array('id', 'is_house'));
		}
		if (!empty($paylog)) {
			$item['uniontid'] = $paylog['uniontid'];
		}
		getOrderpaytext($item);
	}
	$express = express_name();
	if (checksubmit('submit')) {
		$setting = pdo_get('storex_set', array('weid' => $_W['uniacid']));
		$status = intval($_GPC['status']);
		if ($item['status'] == $status) {
			message('订单状态已经是该状态了，请勿重复操作！', '', 'error');
		}
		
		$data = array(
			'msg' => $_GPC['msg'],
			'mngtime' => TIMESTAMP,
			'track_number' => trim($_GPC['track_number']),
			'express_name' => trim($_GPC['express_name']),
		);
		if ($status > 4 && 8 > $status) {
			if ($status == 5 && $data['goods_status'] != GOODS_STATUS_CHECKED) {
				$data['goods_status'] = GOODS_STATUS_CHECKED;
			} elseif ($status == 6 && $data['goods_status'] != GOODS_STATUS_SHIPPED) {
				$data['goods_status'] = GOODS_STATUS_SHIPPED;
			} elseif ($status == 7 && $data['goods_status'] != GOODS_STATUS_RECEIVED) {
				$data['goods_status'] = GOODS_STATUS_RECEIVED;
			} else {
				message('商品状态已经是该状态了！', '', 'error');
			}
		} elseif ($status == 8) {
			if (($item['status'] == ORDER_STATUS_CANCEL || $item['status'] == ORDER_STATUS_SURE) && $item['paystatus'] == PAY_STATUS_PAID && $item['refund_status'] != REFUND_STATUS_SUCCESS) {
				$data['refund_status'] = REFUND_STATUS_PROCESS;
			} else {
				message('该订单不符合退款要求！', '', 'error');
			}
		} else {
			$data['status'] = $status;
		}
		if ($item['status'] == ORDER_STATUS_SURE || $item['goods_status'] == GOODS_STATUS_CHECKED || $item['goods_status'] == GOODS_STATUS_SHIPPED || $item['goods_status'] == GOODS_STATUS_RECEIVED ) {
			if (!empty($data['status']) && ($data['status'] == ORDER_STATUS_CANCEL || $data['status'] == ORDER_STATUS_REFUSE)) {
				message('订单已确认或发货，不能操作！', '', 'error');
			}
		}
		if ($item['status'] == ORDER_STATUS_CANCEL) {
			message('订单状态已经取消，不能操作！', '', 'error');
		}
		if ($item['status'] == ORDER_STATUS_OVER) {
			message('订单状态已经完成，不能操作！', '', 'error');
		}
		
// 		if (!empty($data['refund_status']) && $data['refund_status'] == 1) {
// 			//处理退款的逻辑，
// 			$message = array();
// 			if (is_error($message)) {
// 				$data['refund_status'] = 3;
// 				pdo_update('storex_order', $data, array('id' => $id));
// 				message('该订单退款失败！', '', 'error');
// 			} else {
// 				$data['refund_status'] = 2;
// 				pdo_update('storex_order', $data, array('id' => $id));
// 			}
// 		}
		
		if ($store_type == STORE_TYPE_HOTEL) {
			//订单取消
			if ($data['status'] == ORDER_STATUS_CANCEL || $data['status'] == ORDER_STATUS_REFUSE) {
				if ($item['paystatus'] == PAY_STATUS_PAID) {
					$data['refund_status'] = REFUND_STATUS_PROCESS;
				}
				$room_date_list = pdo_getall('storex_room_price', array('roomid' => $item['roomid'], 'roomdate >=' => $item['btime'], 'roomdate <' => $item['etime'], 'status' => 1), 
					array('id', 'roomdate', 'num', 'status'));
				if (!empty($room_date_list)) {
					foreach ($room_date_list as $key => $value) {
						$num = $value['num'];
						if ($num >= 0) {
							$now_num = $num + $item['nums'];
							pdo_update('storex_room_price', array('num' => $now_num), array('id' => $value['id']));
						}
					}
				}
			}
		}
		
		if (!empty($data['status']) && $data['status'] != $item['status']) {
			//订单退款
			if ($data['status'] == ORDER_STATUS_REFUSE) {
				if (!empty($setting['template']) && !empty($setting['refuse_templateid'])) {
					$tplnotice = array(
						'first' => array('value'=>'尊敬的宾客，非常抱歉的通知您，您的预订订单被拒绝。'),
						'keyword1' => array('value' => $item['ordersn']),
						'keyword2' => array('value' => date('Y.m.d', $item['btime']). '-'. date('Y.m.d', $item['etime'])),
						'keyword3' => array('value' => $item['nums']),
						'keyword4' => array('value' => $item['sum_price']),
						'keyword5' => array('value' => '商品不足'),
					);
					$acc = WeAccount::create();
					$acc->sendTplNotice($item['openid'], $setting['refuse_templateid'], $tplnotice);
				} else {
					$info = '您在'.$store['title'].'预订的'.$room['title']."不足。已为您取消订单";
					$status = send_custom_notice('text', array('content' => urlencode($info)), $item['openid']);
				}
			}
			//订单确认提醒
			if ($data['status'] == ORDER_STATUS_SURE) {
				if ($store_type == STORE_TYPE_HOTEL) {
					if (!empty($good_info) && $good_info['is_house'] == 1) {
						$data['goods_status'] = 4;
					}
				} else {
					$data['goods_status'] = 1;
				}
				//TM00217
				if (!empty($setting['template']) && !empty($setting['templateid'])) {
					$tplnotice = array(
						'first' => array('value' => '您好，您已成功预订' . $store['title'] . '！'),
						'order' => array('value' => $item['ordersn']),
						'Name' => array('value' => $item['contact_name']),
						'datein' => array('value' => date('Y-m-d', $item['btime'])),
						'dateout' => array('value' => date('Y-m-d', $item['etime'])),
						'number' => array('value' => $item['nums']),
						'room type' => array('value' => $item['style']),
						'pay' => array('value' => $item['sum_price']),
						'remark' => array('value' => '酒店预订成功')
					);
					$acc = WeAccount::create();
					$result = $acc->sendTplNotice($item['openid'], $setting['templateid'], $tplnotice);
				} else {
					$info = '您在' . $store['title'] . '预订的' . $room['title'] . '已预订成功';
					$status = send_custom_notice('text', array('content' => urlencode($info)), $item['openid']);
				}
				if (check_ims_version()) {
					$plugins = get_plugin_list();
					if (!empty($plugins) && !empty($plugins['wn_storex_plugin_sms'])) {
						mload()->model('sms');
						$content = array(
							'store' => $store['title'],
							'ordersn' => $item['ordersn'],
							'price' => $item['sum_price'],
						);
						sms_send($item['mobile'], $content, 'user');
					}
				}
			}
	
			//订单完成提醒
			if ($data['status'] == ORDER_STATUS_OVER) {
				$uid = mc_openid2uid(trim($item['openid']));
				//订单完成后增加积分
				card_give_credit($uid, $item['sum_price']);
				//增加出售货物的数量
				add_sold_num($room);
				//OPENTM203173461
				if (!empty($setting['template']) && !empty($setting['finish_templateid']) && $store_type == STORE_TYPE_HOTEL) {
					$tplnotice = array(
						'first' => array('value' =>'您已成功办理离店手续，您本次入住酒店的详情为'),
						'keyword1' => array('value' =>date('Y-m-d', $item['btime'])),
						'keyword2' => array('value' =>date('Y-m-d', $item['etime'])),
						'keyword3' => array('value' =>$item['sum_price']),
						'remark' => array('value' => '欢迎您的下次光临。')
					);
					$acc = WeAccount::create();
					$result = $acc->sendTplNotice($item['openid'], $setting['finish_templateid'], $tplnotice);
				} else {
					$info = '您在'.$store['title'] . '预订的' . $room['title'] . '订单已完成,欢迎下次光临';
					$status = send_custom_notice('text', array('content' => urlencode($info)), $item['openid']);
				}
			}
			if ($data['status'] == ORDER_STATUS_CANCEL) {
				$info = '您在' . $store_info['title'] . '预订的' . $goods_info['title'] . "订单已取消，请联系管理员！";
				$status = send_custom_notice('text', array('content' => urlencode($info)), $item['openid']);
			}
		}
		
		if (!empty($data['goods_status'])) {
			//已入住提醒
			if ($data['goods_status'] == GOODS_STATUS_CHECKED) {
				//TM00058
				if (!empty($setting['template']) && !empty($setting['check_in_templateid'])) {
					$tplnotice = array(
							'first' =>array('value' =>'您好,您已入住' . $store['title'] . $room['title']),
							'hotelName' => array('value' => $store['title']),
							'roomName' => array('value' => $room['title']),
							'date' => array('value' => date('Y-m-d', $item['btime'])),
							'remark' => array('value' => '如有疑问，请咨询' . $store['phone'] . '。'),
					);
					$acc = WeAccount::create();
					$result = $acc->sendTplNotice($item['openid'], $setting['check_in_templateid'], $tplnotice);
				} else {
					$info = '您已成功入住' . $store['title'] . '预订的' . $room['title'];
					$status = send_custom_notice('text', array('content' => urlencode($info)), $item['openid']);
				}
			}
			
			if ($data['goods_status'] == GOODS_STATUS_SHIPPED) {
				$info = '您在' . $store['title'] . '预订的' . $room['title'] . '已发货';
				$status = send_custom_notice('text', array('content' => urlencode($info)), $item['openid']);
			}
			if ($data['goods_status'] == GOODS_STATUS_RECEIVED) {
				$info = '您在' . $store['title'] . '预订的' . $room['title'] . '管理员已代替操作收货，如有疑问请咨询管理员！';
				$status = send_custom_notice('text', array('content' => urlencode($info)), $item['openid']);
			}
		}
		
		if (!empty($item['coupon'])) {
			if ($data['status'] == ORDER_STATUS_CANCEL || $data['status'] == ORDER_STATUS_REFUSE) {
				pdo_update('storex_coupon_record', array('status' => 1), array('id' => $item['coupon']));
			} elseif ($data['status'] == ORDER_STATUS_OVER) {
				pdo_update('storex_coupon_record', array('status' => 3), array('id' => $item['coupon']));
			}
		}
		pdo_update('storex_order', $data, array('id' => $id));
		message('订单信息处理完成！', $this->createWebUrl('shop_order', array('op' => 'edit', 'id' => $id, 'roomid' => $roomid, 'storeid' => $storeid)), 'success');
	}
	if ($store_type == STORE_TYPE_HOTEL && !empty($good_info) && $good_info['is_house'] == 1) {
		$btime = $item['btime'];
		$etime = $item['etime'];
		$start = date('m-d', $btime);
		$end = date('m-d', $etime);
		//日期列
		$days = ceil(($etime - $btime) / 86400);
		$date_array = array();
		$date_array[0]['date'] = $start;
		$date_array[0]['day'] = date('j', $btime);
		$date_array[0]['time'] = $btime;
		$date_array[0]['month'] = date('m', $btime);
		if ($days > 1) {
			for ($i = 1; $i < $days; $i++) {
				$date_array[$i]['time'] = $date_array[$i - 1]['time'] + 86400;
				$date_array[$i]['date'] = date('Y-m-d', $date_array[$i]['time']);
				$date_array[$i]['day'] = date('j', $date_array[$i]['time']);
				$date_array[$i]['month'] = date('m', $date_array[$i]['time']);
			}
		}
		$room_date_list = pdo_getall('storex_room_price', array('roomid' => $item['roomid'], 'roomdate >=' => $item['btime'], 'roomdate <' => $item['etime'], 'status' => 1), array('id', 'roomdate', 'num', 'status'));
		$flag = 0;
		if (!empty($room_date_list)) {
			$flag = 1;
		}
		$list = array();
		if ($flag == 1) {
			for ($i = 0; $i < $days; $i++) {
				$k = $date_array[$i]['time'];
				foreach ($room_date_list as $p_key => $p_value) {
					//判断价格表中是否有当天的数据
					if ($p_value['roomdate'] == $k) {
						$list[$k]['status'] = $p_value['status'];
						if (empty($p_value['num'])) {
							$list[$k]['num'] = 0;
						} elseif ($p_value['num'] == -1) {
							$list[$k]['num'] = '不限';
						} else {
							$list[$k]['num'] = $p_value['num'];
						}
						$list[$k]['has'] = 1;
						break;
					}
				}
				//价格表中没有当天数据
				if (empty($list[$k])) {
					$list[$k]['num'] = '不限';
					$list[$k]['status'] = 1;
				}
			}
		} else {
			//价格表中没有数据
			for ($i = 0; $i < $days; $i++) {
				$k = $date_array[$i]['time'];
				$list[$k]['num'] = "不限";
				$list[$k]['status'] = 1;
			}
		}
	}
	$member_info = pdo_get('storex_member', array('id' => $item['memberid']), array('from_user', 'isauto'));
	include $this->template('store/shop_orderedit');
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	$item = pdo_get('storex_order', array('id' => $id), array('id'));
	if (empty($item)) {
		message('抱歉，订单不存在或是已经删除！', '', 'error');
	}
	pdo_delete('storex_order', array('id' => $id));
	message('删除成功！', referer(), 'success');
}

if ($op == 'deleteall') {
	foreach ($_GPC['idArr'] as $k => $id) {
		$id = intval($id);
		pdo_delete('storex_order', array('id' => $id));
	}
	message(error(0, '删除成功！'), '', 'ajax');
}

if ($op == 'edit_price') {
	$order_id = intval($_GPC['id']);
	$sum_price = $_GPC['sum_price'];
	if (!is_numeric($sum_price)) {
		message(error(-1, '价格必须是数字！'), '', 'ajax');
	}
	$sum_price = sprintf('%1.2f', $sum_price);
	if ($sum_price <= 0) {
		message(error(-1, '价格保留两位小数后不能小于零！'), '', 'ajax');
	}
	$status = array('0', '1');
	$order_info = pdo_get('storex_order', array('weid' => $_W['uniacid'], 'id' => $order_id, 'paystatus' => 0, 'status' => $status), array('id', 'sum_price'));
	if (empty($order_info)) {
		message(error(-1, '抱歉，订单不是未支付状态或者订单已取消！'), '', 'ajax');
	}
	$core_paylog = pdo_get('core_paylog', array('tid' => $order_info['id'], 'module' => $_GPC['m'], 'uniacid' => $_W['uniacid']));
	$core_result = true;
	if (!empty($core_paylog)) {
		$core_result = pdo_update('core_paylog', array('fee' => $sum_price, 'card_fee' => $sum_price), array('plid' => $core_paylog['plid']));
	}
	$result = pdo_update('storex_order', array('sum_price' => $sum_price), array('id' => $order_info['id']));
	if (!empty($core_result) && !empty($result)) {
		message(error(0, '修改成功！'), '', 'ajax');
	} else {
		message(error(-1, '修改失败！'), '', 'ajax');
	}
}

if ($op == 'print_order') {
	$order_id = intval($_GPC['id']);
	$storeid = intval($_GPC['storeid']);
	$print = intval($_GPC['print']);
	if (empty($order_id)) {
		message('获取订单失败!', referer(), 'error');
	}
	mload()->model('print');
	$result = print_order($print, $order_id, $storeid);
	if ($result['errno'] != 0) {
		message($result['message'], referer(), 'error');
	} else {
		message('打印成功！', referer(), 'success');
	}
}

if ($op == 'check_print_plugin') {
	$plugins = array();
	if (check_ims_version()) {
		$plugins = get_plugin_list();
		if (!empty($plugins) && !empty($plugins['wn_storex_plugin_printer'])) {
			$url = wurl('site/entry/printerset', array('op' => 'display', 'm'=> 'wn_storex_plugin_printer'), true);
			header("Location: {$url}");
			exit;
		} else {
			message('该店铺未安装或设置打印机插件！', referer(), 'error');
		}
	} else {
		message('该微擎版本不支持打印机插件！', referer(), 'error');
	}
}