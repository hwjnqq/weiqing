<?php 
//action 1预定  2购买
function orders_check_status($item){
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
	//1是显示,2不显示
	$item['is_pay'] = 2;//立即付款 is_pay
	$item['is_cancle'] = 2;//取消订单is_cancle
	$item['is_confirm'] = 2;//确认收货is_confirm
	$item['is_over'] = 2;//再来一单is_over
	$item['is_comment'] = 2;//显示评价is_comment
	if ($item['status'] == 0){
		if ($item['action'] == 1){
			$status = STORE_SURE_STATUS;
		} else {
			if ($item['paystatus'] == 0){
				$status = STORE_UNPAY_STATUS;
				$item['is_pay'] = 1;
			} else {
				$status = STORE_SURE_STATUS;
			}
		}
		$item['is_cancle'] = 1;
	} elseif ($item['status'] == -1){
		if ($item['paystatus'] == 0){
			$status = STORE_CANCLE_STATUS;
			$item['is_over'] = 1;
		} else {
			$status = STORE_REPAY_STATUS;
		}
	} elseif ($item['status'] == 1){
		if ($item['store_type'] == 1){//酒店
			if ($item['action'] == 1){
				$status = STORE_CONFIRM_STATUS;
				$item['is_cancle'] = 1;
			} else {
				$status = STORE_UNLIVE_STATUS;
				$item['is_cancle'] = 1;
				if ($item['paystatus'] == 0){
					$item['is_pay'] = 1;
				}
			}
		} else {
			if ($item['action'] == 1 || $item['paystatus'] == 1){//预定
				if ($item['mode_distribute'] == 1){//自提
					$item['is_cancle'] = 1;
					$status = STORE_CONFIRM_STATUS;
				} elseif ($item['mode_distribute'] == 2) {
					if ($item['goods_status'] == 1){
						$item['is_cancle'] = 1;
						$status = STORE_UNSENT_STATUS;
					} elseif ($item['goods_status'] == 2){
						$item['is_confirm'] = 1;
						$status = STORE_SENT_STATUS;
					} elseif ($item['goods_status'] == 3){
						$status = STORE_GETGOODS_STATUS;
					} else {
						$item['is_cancle'] = 1;
						$status = STORE_CONFIRM_STATUS;
					}
				}
			} else {
				if ($item['paystatus'] == 0){
					if ($item['mode_distribute'] == 1 ){//自提
						$item['is_cancle'] = 1;
						$item['is_pay'] = 1;
						$status = STORE_CONFIRM_STATUS;
					} elseif ($item['mode_distribute'] == 2) {
						if ($item['goods_status'] == 1){
							$item['is_cancle'] = 1;
							$item['is_pay'] = 1;
							$status = STORE_UNSENT_STATUS;
						} elseif ($item['goods_status'] == 2){
							$item['is_confirm'] = 1;
							$status = STORE_SENT_STATUS;
						} elseif ($item['goods_status'] == 3){
							$status = STORE_GETGOODS_STATUS;
						} else {
							$item['is_cancle'] = 1;
							$item['is_pay'] = 1;
							$status = STORE_CONFIRM_STATUS;
						}
					}
				} else {
					$status = STORE_REPAY_STATUS;
				}
			}
		}
	} elseif ($item['status'] == 2){
		if ($item['paystatus'] == 0){
			$status = STORE_REFUSE_STATUS;
		} else {
			$status = STORE_REPAY_SUCCESS_STATUS;
		}
	} elseif ($item['status'] == 4){
		$status = STORE_LIVE_STATUS;
		$item['is_over'] = 1;
	} elseif ($item['status'] == 3){
		$status = STORE_OVER_STATUS;
		$item['is_over'] = 1;
		if ($item['comment'] == 0){
			$item['is_comment'] = 1;
		}
	}
	$setting = pdo_get('storex_set', array('weid' => intval($_W['uniacid'])));
	if ($setting['refund'] == 1) {
		$item['is_cancle'] = 2;
	}
	$item['order_status'] = $order_status_text[$status];
	return $item;
}