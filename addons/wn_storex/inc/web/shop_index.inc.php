<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('dashboard');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'dashboard';

if ($op == 'dashboard') {
	$storeid = intval($_W['wn_storex']['store_info']['id']);
	$yesterday_period = revenue(-1);
	$today_period = revenue(0);
	//昨日营业额
	$yesterday_revenue_condition = " WHERE weid = :uniacid AND status = :status AND time >= :starttime AND time < :endtime AND hotelid = :hotelid";
	$yesterday_revenue_params = array(':starttime' => $yesterday_period['starttime'], ':endtime' => $yesterday_period['endtime'], ':uniacid' => $_W['uniacid'], ':hotelid' => $storeid, ':status' => 3);
	$yesterday_revenue = pdo_fetchcolumn("SELECT SUM(sum_price) FROM" . tablename('storex_order') . $yesterday_revenue_condition, $yesterday_revenue_params);
	//昨日订单数
	$yesterday_order_condition = " WHERE weid = :uniacid AND status != :status AND time >= :starttime AND time < :endtime AND hotelid = :hotelid";
	$yesterday_order_params = array(':starttime' => $yesterday_period['starttime'], ':endtime' => $yesterday_period['endtime'], ':uniacid' => $_W['uniacid'], ':hotelid' => $storeid, ':status' => -1);
	$yesterday_order_count = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('storex_order') . $yesterday_order_condition, $yesterday_order_params);
	//今日订单数
	$today_order_condition = " WHERE weid = :uniacid AND status != :status AND time >= :starttime AND time < :endtime AND hotelid = :hotelid";
	$today_order_params = array(':starttime' => $today_period['starttime'], ':endtime' => $today_period['endtime'], ':uniacid' => $_W['uniacid'], ':hotelid' => $storeid, ':status' => -1);
	$today_order_count = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('storex_order') . $today_order_condition, $today_order_params);
	//待确认订单
	$not_confirmed_order_condition = " WHERE weid = :uniacid AND status = :status AND hotelid = :hotelid";
	$not_confirmed_order_params = array(':uniacid' => $_W['uniacid'], ':hotelid' => $storeid, ':status' => 0);
	$not_confirmed_order_count = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('storex_order') . $not_confirmed_order_condition, $not_confirmed_order_params);
	//营业额趋势图

	$starttime = empty($_GPC['time']['start']) ? mktime(0, 0, 0, date('m') , 1, date('Y')) : strtotime($_GPC['time']['start']);
	$endtime = empty($_GPC['time']['end']) ? TIMESTAMP : strtotime($_GPC['time']['end']) + 86399;
	$num = ($endtime + 1 - $starttime) / 86400;
	$stat = array();
	for ($i = 0; $i < $num; $i++) {
		$time = $i * 86400 + $starttime;
		$key = date('m-d', $time);
		$stat[$key] = 0;
	}
	$data = pdo_fetchall("SELECT id, time, sum_price, hotelid FROM " . tablename('storex_order') . " WHERE weid = :uniacid AND time >= :starttime AND time <= :endtime AND status = :status AND hotelid = :storeid ORDER BY time ASC", array(':uniacid' => $_W['uniacid'], ':starttime' => $starttime, ':endtime' => $endtime, ':status' => 3, ':storeid' => $storeid));
	if (!empty($data)) {
		foreach ($data as $da) {
			$key = date('m-d', $da['time']);
			$stat[$key] += $da['sum_price'];
		}
	}
	$out['label'] = array_keys($stat);
	$out['datasets'] = array_values($stat);
	// echo "<pre>";
	// print_r($out);
	// echo "</pre>";
	// exit;
	if ($_W['isajax']) {
		$starttime = empty($_GPC['time']['start']) ? mktime(0, 0, 0, date('m') , 1, date('Y')) : strtotime($_GPC['time']['start']);
		$endtime = empty($_GPC['time']['end']) ? TIMESTAMP : strtotime($_GPC['time']['end']) + 86399;
		$num = ($endtime + 1 - $starttime) / 86400;
		$stat = array();
		for ($i = 0; $i < $num; $i++) {
			$time = $i * 86400 + $starttime;
			$key = date('m-d', $time);
			$stat[$key] = 0;
		}
		$data = pdo_fetchall("SELECT id, time, sum_price, hotelid FROM " . tablename('storex_order') . " WHERE weid = :uniacid AND time >= :starttime AND time <= :endtime AND status = :status AND hotelid = :storeid ORDER BY time ASC", array(':uniacid' => $_W['uniacid'], ':starttime' => $starttime, ':endtime' => $endtime, ':status' => 3, ':storeid' => $storeid));
		if (!empty($data)) {
			foreach ($data as $da) {
				$key = date('m-d', $da['time']);
				$stat[$key] += $da['sum_price'];
			}
		}

		// $out['label'] = array_keys($stat['consume']);
		// $out['datasets'] = array('recharge' => array_values($stat['recharge']), 'consume' => array_values($stat['consume']));
		$out['label'] = array_keys($stat);
		$out['datasets'] = array_values($stat);
		message(error(0, $out), '', 'ajax');
		// exit(json_encode($out));
	}
	$starttime = empty($_GPC['time']['start']) ? strtotime(date('Y-m-d')) - 7 * 86400 : strtotime($_GPC['time']['start']);
	$endtime = empty($_GPC['time']['end']) ? TIMESTAMP : strtotime($_GPC['time']['end']) + 86399;
	$list = pdo_fetchall("SELECT * FROM " . tablename('storex_order') . " WHERE weid = :uniacid AND time >= :starttime AND time <= :endtime AND status = :status AND hotelid = :storeid ORDER BY time ASC", array(':uniacid' => $_W['uniacid'], ':starttime' => $starttime, ':endtime' => $endtime, ':status' => 3, ':storeid' => $storeid));
	$day = array();
	if (!empty($list) && is_array($list)) {
		foreach ($list as $row) {
			$day[] = date('m-d', $row['time']);
			$fee[] = $row['sum_price'];
		}
	}
	// echo "<pre>";
	// print_r(array($starttime, $endtime));
	// echo "</pre>";
	// echo "<pre>";
	// print_r($list);
	// echo "</pre>";
	// echo "<pre>";
	// print_r($day);
	// echo "</pre>";
	// echo "<pre>";
	// print_r($fee);
	// echo "</pre>";exit;
}

/** 
* 
* @param $period 时间周期,默认0为今日,-1为昨日,-7为七日
* @return 开始结束时间
*/
function revenue($period) {
	if ($period == 0) {
		$starttime = strtotime(date('Y-m-d'));
		$endtime = $starttime + 86400;
	} elseif ($period == -1) {
		$starttime = strtotime(date('Y-m-d',strtotime($period . 'day')));
		$endtime = strtotime(date('Y-m-d'));
	} else {
		$starttime = strtotime(date('Y-m-d',strtotime($period . 'day')));
		$endtime = strtotime(date('Y-m-d')) + 86400;
	}
	return array('starttime' => $starttime, 'endtime' => $endtime);
}
include $this->template('store/shop_dashboard');