<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');
mload()->model('card');
mload()->model('order');

$ops = array('display', 'edit', 'delete', 'deleteall', 'edit_msg','edit_price', 'print_order', 'check_print_plugin');
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
		$condition .= " AND o.hotelid = " . $storeid;
	}
	if (!empty($roomid)) {
		$condition .= " AND o.roomid = " . $roomid;
	}
	if (!empty($_GPC['status'])) {
		if ($_GPC['status'] == 4) {
			$condition .= " AND o.status = 0";
		} else {
			$condition .= " AND o.status =" . intval($_GPC['status']);
		}
	}
	$paystatus = $_GPC['paystatus'];
	if (!empty($_GPC['paystatus'])) {
		if ($_GPC['paystatus'] == 2) {
			$condition .= " and o.paystatus = 0";
		} else {
			$condition .= " and o.paystatus = " . intval($_GPC['paystatus']);
		}
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
	if (!empty($_GPC['export'])) {
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
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		$item = pdo_get('storex_order', array('id' => $id));
		if (empty($item)) {
			message(error(-1, '抱歉，订单不存在或是已经删除！'), '', 'ajax');
		}
		$is_house = 2;
		if ($store_type == STORE_TYPE_HOTEL) {
			$good_info = pdo_get('storex_room', array('hotelid' => $storeid, 'id' => $item['roomid']), array('id', 'is_house'));
			$is_house = $good_info['is_house'];
		}
		$paylog = pdo_get('core_paylog', array('uniacid' => $item['weid'], 'tid' => $item['id'], 'module' => 'wn_storex'), array('uniacid', 'uniontid', 'tid'));
		if (!empty($paylog)) {
			$item['uniontid'] = $paylog['uniontid'];
		}
		$refund_logs = pdo_get('storex_refund_logs', array('uniacid' => $_W['uniacid'], 'orderid' => $item['id']), array('id', 'status'));
		$actions = getOrderAction($item, $store_type, $is_house);
		getOrderpaytext($item);
	}
	$express = express_name();
	if ($_W['isajax'] && $_W['ispost']) {
		$setting = pdo_get('storex_set', array('weid' => $_W['uniacid']));
		$all_actions = array('cancel', 'refund', 'refuse', 'confirm', 'send', 'live', 'over');
		$data = array(
			'mngtime' => TIMESTAMP,
		);
		$action = $_GPC['action'];
		if (in_array($action, $all_actions) && !empty($actions) && !empty($actions[$action])) {
			if ($action == 'cancel') {
				$data['status'] = ORDER_STATUS_CANCEL;
			} elseif ($action == 'refund') {
				if ($item['paytype'] == 'credit') {
					mload()->model('order');
					$result = order_begin_refund($item['id']);
					if (is_error($result)) {
						message($result, '', 'ajax');
					} else {
						message(error(0, '退款成功'), '', 'ajax');
					}
				} elseif ($item['paytype'] == 'wechat') {
					$this->refund(array('module' => 'wn_storex', 'tid' => $item['id']));
				}
			} elseif ($action == 'refuse') {
				$data['status'] = ORDER_STATUS_REFUSE;
			} elseif ($action == 'confirm') {
				$data['status'] = ORDER_STATUS_SURE;
			} elseif ($action == 'send') {
				$data['goods_status'] = GOODS_STATUS_SHIPPED;
			} elseif ($action == 'live') {
				$data['goods_status'] = GOODS_STATUS_CHECKED;
			} elseif ($action == 'over') {
				$data['status'] = ORDER_STATUS_OVER;
			}
			if ($store_type == STORE_TYPE_HOTEL) {
				//订单取消和拒绝
				if ($data['status'] == ORDER_STATUS_CANCEL || $data['status'] == ORDER_STATUS_REFUSE) {
					if ($item['paystatus'] == PAY_STATUS_PAID) {
						$data['refund_status'] = REFUND_STATUS_PROCESS;
					}
					$room_date_list = pdo_getall('storex_room_price', array('roomid' => $item['roomid'], 'roomdate >=' => $item['btime'], 'roomdate <' => $item['etime'], 'status' => 1), array('id', 'roomdate', 'num', 'status'));
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
			$infos = array();
			$infos['room'] = $room['title'];
			$infos['store'] = $store['title'];
			$infos['store_type'] = $store['store_type'];
			$infos['template'] = $setting['template'];
			if (!empty($data['status'])) {
				//订单拒绝
				if ($data['status'] == ORDER_STATUS_REFUSE) {
					$infos['refuse_templateid'] = $setting['refuse_templateid'];
					order_refuse_notice($item, $infos);
				}
				//订单确认提醒   TM00217
				if ($data['status'] == ORDER_STATUS_SURE) {
					if ($store_type == STORE_TYPE_HOTEL) {
						if (!empty($good_info) && $is_house == 1) {
							$data['goods_status'] = GOODS_STATUS_NOT_CHECKED;
						}
					} else {
						$data['goods_status'] = GOODS_STATUS_NOT_SHIPPED;
					}
					$infos['templateid'] = $setting['templateid'];
					order_sure_notice($item, $infos);
					
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
			
				//订单完成提醒   OPENTM203173461
				if ($data['status'] == ORDER_STATUS_OVER) {
					$uid = mc_openid2uid(trim($item['openid']));
					//订单完成后增加积分
					card_give_credit($uid, $item['sum_price']);
					//增加出售货物的数量
					add_sold_num($room);
					$infos['finish_templateid'] = $setting['finish_templateid'];
					order_over_notice($item, $infos);
					
					mload()->model('sales');
					sales_update(array('storeid' => $item['hotelid'], 'sum_price' => $item['sum_price']));
				}
				if ($data['status'] == ORDER_STATUS_CANCEL) {
					$info = '您在' . $store['title'] . '预订的' . $room['title'] . "订单已取消，请联系管理员！";
					$status = send_custom_notice('text', array('content' => urlencode($info)), $item['openid']);
				}
			}
			
			if (!empty($data['goods_status'])) {
				$infos['phone'] = $store['phone'];
				//已入住提醒   TM00058
				if ($data['goods_status'] == GOODS_STATUS_CHECKED) {
					$infos['check_in_templateid'] = $setting['check_in_templateid'];
					order_checked_notice($item, $infos);
				}
				if ($data['goods_status'] == GOODS_STATUS_SHIPPED) {
					$info = '您在' . $store['title'] . '预订的' . $room['title'] . '已发货';
					$status = send_custom_notice('text', array('content' => urlencode($info)), $item['openid']);
				}
			}
			//卡券状态修改
			if (!empty($item['coupon'])) {
				if ($data['status'] == ORDER_STATUS_CANCEL || $data['status'] == ORDER_STATUS_REFUSE) {
					pdo_update('storex_coupon_record', array('status' => 1), array('id' => $item['coupon']));
				} elseif ($data['status'] == ORDER_STATUS_OVER) {
					pdo_update('storex_coupon_record', array('status' => 3), array('id' => $item['coupon']));
				}
			}
			pdo_update('storex_order', $data, array('id' => $id));
			message(error('0', '订单信息处理完成！'), '', 'ajax');
		} else {
			message(error('-1', '订单操作错误！'), '', 'ajax');
		}
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
		message('抱歉，订单不存在或是已经删除！', referer(), 'error');
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

if ($op == 'edit_msg') {
	$data = array(
		'msg' => trim($_GPC['msg']),
		'track_number' => trim($_GPC['track_number']),
		'express_name' => trim($_GPC['express_name']),
	);
	$order = pdo_get('storex_order', array('id' => intval($_GPC['id'])), array('id'));
	if (empty($order)) {
		message('抱歉，订单不存在或是已经删除！', referer(), 'error');
	}
	$result = pdo_update('storex_order', $data, array('id' => intval($_GPC['id'])));
	if (!empty($result)) {
		message('备注修改成功！', referer(), 'success');
	} else {
		message('备注修改失败！', referer(), 'error');
	}
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