<?php
/**
 * 订单状态
 * @return array 
 */
function order_status($status) {
	$order_status = array(
		ORDER_STATUS_CANCEL => '订单取消',
		ORDER_STATUS_NOT_SURE => '订单未确认',
		ORDER_STATUS_SURE => '订单已确认',
		ORDER_STATUS_REFUSE => '订单拒绝',
		ORDER_STATUS_OVER => '订单完成'
	);
	return $order_status[$status] ? $order_status[$status] : '未知';
}

/**
 * 订单支付状态
 * @return array 
 */
function order_pay_status($status) {
	$order_pay_status = array(
		PAY_STATUS_UNPAID => '未支付',
		PAY_STATUS_PAID => '已支付'
	);
	return $order_pay_status[$status] ? $order_pay_status[$status] : '未知';
}

/**
 * 订单退款状态
 * @return array 
 */
function order_refund_status($status) {
	$order_refund_status = array(
		REFUND_STATUS_PROCESS => '退款处理中',
		REFUND_STATUS_SUCCESS => '退款成功',
		REFUND_STATUS_FAILED => '退款失败'
	);
	return $order_refund_status[$status] ? $order_refund_status[$status] : '未知';
}

/**
 * 订单商品状态
 * @return array 
 */
function order_goods_status($status) {
	$order_goods_status = array(
		GOODS_STATUS_NOT_SHIPPED => '待发货',
		GOODS_STATUS_SHIPPED => '已发货',
		GOODS_STATUS_RECEIVED => '已收货',
		GOODS_STATUS_NOT_CHECKED => '未入住',
		GOODS_STATUS_CHECKED => '已入住',
	);
	return $order_goods_status[$status] ? $order_goods_status[$status] : '未知';
}


/**
 * action 1预定  2购买
 * 获取订单的状态
 * status -1取消，0未确认，1已确认，2退款，3完成，4已入住
 * goods_status 1待发货，2已发货，3已收货，4待入住，5已入住
 */
