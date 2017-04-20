<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('exchange_display', 'post', 'change_status', 'coupon_info', 'delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'exchange_display';

if ($op == 'exchange_display') {
	$title = trim($_GPC['title']);
	$condition = '';
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	if (!empty($title)) {
		$cids = pdo_getall('storex_coupon', array('uniacid' => intval($_W['uniacid']), 'title LIKE' => '%'.$title.'%', 'source' => 1), array(), 'id');
		$cids = implode('\',\'', array_keys($cids));
		$condition = ' AND extra IN(\''.$cids.'\')';
	}
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('storex_activity_exchange')." WHERE uniacid = :uniacid AND type = :type ".$condition, array(':uniacid' => $_W['uniacid'], ':type' => 1));
	$list = pdo_fetchall("SELECT * FROM ".tablename('storex_activity_exchange')." WHERE uniacid = :uniacid AND type = :type ".$condition." ORDER BY id desc LIMIT ".($pindex - 1)*$psize.','. $psize, array(':uniacid' => $_W['uniacid'], ':type' => 1));
	if (!empty($list)) {
		foreach($list as &$ex) {
			$ex['coupon'] = activity_get_coupon_info($ex['extra']);
			$ex['starttime'] = date('Y-m-d', $ex['starttime']);
			$ex['endtime'] = date('Y-m-d', $ex['endtime']);
		}
		unset($ex);
	}
	$pager = pagination($total, $pindex, $psize);
}

if ($op == 'post') {
	if (checksubmit('submit')) {
		$start = $_GPC['coupon_start'];
		$end = $_GPC['coupon_end'];
		$post = array(
			'uniacid' => $_W['uniacid'],
			'extra' => $_GPC['coupon'],
			'status' => intval($_GPC['status']),
			'credittype' => $_GPC['credittype'],
			'credit' => abs(intval($_GPC['credit'])),
			'pretotal' => empty($_GPC['pretotal']) ? 1 : intval($_GPC['pretotal']),
			'status' => $_GPC['status'],
			'starttime' => strtotime($_GPC['date']['start']),
			'endtime' => strtotime($_GPC['date']['end']),
		);
		if ($start && $end) {
			$start = strtotime(str_replace('.', '-', $start));
			$end = strtotime(str_replace('.', '-', $end));
			if ($start > $post['starttime'] || $end < $post['starttime'] || $start > $post['endtime'] || $end < $post['endtime']) {
				message('日期范围超过卡券日期范围', '', 'info');
			}
		}
		$post['type'] = 1;
		if (empty($id)) {
			pdo_insert('storex_activity_exchange', $post);
			message('添加兑换卡券成功', $this->createWeburl('couponexchange', array('op' => 'exchange_display')), 'success');
		}
	}
	$id = intval($_GPC['id']);
	
	if (!empty($id)) {
		$data = pdo_get('storex_activity_exchange', array('id' => $id, 'uniacid' => $_W['uniacid']));
		$data['coupon'] = pdo_get('storex_coupon', array('uniacid' => $_W['uniacid'], 'id' => $data['extra']));
		$data['coupon']['logo_url'] = tomedia($data['coupon']['logo_url']);
	} else {
		$data['starttime'] = time();
		$data['endtime'] = time();
	}
	$coupons = pdo_getall('storex_coupon', array('uniacid' => intval($_W['uniacid']), 'source' => 1), array(), 'id');
	$coupon_exists = pdo_getall('storex_activity_exchange', array('type' => 1, 'uniacid' => $_W['uniacid']), array(), 'extra');
	$coupon_exists = array_keys($coupon_exists);
	foreach ($coupons as $key => &$coupon) {
		$coupon = activity_get_coupon_info($coupon['id']);
		if (in_array($key, $coupon_exists)) {
			unset($coupons[$key]);
		}
	}
	unset($coupon);
}

if ($op == 'change_status') {
	$id = $_GPC['id'];
	$status = intval($_GPC['status']);
	pdo_update('storex_activity_exchange', array('status' => $status),array('uniacid' => $_W['uniacid'], 'id' => $id));
	message(error(0), '', 'ajax');
}

if ($op == 'coupon_info') {
	$coupon = activity_get_coupon_info(intval($_GPC['id']));
	message(error(0, $coupon), '', 'ajax');
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	$exist = pdo_get('storex_activity_exchange', array('id' => $id, 'uniacid' => $_W['uniacid']));
	if (empty($exist)) {
		message('兑换卡券不存在', referer(), 'info');
	}
	pdo_delete('storex_activity_exchange', array('id' => $id, 'uniacid' => $_W['uniacid']));
	message('兑换卡券删除成功', referer(), 'success');
}

include $this->template('couponexchange');