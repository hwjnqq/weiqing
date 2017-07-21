<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

check_params();
$storeid = intval($_GPC['id']);
$store_info = get_store_info($storeid);
if ($op == 'display') {
	$default_module = array(
		array(
			'type' => 'search',
			'items' => array()
		),
		array(
			'type' => 'slide',
			'items' => array()
		),
		array(
			'type' => 'notice',
			'items' => array()
		),
		array(
			'type' => 'nav',
			'items' => array()
		),
		array(
			'type' => 'cube',
			'items' => array()
		),
		array(
			'type' => 'nav',
			'items' => array()
		),
		array(
			'type' => 'recommend',
			'items' => array()
		),
	);
	$homepage_list = pdo_getall('storex_homepage', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array(), 'displayorder', 'displayorder ASC');
	if (!empty($homepage_list) && is_array($homepage_list)) {
		foreach ($homepage_list as $key => &$value) {
			unset($value['id'], $value['displayorder'], $value['uniacid'], $value['storeid']);
			$value['items'] = !empty($value['items']) ? iunserializer($value['items']) : '';
			if ($value['type'] == 'recommend') {
				$recommend_key = $key;
				$recommend_info = $value;
			}
		}
		$tablaname = gettablebytype($store_info['store_type']);
		if (!empty($recommend_info['items']) && is_array($recommend_info['items'])) {
			$goodslist = pdo_getall($tablaname, array('id' => array_values($recommend_info['items'])), array('id', 'thumb', 'title', 'cprice'), 'id');
			foreach ($recommend_info['items'] as $key => &$value) {
				$value = $goodslist[$value];
				$value['thumb'] = tomedia($value['thumb']);
			}
		}
		$homepage_list[$recommend_key] = $recommend_info;
	} else {
		$homepage_list = $default_module;
	}
	message(error(0, $homepage_list), '', 'ajax');
}
