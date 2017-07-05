<?php
defined('IN_IA') or exit('Access Denied');

global $_GPC, $_W;
define('STORE_TYPE_NORMAL', '0');
define('STORE_TYPE_HOTEL', '1');

if ($_GPC['do'] == 'shop_settings' || $_GPC['do'] == 'shop_category' || $_GPC['do'] == 'shop_manage' || $_GPC['do'] == 'shop_comment' || $_GPC['do'] == 'shop_index') {
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
		'icon' => 'fa fa-scan'
	),
	'shop_category' => array(
		'title' => '商品分类',
		'url' => $this->createWebUrl('shop_category', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-search'
	),
	'shop_manage' => array(
		'title' => '商品管理',
		'url' => $this->createWebUrl('shop_manage', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-search'
	),
	'shop_settings' => array(
		'title' => '店铺设置',
		'url' => $this->createWebUrl('shop_settings', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-search'
	),
);
