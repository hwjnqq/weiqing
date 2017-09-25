<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'add_cart', 'update_cart');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'error';

check_params();
load()->model('mc');
$uid = mc_openid2uid($_W['openid']);
$storeid = intval($_GPC['id']);
$store_info = get_store_info($storeid);
if ($op == 'display') {
	$cart_info = pdo_get('storex_cart', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'uid' => $uid));
	if (!empty($cart_info)) {
		$goods_list = iunserializer($cart_info['goods']);
		if (!empty($goods_list) && is_array($goods_list)) {
			foreach ($goods_list as $key => $value) {
				if ($value['is_spec'] == 1) {
					$spec_goodsids[] = $value['id'];
				} elseif ($value['is_spec'] == 2) {
					$not_spec_goodsids[] = $value['id'];
				} elseif ($value['is_spec'] == 3) {
					$packageids[] = $value['id'];
				}
			}
		}
		$base_goods = pdo_getall('storex_goods', array('id' => $not_spec_goodsids), array('title', 'oprice', 'sub_title', 'thumb', 'id', 'cprice'), 'id');
		$spec_goods = pdo_getall('storex_spec_goods', array('id' => $spec_goodsids), array('title', 'oprice', 'sub_title', 'thumb', 'goods_val', 'id', 'goodsid', 'cprice'), 'id');
		$package_goods = pdo_getall('storex_sales_package', array('id' => $packageids), array('title', 'price', 'title', 'sub_title', 'thumb', 'express', 'id'), 'id');
		if (!empty($base_goods) && is_array($base_goods)) {
			foreach ($base_goods as &$value) {
				$value['thumb'] = tomedia($value['thumb']);
			}
			unset($value);
		}
		if (!empty($spec_goods) && is_array($spec_goods)) {
			foreach ($spec_goods as &$val) {
				$val['thumb'] = tomedia($val['thumb']);
				$goods_val = iunserializer($val['goods_val']);
				$val['spec_title'] .= ' ' . implode(' ', $goods_val);
				$spec_info = get_spec_list($val['goodsid'], $storeid);
				$val['sp_name'] = $spec_info['sp_name'];
				$val['sp_val'] = $spec_info['sp_val'];
				$val['spec_list'] = $spec_info['spec_list'];
			}
			unset($val);
		}

		if (!empty($package_goods) && is_array($package_goods)) {
			foreach ($package_goods as &$package) {
				$package['oprice'] = $package['cprice'] = $package['price'];
			}
			unset($package);
		}
		if (!empty($goods_list) && is_array($goods_list)) {
			foreach ($goods_list as $id => &$goods) {
				if ($goods['is_spec'] == 1) {
					$goods['goods_info'] = $spec_goods[$id];
				} elseif ($goods['is_spec'] == 2) {
					$goods['goods_info'] = $base_goods[$id];
				} elseif ($goods['is_spec'] == 3) {
					$goods['goods_info'] = $package_goods[$id];
				}
			}
			unset($goods);
		}
		$cart_info['goods'] = array_values($goods_list);
	} else {
		$cart_info = array();
	}
	wmessage(error(0, $cart_info), '', 'ajax');
}

if ($op == 'add_cart') {
	$is_spec = intval($_GPC['is_spec']);
	$goodsid = intval($_GPC['goodsid']);
	if ($is_spec == 1) {
		$goods_info = pdo_get('storex_spec_goods', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'id' => $goodsid), array('title', 'id', 'goodsid', 'sub_title', 'goods_val', 'thumb', 'oprice', 'cprice', 'stock'));
		$goods_info['is_spec'] = 1;
	} elseif ($is_spec == 2) {
		$goods_info = pdo_get('storex_goods', array('weid' => $_W['uniacid'], 'store_base_id' => $storeid, 'id' => $goodsid), array('title', 'sub_title', 'thumb', 'oprice', 'cprice', 'id'));
		$goods_info['is_spec'] = 2;
	} elseif ($is_spec == 3) {
		$goods_info = pdo_get('storex_sales_package', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'id' => $goodsid), array('title', 'sub_title', 'thumb', 'price', 'express', 'id'));
		$goods_info['oprice'] = $goods_info['cprice'] = $goods_info['price'];
		$goods_info['is_spec'] = 3;
	}
	if (!empty($goods_info)) {
		$cart_info = pdo_get('storex_cart', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'uid' => $uid));
		if (empty($cart_info)) {
			$goods[$goods_info['id']] = array(
				'is_spec' => $goods_info['is_spec'],
				'id' => $goods_info['id'],
				'nums' => 1
			);
			$cart_goods = array(
				'uniacid' => $_W['uniacid'],
				'storeid' => $storeid,
				'uid' => $uid,
				'total' => 1,
				'total_price' => $goods_info['oprice'],
				'goods' => iserializer($goods)
			);
			pdo_insert('storex_cart', $cart_goods);
		} else {
			$cart_goods = array(
				'total' => $cart_info['total'],
				'total_price' => $cart_info['total_price'],
			);
			$goods = iunserializer($cart_info['goods']);
			if (!empty($goods) && is_array($goods)) {
				foreach ($goods as $key => &$value) {
					if ($goods_info['id'] == $key) {
						$value['nums']++;

					} else {
						$goods[$goods_info['id']] = array(
							'is_spec' => $goods_info['is_spec'],
							'id' => $goods_info['id'],
							'nums' => 1
						);
					}
					$cart_goods['total']++;
				}
				unset($value);
				$cart_goods['total_price'] += $goods_info['cprice'];
				$cart_goods['goods'] = iserializer($goods);
			}
			pdo_update('storex_cart', $cart_goods, array('id' => $cart_info['id']));
		}
	}
	wmessage(error(0, '加入购物车成功'), '', 'ajax');
}