function orders_check_status($item) {
	global $_W;
	if ($item['store_type'] == STORE_TYPE_HOTEL) {
		$room = pdo_get('storex_room', array('id' => $item['roomid']), array('id', 'is_house'));
	}
	if ($item['paystatus'] == PAY_STATUS_PAID) {
		$refund_log = pdo_get('storex_refund_logs', array('uniacid' => $_W['uniacid'], 'orderid' => $item['id']), array('id', 'status'));
	}
	//1是显示,2不显示
	$item['is_pay'] = 2;//立即付款 is_pay
	$item['is_cancel'] = 2;//取消订单is_cancel
	$item['is_confirm'] = 2;//确认收货is_confirm
	$item['is_over'] = 2;//再来一单is_over
	$item['is_comment'] = 2;//显示评价is_comment
	$item['is_refund'] = 2;//显示退款is_refund
	
	if ($item['status'] == ORDER_STATUS_NOT_SURE) {//未确认
		if ($item['paystatus'] == PAY_STATUS_UNPAID) {
			$item['is_pay'] = 1;
		}
		$item['is_cancel'] = 1;
	} elseif ($item['status'] == ORDER_STATUS_CANCEL) {//取消
		if ($item['paystatus'] == PAY_STATUS_UNPAID) {
			$item['is_over'] = 1;
		} elseif ($item['paystatus'] == PAY_STATUS_PAID) {
			if (empty($refund_log)) {
				if (check_ims_version() || $item['paytype'] == 'credit') {
					$item['is_refund'] = 1;
				}
			}
		}
	} elseif ($item['status'] == ORDER_STATUS_SURE) {//已确认
		if ($item['store_type'] == STORE_TYPE_HOTEL) {//酒店
			if (!empty($room)) {
				if ($item['paystatus'] == PAY_STATUS_UNPAID) {
					$item['is_pay'] = 1;
				}
				if ($item['goods_status'] == GOODS_STATUS_NOT_CHECKED || empty($item['goods_status'])) {
					$item['is_cancel'] = 1;
				}
			}
		} else {//非酒店
			if ($item['paystatus'] == PAY_STATUS_PAID) {//已支付
				if ($item['mode_distribute'] == 1) {//自提
					$item['is_cancel'] = 1;
				} elseif ($item['mode_distribute'] == 2) {
					if ($item['goods_status'] == GOODS_STATUS_NOT_SHIPPED) {
						$item['is_cancel'] = 1;
					} elseif ($item['goods_status'] == GOODS_STATUS_SHIPPED) {
						$item['is_confirm'] = 1;
					}
				}
			} elseif ($item['paystatus'] == PAY_STATUS_UNPAID) {
				if ($item['mode_distribute'] == 1) {//自提
					$item['is_cancel'] = 1;
					$item['is_pay'] = 1;
				} elseif ($item['mode_distribute'] == 2) {
					if ($item['goods_status'] == GOODS_STATUS_NOT_SHIPPED) {
						$item['is_cancel'] = 1;
						$item['is_pay'] = 1;
					} elseif ($item['goods_status'] == GOODS_STATUS_SHIPPED) {
						$item['is_confirm'] = 1;
						$item['is_pay'] = 1;
					} elseif ($item['goods_status'] == GOODS_STATUS_RECEIVED) {
						$item['is_pay'] = 1;
					}
				}
			}
		}
	} elseif ($item['status'] == ORDER_STATUS_REFUSE) {//拒绝
		if ($item['paystatus'] == PAY_STATUS_PAID) {
			if (empty($refund_log)) {
				$item['is_refund'] = 1;
			}
			$item['is_over'] = 1;
		}
	} elseif ($item['status'] == ORDER_STATUS_OVER) {//完成
		if ($item['comment'] == 0) {
			$item['is_comment'] = 1;
		}
		$item['is_over'] = 1;
	}
	$setting = pdo_get('storex_set', array('weid' => intval($_W['uniacid'])));
	if ($setting['refund'] == 1) {
		$item['is_cancel'] = 2;
	}
	$item['order_status_cn'] = order_status($item['status']);
	$item['pay_status_cn'] = order_pay_status($item['paystatus']);
	$item['goods_status_cn'] = '';
	if ($item['paystatus'] == 1 && !empty($refund_log['status'])) {
		$item['pay_status_cn'] = order_refund_status($refund_log['status']);
	}
	if (!empty($item['goods_status'])) {
		$item['goods_status_cn'] = order_goods_status($item['goods_status']);
	}
	return $item;
}

//提交申请退款
function order_build_refund($orderid) {
	global $_W;
	$order = pdo_get('storex_order', array('id' => $orderid));
	$refund = pdo_get('storex_refund_logs', array('orderid' => $orderid));
	if (empty($order)) {
		return error(-1, '订单不存在或已删除');
	}
	if ($order['sum_price'] <= 0) {
		return error(-1, '订单支付金额为0, 不能发起退款申请');
	}
	$logs = array(
		'type' => $order['paytype'],
		'uniacid' => $_W['uniacid'],
		'orderid' => intval($orderid),
		'storeid' => intval($order['hotelid']),
		'refund_fee' => $order['sum_price'],
		'total_fee' => $order['sum_price'],
		'status' => REFUND_STATUS_PROCESS,
		'time' => TIMESTAMP
	);
	if (empty($refund)) {
		pdo_update('storex_order', array('refund_status' => 1), array('id' => $orderid));
		pdo_insert('storex_refund_logs', $logs);
	} else {
		if ($refund['status'] == REFUND_STATUS_SUCCESS) {
			return error(-1, '退款已成功, 不能发起退款');
		} elseif ($refund['status'] == REFUND_STATUS_PROCESS) {
			return error(-1, '退款处理中');
		} elseif ($refund['status'] == REFUND_STATUS_FAILED) {
		}
	}
	return true;
}

