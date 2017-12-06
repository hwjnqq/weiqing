<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('order_stat', 'sales_stat');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'order_stat';

$storeid = intval($_W['wn_storex']['store_info']['id']);
//订单趋势图
$starttime = empty($_GPC['time']['start']) ? mktime(0, 0, 0, date('m') , 1, date('Y')) : strtotime($_GPC['time']['start']);
$endtime = empty($_GPC['time']['end']) ? TIMESTAMP : strtotime($_GPC['time']['end']) + 86399;
$num = ($endtime + 1 - $starttime) / 86400;

if ($op == 'order_stat') {
	$order_stat = array();
	for ($i = 0; $i < $num; $i++) {
		$time = $i * 86400 + $starttime;
		$key = date('Ymd', $time);
		$label[] = $key;
		$order_stat['canceled_order'][$key] = 0;
		$order_stat['not_confirmed_order'][$key] = 0;
		$order_stat['refund_order'][$key] = 0;
		$order_stat['completed_order'][$key] = 0;
	}
	$canceled_order = pdo_fetchall("SELECT id, time, hotelid FROM " . tablename('storex_order') . " WHERE weid = :uniacid AND time >= :starttime AND time <= :endtime AND status = :status AND hotelid = :storeid ORDER BY time ASC", array(':uniacid' => $_W['uniacid'], ':starttime' => $starttime, ':endtime' => $endtime, ':status' => ORDER_STATUS_CANCEL, ':storeid' => $storeid));
	$not_confirmed_order = pdo_fetchall("SELECT id, time, hotelid FROM " . tablename('storex_order') . " WHERE weid = :uniacid AND time >= :starttime AND time <= :endtime AND status = :status AND hotelid = :storeid ORDER BY time ASC", array(':uniacid' => $_W['uniacid'], ':starttime' => $starttime, ':endtime' => $endtime, ':status' => ORDER_STATUS_NOT_SURE, ':storeid' => $storeid));
	$refund_order = pdo_fetchall("SELECT id, time, storeid FROM " . tablename('storex_refund_logs') . " WHERE uniacid = :uniacid AND time >= :starttime AND time <= :endtime AND status = :status AND storeid = :storeid ORDER BY time ASC", array(':uniacid' => $_W['uniacid'], ':starttime' => $starttime, ':endtime' => $endtime, ':status' => REFUND_STATUS_SUCCESS, ':storeid' => $storeid));
	$completed_order = pdo_fetchall("SELECT id, time, hotelid FROM " . tablename('storex_order') . " WHERE weid = :uniacid AND time >= :starttime AND time <= :endtime AND status = :status AND hotelid = :storeid ORDER BY time ASC", array(':uniacid' => $_W['uniacid'], ':starttime' => $starttime, ':endtime' => $endtime, ':status' => ORDER_STATUS_OVER, ':storeid' => $storeid));
	if (!empty($canceled_order) && is_array($canceled_order)) {
		foreach ($canceled_order as $value) {
			$key = date('Ymd', $value['time']);
			$order_stat['canceled_order'][$key]++;
		}
	}
	if (!empty($not_confirmed_order) && is_array($not_confirmed_order)) {
		foreach ($not_confirmed_order as $value) {
			$key = date('Ymd', $value['time']);
			$order_stat['not_confirmed_order'][$key]++;
		}
	}
	if (!empty($refund_order) && is_array($refund_order)) {
		foreach ($refund_order as $value) {
			$key = date('Ymd', $value['time']);
			$order_stat['refund_order'][$key]++;
		}
	}
	if (!empty($completed_order) && is_array($completed_order)) {
		foreach ($completed_order as $value) {
			$key = date('Ymd', $value['time']);
			$order_stat['completed_order'][$key]++;
		}
	}
	if (!empty($order_stat) && is_array($order_stat)) {
		foreach ($order_stat as $key => $value) {
			$series_data[$key] = array_values($value);
		}
	}
	$chart_data['label'] = $label;
	$chart_data['series_data'] = $series_data;
	foreach ($series_data as $key => $order_info) {
		foreach ($order_info as $k => $val) {
			$order_list[$k][$key] = $val;
			$order_list[$k]['date'] = $label[$k];
		}

	}
	krsort($order_list);
	include $this->template('store/shop_stat_order');
}

if ($op == 'sales_stat') {
	$sales_stat = array();
	for ($i = 0; $i < $num; $i++) {
		$time = $i * 86400 + $starttime;
		$key = date('Ymd', $time);
		$sales_stat[$key] = 0;
	}
	$sales_list = pdo_getall('storex_sales', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array('date', 'cumulate'));
	$total_sales = 0;
	if (!empty($sales_list) && is_array($sales_list)) {
		foreach ($sales_list as $key => $value) {
			if (!empty($value['date']) && strtotime($value['date']) >= $starttime && strtotime($value['date']) <= $endtime) {
				$sales_stat[$value['date']] = $value['cumulate'];
				$total_sales += $value['cumulate'];
			}
			
		}
	}
	$chart_data['label'] = array_keys($sales_stat);
	$chart_data['series_data'] = array_values($sales_stat);
	krsort($sales_stat);
	include $this->template('store/shop_stat_sales');
}