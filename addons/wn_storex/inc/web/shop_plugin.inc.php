<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$store_info = $_W['wn_storex']['store_info'];
if ($op == 'display') {
	$hotel_service_show = false;
	load()->model('module');
	$hotel_service = module_fetch('wn_storex_plugin_hotel_service');
	if (!empty($hotel_service) && $hotel_service['enabled'] == 1) {
		$hotel_service_show = true;
	}
}
include $this->template('store/shop_plugin');