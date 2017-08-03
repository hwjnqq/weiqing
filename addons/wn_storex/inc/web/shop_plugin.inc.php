<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$store_info = $_W['wn_storex']['store_info'];
if ($op == 'display') {
	load()->model('module');
	$hotel_service = module_fetch('wn_storex_plugin_hotel_service');
	$hotel_service_show = false;
	if (!empty($hotel_service) && $hotel_service['enabled'] == 1) {
		$hotel_service_show = true;
	}
}
include $this->template('store/shop_plugin');