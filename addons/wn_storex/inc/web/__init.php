<?php
defined('IN_IA') or exit('Access Denied');

global $_GPC, $_W;

if ($_GPC['do'] == 'shop_settings' || $_GPC['do'] == 'shop_category' || $_GPC['do'] == 'shop_goodsmanage' || $_GPC['do'] == 'shop_comment' || $_GPC['do'] == 'shop_index' || $_GPC['do'] == 'shop_order' || $_GPC['do'] == 'shop_stat') {
	if (empty($_GPC['storeid'])) {
		message('请重新选择店铺', $this->createWebUrl('storemanage', array('op' => 'list')), 'error');
	}
	$storex_bases = pdo_get('storex_bases', array('id' => $_GPC['storeid']));
	$_W['wn_storex']['store_info'] = $storex_bases;
}
//店铺后台菜单设置
$aside_nav = array(
	'shop_index' => array(
		'title' => '概况',
		'url' => $this->createWebUrl('shop_index', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-columns'
	),
	'shop_category' => array(
		'title' => '商品分类',
		'url' => $this->createWebUrl('shop_category', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-bars'
	),
	'shop_goodsmanage' => array(
		'title' => '商品管理',
		'url' => $this->createWebUrl('shop_goodsmanage', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-gift'
	),
	'shop_comment' => array(
		'title' => '商品评价',
		'url' => $this->createWebUrl('shop_comment', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-search'
	),
	'shop_order' => array(
		'title' => '店铺订单',
		'url' => $this->createWebUrl('shop_order', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-search'
	),
	'shop_stat' => array(
		'title' => '数据统计',
		'url' => $this->createWebUrl('shop_stat', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-area-chart'
	),
	'shop_plugin' => array(
		'title' => '应用',
		'url' => $this->createWebUrl('shop_plugin', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-th-large'
	),
	'shop_settings' => array(
		'title' => '店铺设置',
		'url' => $this->createWebUrl('shop_settings', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-cogs'
	),
);
