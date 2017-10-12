<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display', 'notice');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'error';

check_params();
$storeid = $_GPC['id'];
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
			'type' => 'adv',
			'items' => array()
		),
		array(
			'type' => 'activity_seckill',
			'items' => array()
		),
		array(
			'type' => 'activity_limited',
			'items' => array()
		)
	);
	$homepage_list = pdo_getall('storex_homepage', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'is_wxapp' => 1), array(), 'displayorder', 'displayorder ASC');
	$activity_list = pdo_getall('storex_goods_activity', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'endtime >' => TIMESTAMP), array(), 'id');
	if (!empty($activity_list) && is_array($activity_list)) {
		foreach ($activity_list as $key => $value) {
			if ($value['is_spec'] == 1) {
				$spec_ids[$key] = $value['specid'];
			} else {
				$not_spec_ids[$key] = $value['goodsid'];
			}
			if ($value['type'] == 1) {
				$activity_seckill[] = $value;
			} elseif ($value['type'] == 2) {
				$activity_limited[] = $value;
			}
		}
	}
	$spec_goods = pdo_getall('storex_spec_goods', array('id' => $spec_ids), array('id', 'goodsid', 'title', 'goods_val', 'oprice', 'cprice', 'stock', 'thumb'), 'id');
	$not_spec_goods = pdo_getall('storex_goods', array('id' => $not_spec_ids), array('id', 'title', 'oprice', 'cprice', 'stock', 'thumb'), 'id');
	if (!empty($spec_goods) && is_array($spec_goods)) {
		foreach ($spec_goods as &$goods) {
			$goods['thumb'] = tomedia($goods['thumb']);
			$goods['goods_val'] = iunserializer($goods['goods_val']);
			$goods['is_spec'] = 1;
		}
		unset($goods);
	}
	if (!empty($not_spec_goods) && is_array($not_spec_goods)) {
		foreach ($not_spec_goods as &$val) {
			$val['goodsid'] = $val['id'];
			$val['thumb'] = tomedia($val['thumb']);
			$val['is_spec'] = 2;
		}
		unset($val);
	}
	if (!empty($activity_seckill) && is_array($activity_seckill)) {
		foreach ($activity_seckill as $list) {
			if ($list['is_spec'] == 1) {
				$goods_val = implode(' ', $spec_goods[$list['specid']]['goods_val']);
				$spec_goods[$list['specid']]['cprice'] = $list['price'];
				$spec_goods[$list['specid']]['nums'] = $list['nums'];
				$spec_goods[$list['specid']]['title'] .= ' ' . $goods_val;
				$spec_goods[$list['specid']]['specid'] = $list['specid'];
				$seckill_list[] = $spec_goods[$list['specid']];
			} else {
				$not_spec_goods[$list['goodsid']]['nums'] = $list['nums'];
				$seckill_list[] = $not_spec_goods[$list['goodsid']];
			}
		}
	}
	if (!empty($activity_limited) && is_array($activity_limited)) {
		foreach ($activity_limited as $list) {
			if ($list['is_spec'] == 1) {
				$goods_val = implode(' ', $spec_goods[$list['specid']]['goods_val']);
				$spec_goods[$list['specid']]['cprice'] = $list['price'];
				$spec_goods[$list['specid']]['nums'] = $list['nums'];
				$spec_goods[$list['specid']]['title'] .= ' ' . $goods_val;
				$spec_goods[$list['specid']]['specid'] = $list['specid'];
				$limited_list[] = $spec_goods[$list['specid']];
			} else {
				$limited_list[] = $not_spec_goods[$list['goodsid']];
			}
		}
	}
	if (!empty($homepage_list) && is_array($homepage_list)) {
		foreach ($homepage_list as $key => &$value) {
			unset($value['displayorder'], $value['uniacid'], $value['storeid']);
			$value['items'] = !empty($value['items']) ? iunserializer($value['items']) : '';
			if ($value['type'] == 'recommend') {
				if ($store_info['store_type'] != STORE_TYPE_HOTEL) {
					$recommend_key = $key;
					$recommend_info = $value;
				} else {
					unset($homepage_list[$key]);
				}
			}
			if ($value['type'] == 'footer') {
				unset($homepage_list[$key]);
			}
			if ($value['type'] == 'activity_seckill') {
				$value['items'] = $seckill_list;
			}
			if ($value['type'] == 'activity_limited') {
				$value['items'] = $limited_list;
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
	$share_data = array(
		'title' => $store_info['title'],
		'desc' => $store_info['title'] . '--万能小店',
		'link' => murl('entry', array('do' => 'display', 'id' => $storeid, 'm' => 'wn_storex', 'type' => 'storeindex'), true, true),
		'imgUrl' => tomedia($store_info['thumb'])
	);
	$share_data = get_share_data('homepage', array('storeid' => $storeid), $share_data);
	wmessage(error(0, $homepage_list), $share_data, 'ajax');
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