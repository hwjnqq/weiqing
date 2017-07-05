<?php
defined('IN_IA') or exit('Access Denied');
global $_GPC, $_W;
define('STORE_TYPE_NORMAL', '0');
define('STORE_TYPE_HOTEL', '1');

if ($_GPC['do'] == 'storebackstage' || $_GPC['do'] == 'goods_category' || $_GPC['do'] == 'goods_manage') {
	if (empty($_GPC['storeid'])) {
		message('请重新选择店铺', $this->createWebUrl('storemanage', array('op' => 'list')), 'error');
	}
	$storex_bases = pdo_get('storex_bases', array('id' => $_GPC['storeid']));
	$_W['wn_storex']['store_info'] = $storex_bases;
}

