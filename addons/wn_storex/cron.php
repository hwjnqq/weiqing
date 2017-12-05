<?php
defined('IN_IA') or exit('Access Denied');

class Wn_storexModuleCron extends WeModuleCron {
	public function doCronGroup() {
		include model.php;
		global $_W, $_GPC;
		$id = intval($_W['cron']['extra']);//活动的id
		if (!check_ims_version()) {
			return;
		}
		//活动
		$group_activity = pdo_get('storex_plugin_group_activity', array('id' => $id));
		if (!empty($group_activity)) {
			if ($group_activity['starttime'] <= TIMESTAMP && TIMESTAMP < $group_activity['endtime']) {
				return;
			}
		} else {
			return;
		}
		//活动下的商品
		$activity_goods = pdo_getall('storex_plugin_activity_goods', array('group_activity' => $group_activity['id']), array(), 'id');
		$activity_goods_ids = array_keys($activity_goods);
		//开始的团   over 1完成2未完成3已退款
		$group = pdo_getall('storex_plugin_group', array('activity_goodsid' => $activity_goods_ids, 'over' => 2), array(), 'id');
		$group_ids = array_keys($group);
		if (!empty($group)) {
			$order = pdo_getall('storex_order', array('group_id' => $group_ids, 'paystatus' => 1), array('id', 'group_id', 'paystatus', 'refund_status', 'openid', 'paytype'), 'id');
			if (!empty($order)) {
				$order_ids = array_keys($order);
				//删除退款成功的
				$refund_log_s = pdo_getall('storex_refund_logs', array('orderid' => $order_ids, 'status' => 2));
				if (!empty($refund_log_s)) {
					foreach ($refund_log_s as $log) {
						if (!empty($order[$log['orderid']])) {
							unset($order[$log['orderid']]);
						}
					}
				}
				load()->model('mc');
				mload()->model('order');
				mload()->model('log');
				if (!empty($order)) {
					foreach ($order as $orderid => $info) {
						$result = order_build_refund($info['id']);
						if (!is_error($result)) {
							$logs = array(
								'table' => 'storex_order_logs',
								'time' => TIMESTAMP,
								'uid' => mc_openid2uid($info['openid']),
								'clerk_type' => 2,
								'orderid' => intval($info['id']),
							);
							$logs['before_change'] = $order['refund_status'];
							$logs['after_change'] = 1;
							$logs['type'] = 'refund_status';
							log_write($logs);
							$refund = pdo_get('storex_refund_logs', array('uniacid' => $_W['uniacid'], 'orderid' => $info['id']), array('status'));
							if ($info['paytype'] == 'credit') {
								$result = order_begin_refund($info['id']);
							} elseif ($info['paytype'] == 'wechat') {
								$result = $this->refund($info['id']);
							} elseif ($info['paytype'] == 'alipay') {
	
							}
							if (!is_error($result)) {
								$logs['type'] = 'refund';
								$logs['before_change'] = $refund['status'];
								$logs['after_change'] = REFUND_STATUS_SUCCESS;
								$logs['clerk_type'] = 2;
								log_write($logs);
								pdo_update('storex_order', array('status' => 2), array('id' => $info['id']));
							}
						}
					}
				}	
			}
		}
	}
}