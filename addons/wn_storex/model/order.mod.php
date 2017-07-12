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
		REFUND_STATUS_APPLY => '退款申请',
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
	if ($item['store_type'] == 1) {
		$room = pdo_get('storex_room', array('id' => $item['roomid']), array('id', 'is_house'));
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
// 			if (empty($item['refund_status'])) {
// 				$item['is_refund'] = 1;
// 			}
		}
	} elseif ($item['status'] == ORDER_STATUS_SURE) {//已确认
		if ($item['store_type'] == 1) {//酒店
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
		if ($item['paystatus'] == 1) {
			$item['is_refund'] = 1;
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
	$item['order_status'] = order_status($item['status']);
	return $item;
}

//提交申请退款
function order_build_refund($orderid) {
	global $_W;
	$order = pdo_get('storex_order', array('id' => $orderid));
	if (empty($order)) {
		return error(-1, '订单不存在或已删除');
	}
	if ($order['sum_price'] <= 0) {
		return error(-1, '订单支付金额为0, 不能发起退款申请');
	}
	$moduleid = pdo_fetchcolumn("SELECT mid FROM " . tablename('modules') . " WHERE name = :name", array(':name' => 'wn_storex'));
	$moduleid = empty($moduleid) ? '000000' : sprintf("%06d", $moduleid);
	$logs = array(
		'type' => $order['paytype'],
		'uniacid' => $_W['uniacid'],
		'orderid' => intval($orderid),
		'out_refund_no' => date('YmdHis') . $moduleid . random(8, 1),
		'refund_fee' => $order['sum_price'],
		'total_fee' => $order['sum_price'],
		'status' => REFUND_STATUS_APPLY,
		'time' => TIMESTAMP
	);
	pdo_update('storex_order', array('is_refund' => 2), array('id' => $id));
	pdo_insert('storex_refund_logs', $logs);
	return pdo_insertid();
}

function order_begin_refund($orderid) {
	global $_W;
	$refund = pdo_get('storex_refund_logs', array('uniacid' => $_W['uniacid'], 'orderid' => $orderid));
	$order = pdo_get('storex_order', array('uniacid' => $_W['uniacid'], 'orderid' => $orderid));
	if(empty($refund)) {
		return error(-1, '退款申请不存在或已删除');
	}
	if($refund['status'] == REFUND_STATUS_SUCCESS) {
		return error(-1, '退款已成功, 不能发起退款');
	}

	if($refund['type'] == 'credit') {
		if($order['openid'] > 0) {
			$log = array(
				$order['openid'],
				"万能小店订单退款, 订单号:{$order['ordersn']}, 退款金额:{$ordersn['sum_price']}元",
				'wn_storex'
			);
			load()->model('mc');
			mc_credit_update($order['openid'], 'credit2', $order['sum_price'], $log);
			pdo_update('storex_refund_logs', array('status' => REFUND_STATUS_SUCCESS, 'time' => TIMESTAMP), array('id' => $refund['id'], 'uniacid' => $_W['uniacid']));
		}
		return true;
	} elseif ($refund['type'] == 'wechat') {
		load()->classs('weixin.pay');
		$wxpay_api = new WeiXinPay();
		$params = array(
			'total_fee' => $refund['fee'] * 100,
			'refund_fee' => $refund['fee'] * 100,
			'out_trade_no' => $refund['out_trade_no'],
			'out_refund_no' => $refund['out_refund_no'],
		);
		$response = $pay->refundOrder($params);
		if(is_error($response)) {
			return error(-1, $response['message']);
		}
		pdo_update('storex_refund_logs', array('status' => REFUND_STATUS_SUCCESS, 'time' => TIMESTAMP), array('id' => $refund['id'], 'uniacid' => $_W['uniacid']));
		return true;
	} elseif($refund['pay_type'] == 'alipay') {
		
	}
}

function order_query_refund($orderid) {
	global $_W;
	$refund = pdo_get('storex_refund_logs', array('uniacid' => $_W['uniacid'], 'orderid' => $orderid));
	if (empty($refund)) {
		return error(-1, '退款申请不存在或已删除');
	}
	if ($refund['pay_type'] == 'wechat') {
		//只有微信需要查询,余额和支付宝不需要
		load()->classs('weixin.pay');
		$wxpay_api = new WeiXinPay();
		$response = $pay->payRefund_query(array('out_refund_no' => $refund['out_refund_no']));
		if (is_error($response)) {
			return $response;
		}
		$wechat_status = $pay->payRefund_status();
		$update = array(
			'refund_status' => $wechat_status[$response['refund_status_0']]['value'],
		);
		if($response['refund_status_0'] == 'SUCCESS') {
			$update['time'] = TIMESTAMP;
			$update['status'] = REFUND_STATUS_SUCCESS;
			pdo_update('storex_refund_logs', array('status' => REFUND_STATUS_SUCCESS, 'time' => TIMESTAMP), array('uniacid' => $_W['uniacid'], 'id' => $refund['id']));
		} else {
			pdo_update('storex_refund_logs', $update, array('status' => REFUND_STATUS_SUCCESS, 'time' => TIMESTAMP), 'id' => $refund['id']));
		}
		return true;
	}
	return true;
}