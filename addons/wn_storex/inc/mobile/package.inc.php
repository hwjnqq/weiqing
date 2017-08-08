<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display', 'register', 'apply');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

check_params();
load()->model('mc');
$uid = mc_openid2uid($_W['openid']);

if ($op == 'display') {
	$package_list = pdo_getall('storex_sales_package', array('uniacid' => $_W['uniacid'], 'storeid' => $_GPC['storeid']));
	$goods_list = pdo_getall('storex_goods', array('weid' => $_W['uniacid'], 'store_base_id' => $_GPC['storeid']), array('id', 'title', 'sub_title', 'thumb', 'oprice'), 'id');
	if (!empty($goods_list) && is_array($goods_list)) {
		foreach ($goods_list as &$goods) {
			$goods['thumb'] = tomedia($goods['thumb']);
		}
		unset($goods);
	}
	if (!empty($package_list) && is_array($package_list)) {
		foreach ($package_list as &$value) {
			$value['goodsids'] = iunserializer($value['goodsids']);
			foreach ($value['goodsids'] as $key => $id) {
				$value['goods_list'][$key] = $goods_list[$id];
			}
		}
		unset($value);
	}
	wmessage(error(0, $package_list), '', 'ajax');
}