function order_begin_refund($orderid) {
	global $_W;
	$refund = pdo_get('storex_refund_logs', array('uniacid' => $_W['uniacid'], 'orderid' => $orderid));
	$order = pdo_get('storex_order', array('weid' => $_W['uniacid'], 'id' => $orderid));
	if (empty($refund)) {
		return error(-1, '退款申请不存在或已删除');
	}
	if ($refund['status'] == REFUND_STATUS_SUCCESS) {
		return error(-1, '退款已成功, 不能发起退款');
	}
	if ($order['paytype'] == 'credit') {
		load()->model('mc');
		$uid = mc_openid2uid($order['openid']);
		if (!empty($uid)) {
			$log = array(
				$uid,
				"万能小店订单退款, 订单号:{$order['ordersn']}, 退款金额:{$order['sum_price']}元",
				'wn_storex'
			);
			mc_credit_update($uid, 'credit2', $order['sum_price'], $log);
			pdo_update('storex_refund_logs', array('status' => REFUND_STATUS_SUCCESS, 'time' => TIMESTAMP), array('id' => $refund['id'], 'uniacid' => $_W['uniacid']));
		}
		return true;
	}
}

//订单拒绝
function order_refuse_notice($params) {
	if (!empty($params['tpl_status']) && !empty($params['refuse_templateid'])) {
		order_notice_tpl($params['openid'], 'refuse_templateid', $params, $params['refuse_templateid']);
	} else {
		$info = '您在' . $params['store'] . '预订的' . $params['room'] . "不足。已为您取消订单";
		$status = send_custom_notice('text', array('content' => urlencode($info)), $params['openid']);
	}
}

//订单确认
function order_sure_notice($params) {
	if (!empty($params['tpl_status']) && !empty($params['templateid']) && $params['store_type'] == STORE_TYPE_HOTEL) {
		order_notice_tpl($params['openid'], 'templateid', $params, $params['templateid']);
	} else {
		$info = '您在' . $params['store'] . '预订的' . $params['room'] . '已预订成功';
		$status = send_custom_notice('text', array('content' => urlencode($info)), $params['openid']);
	}
}

//订单完成
function order_over_notice($params) {
	$info = '您在' . $params['store'] . '购买的' . $params['room'] . '的订单已完成,欢迎下次光临';
	$status = send_custom_notice('text', array('content' => urlencode($info)), $params['openid']);
}

//订单入住
function order_checked_notice($params) {
	if (!empty($params['tpl_status']) && !empty($params['check_in_templateid']) && $params['store_type'] == STORE_TYPE_HOTEL) {
		order_notice_tpl($params['openid'], 'check_in_templateid', $params, $params['check_in_templateid']);
	} else {
		$info = '您已成功入住' . $params['store'] . '预订的' . $params['room'];
		$status = send_custom_notice('text', array('content' => urlencode($info)), $params['openid']);
	}
}

//订单提交成功
function order_confirm_notice($params) {
	if (!empty($params['tpl_status']) && !empty($params['confirm_templateid'])) {
		order_notice_tpl($params['openid'], 'confirm_templateid', $params, $params['confirm_templateid']);
	} else {
		$info = '您在' . $params['store'] . '购买' . $params['room'] . '的订单已提交成功！';
		$status = send_custom_notice('text', array('content' => urlencode($info)), $params['openid']);
	}
}

