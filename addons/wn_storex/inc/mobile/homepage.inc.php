<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display', 'goods_search');
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
	$share_data = array(
		'title' => $store_info['title'],
		'desc' => $store_info['title'] . '--万能小店',
		'link' => murl('entry', array('do' => 'display', 'id' => $storeid, 'm' => 'wn_storex', 'type' => 'storeindex'), true, true),
		'imgUrl' => tomedia($store_info['thumb'])
	);
	wmessage(error(0, $homepage_list), $share_data, 'ajax');
}

if ($op == 'goods_search') {
	$id = intval($_GPC['id']);
	$keywords = trim($_GPC['keywords']);
	$store = get_store_info($id);
	$table = gettablebytype($store['store_type']);
	$condition = array('title LIKE' => '%' . $keywords . '%', 'status' => 1);
	$fields = array('id', 'title', 'thumb', 'thumbs', 'sub_title', 'oprice', 'cprice', 'device');
	if ($table == 'storex_room') {
		$condition['is_house !='] = 1;
		$condition['hotelid'] = $id;
		$fields[] = 'hotelid';
		$fields[] = 'is_house';
	} else {
		$fields[] = 'store_base_id';
		$condition['store_base_id'] = $id;
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 1;
	
	$goods = pdo_getall($table, $condition, $fields, '', 'displayorder DESC', array($pindex, $psize));
	$total = count(pdo_getall($table, $condition));
	if (!empty($goods) && is_array($goods)) {
		foreach ($goods as &$info) {
			if (!empty($info['thumb'])) {
				$info['thumb'] = tomedia($info['thumb']);
			}
			if (!empty($info['thumbs'])) {
				$info['thumbs'] = format_url(iunserializer($info['thumbs']));
			}
		}
		unset($info);
	}
	$page_array = get_page_array($total, $pindex, $psize);
	$list = array(
		'list' => $goods,
		'psize' => $psize,
		'result' => 1,
		'total' => $total,
		'isshow' => $page_array['isshow'],
		'nindex' => $page_array['nindex'],
	);
	wmessage(error(0, $list), '', 'ajax');
}