<?php
defined('IN_IA') or exit('Access Denied');

global $_GPC, $_W;
define('STORE_TYPE_NORMAL', '0');
define('STORE_TYPE_HOTEL', '1');

if ($_GPC['do'] == 'storebackstage' || $_GPC['do'] == 'goods_category' || $_GPC['do'] == 'goods_manage' || $_GPC['do'] == 'goods_comment') {
	if (empty($_GPC['storeid'])) {
		message('请重新选择店铺', $this->createWebUrl('storemanage', array('op' => 'list')), 'error');
	}
	$storex_bases = pdo_get('storex_bases', array('id' => $_GPC['storeid']));
	$_W['wn_storex']['store_info'] = $storex_bases;
}
//店铺后台菜单设置
$aside_nav = array(
	'goods_category' => array(
		'title' => '商品分类',
		'url' => $this->createWebUrl('goods_category', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-search'
	),
	'goods_manage' => array(
		'title' => '商品管理',
		'url' => $this->createWebUrl('goods_manage', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-search'
	),
	'storebackstage' => array(
		'title' => '店铺设置',
		'url' => $this->createWebUrl('storebackstage', array('storeid' => $_GPC['storeid'])),
		'icon' => 'fa fa-search'
	),
);
