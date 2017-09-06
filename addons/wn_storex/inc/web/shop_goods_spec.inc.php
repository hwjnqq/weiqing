<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_GPC['storeid']);
$store = $_W['wn_storex']['store_info'];
$store_type = $store['store_type'];
if ($store_type == STORE_TYPE_HOTEL) {
	message('参数错误', referer(), 'error');
}

if ($op == 'display') {
	$goodsid = intval($_GPC['id']);
	$goods_info = pdo_get('storex_goods', array('store_base_id' => $storeid, 'weid' => $_W['uniacid'], 'id' => $goodsid));
	$categoryid = intval($_GPC['categoryid']);
	$category_info = pdo_get('storex_categorys', array('weid' => $_W['uniacid'], 'store_base_id' => $storeid, 'id' => $categoryid), array('spec', 'id'));
	$category_spec = iunserializer($category_info['spec']);
	if (is_array($category_spec)) {
		$spec_name = pdo_getall('storex_spec', array('id' => $category_spec), array('id', 'name'), 'id');
		$spec_value = pdo_getall('storex_spec_value', array('specid' => $category_spec), array('id', 'name', 'displayorder', 'specid'), '', 'displayorder DESC');
	}
	if (!empty($spec_value) && is_array($spec_value)) {
		foreach ($spec_value as $key => $value) {
			$spec_list[$value['specid']]['name'] = $spec_name[$value['specid']]['name'];
			$spec_list[$value['specid']]['values'][$key] = array(
				'id' => $value['id'],
				'name' => $value['name'],
				'displayorder' => $value['displayorder'],
				'specid' => $value['specid']
			);
		}
	}
}

include $this->template('store/shop_goods_spec');