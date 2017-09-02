<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display', 'notice');
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
	$homepage_list = pdo_getall('storex_homepage', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'is_wxapp' => 1), array(), 'displayorder', 'displayorder ASC');
	if (!empty($homepage_list) && is_array($homepage_list)) {
		foreach ($homepage_list as $key => &$value) {
			unset($value['displayorder'], $value['uniacid'], $value['storeid']);
			$value['items'] = !empty($value['items']) ? iunserializer($value['items']) : '';
			if ($value['type'] == 'recommend') {
				$recommend_key = $key;
				$recommend_info = $value;
			}
			if ($value['type'] == 'footer') {
				unset($homepage_list[$key]);
			}
		}
		$tablaname = gettablebytype($store_info['store_type']);
		if (!empty($recommend_info['items']) && is_array($recommend_info['items'])) {
			$fields = array('id', 'title', 'thumb', 'cprice', 'store_type', 'sub_title', 'oprice', 'sold_num');
			if ($store_info['store_type'] == 1) {
				$fields[] = 'is_house';
			}
			$goodslist = pdo_getall($tablaname, array('id' => array_values($recommend_info['items'])), $fields, 'id');
			foreach ($recommend_info['items'] as $key => &$value) {
				if (!empty($value)) {
					$value = $goodslist[$value];
					$value['thumb'] = tomedia($value['thumb']);
					$value['type'] = 2;
					if ($value['store_type'] == 1 && isset($value['is_house']) && $value['is_house'] == 1) {
						$value['type'] = 1;
					}
				} else {
					unset($recommend_info['items'][$key]);
				}
			}
		}
		if (!empty($recommend_key)) {
			$homepage_list[$recommend_key] = $recommend_info;
		}
	} else {
		$homepage_list = $default_module;
	}
	wmessage(error(0, $homepage_list), '', 'ajax');
}

if ($op == 'notice') {
	$noticeid = $_GPC['noticeid'];
	$noticekey = $_GPC['noticekey'];
	$notice_info = pdo_get('storex_homepage', array('id' => $noticeid, 'uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'type' => 'notice', 'is_wxapp' => 1));
	if (!empty($notice_info) && !empty($notice_info['items'])) {
		$notice_info['items'] = iunserializer($notice_info['items']);
		if (!empty($notice_info['items'][$noticekey])) {
			$article = pdo_get('storex_article', array('id' => $notice_info['items'][$noticekey]['id']));
			if (!empty($article)) {
				$article['thumb'] = tomedia($article['thumb']);
			} else {
				$article = array();
			}
			wmessage(error(0, $article), '', 'ajax');
		}
	}
	wmessage(error(0, array()), '', 'ajax');
}