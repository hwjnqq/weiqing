<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'delete', 'post');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$store_info = $_W['wn_storex']['store_info'];
$storeid = intval($store_info['id']);

if ($op == 'display') {
	$activity_list = pdo_getall('storex_goods_activity', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
}

if ($op == 'post') {
	$id = intval($_GPC['id']);
	$goods_list = pdo_getall('storex_goods', array('weid' => $_W['uniacid'], 'store_base_id' => $storeid), array(), 'id');
	if (!empty($goods_list) && is_array($goods_list)) {
		foreach ($goods_list as &$goods) {
			$goods['thumb'] = tomedia($goods['thumb']);
		}
		unset($goods);
	}
	$current_activity = pdo_get('storex_goods_activity', array('id' => $id));
	if (empty($current_activity)) {
		$current_activity['starttime'] = time();
		$current_activity['endtime'] = time();
	}
	if (checksubmit()) {
		$type = !empty($_GPC['type']) ? intval($_GPC['type']) : 1;
		if (empty($_GPC['title']) || empty($_GPC['price']) || empty($_GPC['goodsid'])) {
			message('参数错误', '', 'error');
		}
		$data = array(
			'title' => trim($_GPC['title']),
			'goodsid' => intval($_GPC['goodsid']),
			'price' => $_GPC['price'],
			'starttime' => strtotime($_GPC['time_limit']['start']),
			'endtime' => strtotime($_GPC['time_limit']['end']),
			'status' => 1
		);
		if ($type == 1) {
			$data['nums'] = intval($_GPC['nums']);
		}
		$activity_info = pdo_get('storex_goods_activity', array('id' => $id), array('id'));
		if (empty($activity_info)) {
			$data['type'] = $type;
			$data['uniacid'] = $_W['uniacid'];
			$data['storeid'] = $storeid;
			pdo_insert('storex_goods_activity', $data);
		} else {
			pdo_update('storex_goods_activity', $data, array('id' => $id));
		}
		message('编辑成功', referer(), 'success');
	}
}


if ($op == 'delete') {
	$id = intval($_GPC['id']);
	$activity_info = pdo_get('storex_goods_activity', array('id' => $id, 'uniacid' => $_W['uniacid']));
	if (empty($activity_info)) {
		message('文章信息错误', referer(), 'error');
	}
	pdo_delete('storex_goods_activity', array('id' => $id, 'uniacid' => $_W['uniacid']));
	message('删除成功', referer(), 'success');
}

include $this->template('store/shop_activity');