<?php
defined('IN_IA') or exit('Access Denied');

global $_GPC, $_W;

$dos = array('shop_settings', 'shop_category', 'shop_goodsmanage', 'shop_comment', 'shop_index', 'shop_order', 'shop_stat', 'shop_plugin', 'shop_plugin_printer', 'shop_homepage', 'shop_member', 'shop_clerk');
if (in_array($_GPC['do'], $dos)) {
	if (empty($_GPC['storeid']) && !($_GPC['do'] == 'shop_settings' && $_GPC['action'] == 'add')) {
		message('请重新选择店铺', $this->createWebUrl('storemanage', array('op' => 'list')), 'error');
	}
	$storex_bases = pdo_get('storex_bases', array('id' => $_GPC['storeid']));
	$_W['wn_storex']['store_info'] = $storex_bases;
	if (empty($_W['wn_storex']['store_info']['store_type'])) {
		$_W['wn_storex']['goods_table'] = 'storex_goods';
		$_W['wn_storex']['table_storeid'] = 'store_base_id';
	} elseif ($_W['wn_storex']['store_info']['store_type'] == 1) {
		$_W['wn_storex']['goods_table'] = 'storex_room';
		$_W['wn_storex']['table_storeid'] = 'hotelid';
	}
}
//店铺后台菜单设置
$aside_nav = array(
	'shop_index' => array(
		'title' => '概况',
		'url' => $this->createWebUrl('shop_index', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-index',
		'active' => array(
			'shop_index',
		)
	),
	'shop_homepage' => array(
		'title' => '首页设置',
		'url' => $this->createWebUrl('shop_homepage', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-home',
		'active' => array(
			'shop_homepage'
		)
	),
	'shop_category' => array(
		'title' => '商品分类',
		'url' => $this->createWebUrl('shop_category', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-category',
		'active' => array(
			'shop_category',
		)
	),
	'shop_goodsmanage' => array(
		'title' => '商品管理',
		'url' => $this->createWebUrl('shop_goodsmanage', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-goods',
		'active' => array(
			'shop_goodsmanage',
			'shop_room_status',
			'shop_room_price',
		)
	),
	'shop_comment' => array(
		'title' => '商品评价',
		'url' => $this->createWebUrl('shop_comment', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-comment',
		'active' => array(
			'shop_comment',
		)
	),
	'shop_order' => array(
		'title' => '店铺订单',
		'url' => $this->createWebUrl('shop_order', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-order',
		'active' => array(
			'shop_order',
		)
	),
	'shop_stat' => array(
		'title' => '数据统计',
		'url' => $this->createWebUrl('shop_stat', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-stat',
		'active' => array(
			'shop_stat',
		)
	),
	'shop_member' => array(
		'title' => '用户管理',
		'url' => $this->createWebUrl('shop_member', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-user',
		'active' => array(
			'shop_member',
		)
	),
	'shop_clerk' => array(
		'title' => '店员管理',
		'url' => $this->createWebUrl('shop_clerk', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-clerk',
		'active' => array(
			'shop_clerk',
		)
	),
	'shop_plugin' => array(
		'title' => '应用',
		'url' => $this->createWebUrl('shop_plugin', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-app',
		'active' => array(
			'shop_plugin',
			'shop_plugin_printer'
		)
	),
	'shop_settings' => array(
		'title' => '店铺设置',
		'url' => $this->createWebUrl('shop_settings', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-setting',
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