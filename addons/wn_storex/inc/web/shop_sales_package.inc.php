<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'post', 'status', 'delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

load()->model('mc');
$storeid = intval($_GPC['storeid']);
$store_info = $_W['wn_storex']['store_info'];
if ($store_info['store_type'] == 1) {
	message('参数错误', referer(), 'error');
}
if ($op == 'display') {
	$package_list = pdo_getall('storex_sales_package', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
}

if ($op == 'post') {
	$table = gettablebytype($store_info['store_type']);
	$goods_list = pdo_getall($table, array('weid' => $_W['uniacid'], 'store_base_id' => $storeid));
	if (!empty($goods_list) && is_array($goods_list)) {
		foreach ($goods_list as &$goods) {
			$goods['thumb'] = tomedia($goods['thumb']);
			$fake_goods_list[$goods['id']] = $goods;
		}
		unset($goods);
	}
	$package_info = pdo_get('storex_sales_package', array('id' => $_GPC['id']));
	if (empty($package_info)) {
		$package_info = array(
			'title' => '',
			'sub_title' => '',
			'price' => '',
			'express' => '',
			'thumb' => '',
			'goodsids' => array()
		);
	} else {
		$package_info['goodsids'] = !empty($package_info['goodsids']) ? iunserializer($package_info['goodsids']) : array();
	}
	if (!empty($package_info['agent_ratio'])) {
		$package_info['agent_ratio'] = iunserializer($package_info['agent_ratio']);
	} else {
		$package_info['agent_ratio'] = array('1' => 0, '2' => 0, '3' => 0);
	}
	$selected_list = array();
	if (!empty($package_info['goodsids']) && is_array($package_info['goodsids'])) {
		foreach ($package_info['goodsids'] as $value) {
			$selected_list[] = $fake_goods_list[$value];
			unset($fake_goods_list[$value]);
		}
	}
	if (!empty($goods_list) && is_array($goods_list)) {
		foreach ($goods_list as $key => $value) {
			if (empty($fake_goods_list[$value['id']])) {
				unset($goods_list[$key]);
			}
		}
		$goods_list = array_values($goods_list);
	}
	if ($_W['ispost'] && $_W['isajax']) {
		$id = intval($_GPC['params']['id']);
		$package = array(
			'title' => trim($_GPC['params']['title']),
			'sub_title' => trim($_GPC['params']['sub_title']),
			'price' => trim($_GPC['params']['price']),
			'express' => trim($_GPC['params']['express']),
			'thumb' => trim($_GPC['params']['thumb']),
			'goodsids' => iserializer($_GPC['params']['goodsids']),
			'status' => 1
		);
		$package['agent_ratio'] = array('1' => 0, '2' => 0, '3' => 0);
		if (!empty($_GPC['params']['agent_ratio'])) {
			foreach ($package['agent_ratio'] as $l => $r) {
				if (!empty($_GPC['params']['agent_ratio'][$l])) {
					$package['agent_ratio'][$l] = $_GPC['params']['agent_ratio'][$l];
				}
			}
		}
		$package['agent_ratio'] = iserializer($package['agent_ratio']);
		$package_info = pdo_get('storex_sales_package', array('id' => $id), array('id'));
		if (empty($package_info)) {
			$package['uniacid'] = $_W['uniacid'];
			$package['storeid'] = $storeid;
			pdo_insert('storex_sales_package', $package);
			$id = pdo_insertid();
		} else {
			pdo_update('storex_sales_package', $package, array('id' => $id));
		}
		if (!empty($_GPC['params']['goodsids']) && is_array($_GPC['params']['goodsids'])) {
			pdo_delete('storex_goods_package', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'packageid' => $id));
			foreach ($_GPC['params']['goodsids'] as $goodsid) {
				pdo_insert('storex_goods_package', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'packageid' => $id, 'goodsid' => $goodsid));
			}
		}
		message(error(0, ''), $this->createWebUrl('shop_sales_package', array('storeid' => $storeid, 'op' => 'display')), 'ajax');
	}
}

if ($op == 'status') {
	$id = intval($_GPC['id']);
	$package_info = pdo_get('storex_sales_package', array('id' => $id, 'uniacid' => $_W['uniacid']), array('id', 'status'));
	if (empty($package_info)) {
		message('套餐信息错误', referer(), 'error');
	}
	$result = pdo_update('storex_sales_package', array('status' => $_GPC['status']), array('id' => $id));
	if (empty($result)) {
		message('修改失败', referer(), 'error');
	} else {
		message('修改成功', referer(), 'success');
	}
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	$package_info = pdo_get('storex_sales_package', array('id' => $id, 'uniacid' => $_W['uniacid']), array('id', 'status'));
	if (empty($package_info)) {
		message('套餐信息错误', referer(), 'error');
	}
	pdo_delete('storex_sales_package', array('id' => $id, 'uniacid' => $_W['uniacid']));
	message('删除成功', referer(), 'success');
}

include $this->template('store/shop_sales_package');