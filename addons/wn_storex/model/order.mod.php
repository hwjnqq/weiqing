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
	$order_status_text = array(
		'1' => '待付款',
		'2' => '等待店铺确认',
		'3' => '订单已取消',
		'4' => '正在退款中',
		'5' => '待入住',
		'6' => '店铺已拒绝',
		'7' => '已退款',
		'8' => '已入住',
		'9' => '已完成',
		'10' => '未发货',
		'11' => '已发货',
		'12' => '已收货',
		'13' => '订单已确认'
	);
	if ($item['store_type'] == STORE_TYPE_HOTEL) {
		$room = pdo_get('storex_room', array('id' => $item['roomid']), array('id', 'is_house'));
	}
	if ($item['paystatus'] == PAY_STATUS_PAID) {
		$refund_log = pdo_get('storex_refund_logs', array('uniacid' => $_W['uniacid'], 'orderid' => $item['id']), array('id', 'status'));
	}
	//1是显示,2不显示
	$item['is_pay'] = 2;//立即付款 is_pay
	$item['is_cancle'] = 2;//取消订单is_cancle
	$item['is_confirm'] = 2;//确认收货is_confirm
	$item['is_over'] = 2;//再来一单is_over
	$item['is_comment'] = 2;//显示评价is_comment
	$item['is_refund'] = 2;//显示退款is_refund
	
	if ($item['status'] == ORDER_STATUS_NOT_SURE) {//未确认
		if ($item['paystatus'] == PAY_STATUS_UNPAID) {
			$item['is_pay'] = 1;
		}
		$item['is_cancle'] = 1;
	} elseif ($item['status'] == ORDER_STATUS_CANCEL) {//取消
		if ($item['paystatus'] == PAY_STATUS_UNPAID) {
			$item['is_over'] = 1;
		} elseif ($item['paystatus'] == PAY_STATUS_PAID) {
			if ($item['refund_status'] == 1 && empty($refund_log)) {
				$item['is_refund'] = 1;
			}
		}
	} elseif ($item['status'] == ORDER_STATUS_SURE) {//已确认
		if ($item['store_type'] == STORE_TYPE_HOTEL) {//酒店
			if (!empty($room)) {
				if ($item['paystatus'] == PAY_STATUS_UNPAID) {
					$item['is_pay'] = 1;
				}
				if ($item['goods_status'] == GOODS_STATUS_NOT_CHECKED) {
					$item['is_cancle'] = 1;
				}
			}
		} else {//非酒店
			if ($item['paystatus'] == PAY_STATUS_PAID) {//已支付
				if ($item['mode_distribute'] == 1) {//自提
					$item['is_cancle'] = 1;
				} elseif ($item['mode_distribute'] == 2) {
					if ($item['goods_status'] == GOODS_STATUS_NOT_SHIPPED) {
						$item['is_cancle'] = 1;
					} elseif ($item['goods_status'] == GOODS_STATUS_SHIPPED) {
						$item['is_confirm'] = 1;
					}
				}
			} elseif ($item['paystatus'] == PAY_STATUS_UNPAID) {
				if ($item['mode_distribute'] == 1) {//自提
					$item['is_cancle'] = 1;
					$item['is_pay'] = 1;
				} elseif ($item['mode_distribute'] == 2) {
					if ($item['goods_status'] == GOODS_STATUS_NOT_SHIPPED) {
						$item['is_cancle'] = 1;
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
			if ($item['refund_status'] == 1 && empty($refund_log)) {
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
		$item['is_cancle'] = 2;
	}
	$item['order_status_cn'] = order_status($item['status']);
	$item['pay_status_cn'] = order_pay_status($item['paystatus']);
	$item['goods_status_cn'] = '';
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
			return error(-1, 'dasd');
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
	} elseif ($order['paytype'] == 'wechat') {
		load()->classs('weixin.pay');
		$wxpay_api = new WeiXinPay();
		$params = array(
			'total_fee' => $refund['fee'] * 100,
			'refund_fee' => $refund['fee'] * 100,
			'out_trade_no' => $refund['out_trade_no'],
			'out_refund_no' => $refund['out_refund_no'],
		);
		$response = $pay->refundOrder($params);
		if (is_error($response)) {
			return error(-1, $response['message']);
		}
		pdo_update('storex_refund_logs', array('status' => REFUND_STATUS_SUCCESS, 'time' => TIMESTAMP), array('id' => $refund['id'], 'uniacid' => $_W['uniacid']));
		return true;
	} elseif ($order['paytype'] == 'alipay') {
		
	}
}

function order_query_refund($orderid) {
	global $_W;
	$refund = pdo_get('storex_refund_logs', array('uniacid' => $_W['uniacid'], 'orderid' => $orderid));
	if (empty($refund)) {
		return error(-1, '退款申请不存在或已删除');
	}
	if ($refund['type'] == 'wechat') {
		//只有微信需要查询,余额和支付宝不需要
		load()->classs('weixin.pay');
		$wxpay_api = new WeiXinPay();
		$response = $pay->refundQuery(array('out_refund_no' => $refund['out_refund_no']));
		if (is_error($response)) {
			return error(-1, $response['message']);
		}
		$wechat_status = $pay->payRefund_status();
		$update = array(
			'refund_status' => $wechat_status[$response['refund_status_0']]['value'],
		);
		if ($response['refund_status_0'] == 'SUCCESS') {
			$update['time'] = TIMESTAMP;
			$update['status'] = REFUND_STATUS_SUCCESS;
			pdo_update('storex_refund_logs', array('status' => REFUND_STATUS_SUCCESS, 'time' => TIMESTAMP), array('uniacid' => $_W['uniacid'], 'id' => $refund['id']));
		} else {
			pdo_update('storex_refund_logs', array('status' => REFUND_STATUS_FAILED, 'time' => TIMESTAMP), array('uniacid' => $_W['uniacid'], 'id' => $refund['id']));
		}
		return true;
	}
	return true;
}