<?php
defined('IN_IA') or exit('Access Denied');
global $_GPC, $_W;
define('STORE_TYPE_NORMAL', '0');
define('STORE_TYPE_HOTEL', '1');

$aside__nav = array(
	'goods_category' => array(
		'do' => 'goods_category',
		'title' => '商品分类',
		'param' => array(
			'storeid' => intval($_GPC['storeid']),
		),
	),
	'goods_manage' => array(
		'do' => 'goods_manage',
		'title' => '商品管理',
		'param' => array(
			'storeid' => intval($_GPC['storeid']),
		),
	),
	'storebackstage' => array(
		'do' => 'storebackstage',
		'title' => '店铺设置',
		'param' => array(
			'storeid' => intval($_GPC['storeid']),
			'op' => 'post',
			'store_type' => $_GPC['store_type'],
		),
	),
);

if ($_GPC['do'] == 'storebackstage' || $_GPC['do'] == 'goods_category' || $_GPC['do'] == 'goods_manage' || $_GPC['do'] == 'goods_comment') {
	if (empty($_GPC['storeid'])) {
		message('请重新选择店铺', $this->createWebUrl('storemanage', array('op' => 'list')), 'error');
	}
	$storex_bases = pdo_get('storex_bases', array('id' => $_GPC['storeid']));
	$_W['wn_storex']['store_info'] = $storex_bases;
}

