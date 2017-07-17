<?php
defined('IN_IA') or exit('Access Denied');

global $_GPC, $_W;

$dos = array('shop_settings', 'shop_category', 'shop_goodsmanage', 'shop_comment', 'shop_index', 'shop_order', 'shop_stat', 'shop_plugin', 'shop_plugin_printer');
if (in_array($_GPC['do'], $dos)) {
	if (empty($_GPC['storeid']) && !($_GPC['do'] == 'shop_settings' && $_GPC['action'] == 'add')) {
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
		'icon' => 'storex-shop',
		'active' => array(
			'shop_index',
		)
	),
	'shop_homepage' => array(
		'title' => '首页设置',
		'url' => $this->createWebUrl('shop_homepage', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-shop',
		'active' => array(
			'shop_homepage'
		)
	),
	'shop_category' => array(
		'title' => '商品分类',
		'url' => $this->createWebUrl('shop_category', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-category-selected',
		'active' => array(
			'shop_category',
		)
	),
	'shop_goodsmanage' => array(
		'title' => '商品管理',
		'url' => $this->createWebUrl('shop_goodsmanage', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-gift',
		'active' => array(
			'shop_goodsmanage',
			'shop_room_status',
			'shop_room_price',
		)
	),
	'shop_comment' => array(
		'title' => '商品评价',
		'url' => $this->createWebUrl('shop_comment', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-search',
		'active' => array(
			'shop_comment',
		)
	),
	'shop_order' => array(
		'title' => '店铺订单',
		'url' => $this->createWebUrl('shop_order', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-order',
		'active' => array(
			'shop_order',
		)
	),
	'shop_stat' => array(
		'title' => '数据统计',
		'url' => $this->createWebUrl('shop_stat', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-sign',
		'active' => array(
			'shop_stat',
		)
	),
	'shop_plugin' => array(
		'title' => '应用',
		'url' => $this->createWebUrl('shop_plugin', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-tag',
		'active' => array(
			'shop_plugin',
			'shop_plugin_printer'
		)
	),
	'shop_settings' => array(
		'title' => '店铺设置',
		'url' => $this->createWebUrl('shop_settings', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-set',
		'active' => array(
			'shop_settings',
		)
	),
);
if (!check_ims_version()) {
	unset($aside_nav['shop_plugin']);
	if ($_GPC['do'] == 'shop_plugin' || $_GPC['do'] == 'shop_plugin_printer') {
		message('请升级微擎系统至1.0以上，并保持最新版本', '', 'error');
	}
}