if ($op == 'update_cart') {
	$cart_list = $_GPC['cart_list'];
	$cart_list = explode(',', $cart_list);
	if (!empty($cart_list) && is_array($cart_list)) {
		foreach ($cart_list as $key => $value) {
			$cart_info = explode('|', $value);
			$goods[$cart_info[0]] = array(
				'is_spec' => $cart_info[2],
				'id' => $cart_info[0],
				'nums' => $cart_info[1]
			);
			$total += $cart_info[1];
			if ($cart_info[2] == 1) {
				$spec_goodsids[] = $cart_info[0];
			} elseif ($cart_info[2] == 2) {
				$base_goodsids[] = $cart_info[0];
			} elseif ($cart_info[2] == 3) {
				$packageids[] = $cart_info[0];
			}
		}
		$base_goods = pdo_getall('storex_goods', array('id' => $base_goodsids), array('title', 'oprice', 'sub_title', 'thumb', 'id'), 'id');
		$spec_goods = pdo_getall('storex_spec_goods', array('id' => $spec_goodsids), array('title', 'oprice', 'sub_title', 'thumb', 'goods_val', 'id', 'goodsid'), 'id');
		$package_goods = pdo_getall('storex_sales_package', array('id' => $packageids), array('title', 'price', 'title', 'sub_title', 'thumb', 'express', 'id'), 'id');
		$total_price = 0;
		if (!empty($goods) && is_array($goods)) {
			foreach ($goods as $k => &$val) {
				if ($val['is_spec'] == 1) {
					$val['price'] = $spec_goods[$k]['oprice'] * $val['nums'];
				} elseif ($val['is_spec'] == 2) {
					$val['price'] = $base_goods[$k]['oprice'] * $val['nums'];
				} elseif ($val['is_spec'] == 3) {
					$val['price'] = $package_goods[$k]['price'] * $val['nums'];
				}
				$total_price += $val['price'];
				unset($val['price']);
			}
			unset($val);
		}
		pdo_update('storex_cart', array('goods' => iserializer($goods), 'total' => $total, 'total_price' => $total_price), array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'uid' => $uid));
	}
	wmessage(error(0, '更新成功'), '', 'ajax');
}

function get_spec_list($goodsid, $storeid) {
	global $_W;
	$spec_list = pdo_getall('storex_spec_goods', array('goodsid' => $goodsid, 'storeid' => $storeid, 'uniacid' => $_W['uniacid']));
	if (!empty($spec_list)) {
		$spec_goods_list = array();
		if (!empty($spec_list) && is_array($spec_list)) {
			foreach ($spec_list as $k => $val) {
				$goods_info['sp_name'] = iunserializer($val['sp_name']);
				$goods_info['sp_val'] = iunserializer($val['sp_val']);
				$goods_val = iunserializer($val['goods_val']);
				if (!empty($goods_val) && is_array($goods_val)) {
					foreach ($goods_val as $key => $value) {
						$goods_val_keys = array_keys($goods_val);
						$goods_val_keys = implode('|', $goods_val_keys);
						$spec_goods_list[$goods_val_keys] = $val['id'];
					}
				}
			}
		}
		$goods_info['spec_list'] = $spec_goods_list;
		return $goods_info;
	}
}