function order_notice_tpl($openid, $type, $params, $templateid) {
	if (!in_array($type, array('refuse_templateid', 'templateid', 'finish_templateid', 'check_in_templateid', 'confirm_templateid'))) {
		return false;
	}
	$tplnotice_list = array(
		'refuse_templateid' => array(
			'first' => array('value' => '尊敬的宾客，非常抱歉的通知您，您的预订订单被拒绝。'),
			'keyword1' => array('value' => $params['ordersn']),
			'keyword3' => array('value' => $params['nums']),
			'keyword4' => array('value' => $params['sum_price']),
			'keyword5' => array('value' => '商品不足'),
		),
		'templateid' => array(
			'first' => array('value' => '您好，您已成功预订' . $params['store'] . '！'),
			'order' => array('value' => $params['ordersn']),
			'Name' => array('value' => $params['contact_name']),
			'datein' => array('value' => date('Y-m-d', $params['btime'])),
			'dateout' => array('value' => date('Y-m-d', $params['etime'])),
			'number' => array('value' => $params['nums']),
			'room type' => array('value' => $params['style']),
			'pay' => array('value' => $params['sum_price']),
			'remark' => array('value' => '酒店预订成功')
		),
		'finish_templateid' => array(
			'first' => array('value' =>'您已成功办理离店手续，您本次入住酒店的详情为'),
			'keyword1' => array('value' => date('Y-m-d', $params['btime'])),
			'keyword2' => array('value' => date('Y-m-d', $params['etime'])),
			'keyword3' => array('value' => $params['sum_price']),
			'remark' => array('value' => '欢迎您的下次光临。')
		),
		'check_in_templateid' => array(
			'first' =>array('value' => '您好,您已入住' . $params['store'] . $params['room']),
			'hotelName' => array('value' => $params['store']),
			'roomName' => array('value' => $params['room']),
			'date' => array('value' => date('Y-m-d', $params['btime'])),
			'remark' => array('value' => '如有疑问，请咨询' . $params['phone'] . '。'),
		),
		'confirm_templateid' => array(
			'first' => array('value' => '您的订单已提交成功！'),
			'keyword1' => array('value' => $params['ordersn']),
			'keyword2' => array('value' => $params['contact_name']),
			'remark' => array('value' => '如有疑问，请咨询' . $params['phone'] . '。'),
		),
	);
	$tplnotice = $tplnotice_list[$type];
	if ($type == 'refuse_templateid' && $params['store_type'] == STORE_TYPE_HOTEL) {
		$tplnotice['keyword2'] = array('value' => date('Y.m.d', $params['btime']) . '-' . date('Y.m.d', $params['etime']));
	}
	$account_api = WeAccount::create();
	$account_api->sendTplNotice($openid, $templateid, $tplnotice);
}

function get_status_logs($id) {
	$logs = pdo_getall('storex_order_logs', array('orderid' => $id), array(), '', 'time ASC');
	if (!empty($logs) && is_array($logs)) {
		$types = array('status', 'goods_status', 'paystatus', 'refund', 'refund_status');
		foreach ($logs as &$val) {
			if (in_array($val['type'], $types)) {
				$val['time'] = date('Y-m-d H:i', $val['time']);
				if ($val['type'] == 'status') {
					if ($val['after_change'] == -1) {
						$val['msg'] = "订单取消";
					} elseif ($val['after_change'] == 1) {
						$val['msg'] = "订单确认";
					} elseif ($val['after_change'] == 2) {
						$val['msg'] = "订单拒绝";
					} elseif ($val['after_change'] == 3) {
						$val['msg'] = "订单完成";
					} elseif ($val['after_change'] == 0) {
						$val['msg'] = "下单成功";
					}
				} elseif ($val['type'] == 'goods_status') {
					if ($val['after_change'] == '5') {
						$val['msg'] = "客户入住";
					} elseif ($val['after_change'] == '2') {
						$val['msg'] = "商家发货";
					} elseif ($val['after_change'] == '3') {
						$val['msg'] = "客户收货";
					}
				} elseif ($val['type'] == 'paystatus') {
					if ($val['after_change'] == '1') {
						$val['msg'] = "订单支付成功";
					}
				} elseif ($val['type'] == 'refund') {
					if ($val['after_change'] == '1') {
						$val['msg'] = "订单退款申请成功";
					}
				} elseif ($val['type'] == 'refund_status') {
					if ($val['after_change'] == '2') {
						$val['msg'] = "订单退款成功";
					}
				}
			}
		}
		unset($val);
	}
	return $logs;
}