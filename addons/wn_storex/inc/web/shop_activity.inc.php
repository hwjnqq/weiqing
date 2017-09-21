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
	$base_goods = pdo_getall('storex_goods', array('weid' => $_W['uniacid'], 'store_base_id' => $storeid), array(), 'id');
	if (is_array($base_goods)) {
		$goodsids = array_keys($base_goods);
		$goods_list = pdo_getall('storex_spec_goods', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'goodsid' => $goodsids), array(), 'id');
	}
	if (!empty($base_goods) && is_array($base_goods)) {
		foreach ($base_goods as &$val) {
			$val['thumb'] = tomedia($val['thumb']);
			$val['is_spec'] = 2;
		}
		unset($val);
	}
	if (!empty($goods_list) && is_array($goods_list)) {
		foreach ($goods_list as &$goods) {
			$fake_ids[] = $goods['goodsid'];
			$goods['thumb'] = tomedia($goods['thumb']);
			$goods['goods_val'] = iunserializer($goods['goods_val']);
			$goods['goods_val_title'] = implode('/', $goods['goods_val']);
			$goods['is_spec'] = 1;
		}
		unset($goods);
	} else {
		$goods_list	= $base_goods;
	}
	$not_have_spec_ids = @array_diff($goodsids, $fake_ids);
	if (!empty($not_have_spec_ids) && is_array($not_have_spec_ids)) {
		foreach ($not_have_spec_ids as $key => $value) {
			$goods_list[] = $base_goods[$value];
		}
	}
	$activitys = pdo_getall('storex_goods_activity', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array('id', 'goodsid', 'specid', 'is_spec'), 'id');
	if (!empty($activitys) && is_array($activitys)) {
		foreach ($activitys as $k => $activity) {
			if ($activity['is_spec'] == 1) {
				$spec_ids[$k] = $activity['specid'];
			} elseif ($activity['is_spec'] == 2) {
				$not_spec_ids[$k] = $activity['goodsid'];
			}
		}
	}
	$available_list = $goods_list;
	if (!empty($available_list) && is_array($available_list)) {
		foreach ($available_list as $key => $goods) {
			if ($goods['is_spec'] == 1) {
				if (is_array($spec_ids) && in_array($goods['id'], $spec_ids)) {
					unset($available_list[$key]);
				}
			} elseif ($goods['is_spec'] == 2) {
				if (is_array($not_spec_ids) && in_array($goods['id'], $not_spec_ids)) {
					unset($available_list[$key]);
				}
			}
		}
	}
	$current_activity = pdo_get('storex_goods_activity', array('id' => $id));
	if (empty($current_activity)) {
		$current_activity['starttime'] = time();
		$current_activity['endtime'] = time();
	}
	if ($current_activity['is_spec'] == 1) {
		$current_activity['edit_thumb'] = $goods_list[$current_activity['specid']]['thumb'];
		$current_activity['edit_title'] = $goods_list[$current_activity['specid']]['title'];
		$current_activity['edit_goods_val'] = $goods_list[$current_activity['specid']]['goods_val_title'];
	} else {
		$current_activity['edit_thumb'] = $base_goods[$current_activity['goodsid']]['thumb'];
		$current_activity['edit_title'] = $base_goods[$current_activity['goodsid']]['title'];
	}
	if (checksubmit()) {
		$type = !empty($_GPC['type']) ? intval($_GPC['type']) : 1;
		if (empty($_GPC['title']) || empty($_GPC['price']) || empty($_GPC['goodsid']) || empty($_GPC['starttime']) || empty($_GPC['endtime'])) {
			message('参数错误', '', 'error');
		}
		if ($type == 1 && $_GPC['nums'] <= 0) {
			message('请填写参与人数', '', 'error');
		} 
		$specid = 0;
		$goodsid = intval($_GPC['goodsid']);
		if ($_GPC['is_spec'] == 1) {
			$specid = intval($_GPC['goodsid']);
			$goodsid = $goods_list[$_GPC['goodsid']]['goodsid'];
		}
		$data = array(
			'title' => trim($_GPC['title']),
			'goodsid' => $goodsid,
			'price' => $_GPC['price'],
			'starttime' => strtotime($_GPC['starttime']),
			'endtime' => strtotime($_GPC['endtime']),
			'status' => 1,
			'is_spec' => intval($_GPC['is_spec']),
			'specid' => $specid
		);
		if ($type == 1) {
			$data['nums'] = intval($_GPC['nums']);
			$data['sell_nums'] = 0;
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