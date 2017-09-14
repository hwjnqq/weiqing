<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display', 'post');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_GPC['storeid']);
$store = $_W['wn_storex']['store_info'];
$store_type = $store['store_type'];
$goodsid = intval($_GPC['id']);
$goods_info = pdo_get('storex_goods', array('store_base_id' => $storeid, 'weid' => $_W['uniacid'], 'id' => $goodsid));
if ($store_type == STORE_TYPE_HOTEL) {
	itoast('参数错误', referer(), 'error');
}
if (!empty($goods_info)) {
	$thumb = tomedia($goods_info['thumb']);
}
if ($op == 'display') {
	$categoryid = intval($_GPC['categoryid']);
	$category_info = pdo_get('storex_categorys', array('weid' => $_W['uniacid'], 'store_base_id' => $storeid, 'id' => $categoryid), array('spec', 'id', 'name'));
	$category_spec = iunserializer($category_info['spec']);
	if (is_array($category_spec)) {
		$spec_name = pdo_getall('storex_spec', array('id' => $category_spec), array('id', 'name'), 'id');
		$spec_value = pdo_getall('storex_spec_value', array('specid' => $category_spec), array('id', 'name', 'displayorder', 'specid'), 'id', 'displayorder DESC');
	}
	if (!empty($spec_value) && is_array($spec_value)) {
		foreach ($spec_value as $key => $value) {
			$spec_list[$value['specid']]['name'] = $spec_name[$value['specid']]['name'];
			$spec_list[$value['specid']]['values'][$key] = array(
				'id' => $value['id'],
				'name' => $value['name'],
				'displayorder' => $value['displayorder'],
				'specid' => $value['specid']
			);
		}
	}

	$spec_goods_list = pdo_getall('storex_spec_goods', array('storeid' => $storeid, 'uniacid' => $_W['uniacid'], 'goodsid' => $goodsid));
	if (!empty($spec_goods_list) && is_array($spec_goods_list)) {
		foreach ($spec_goods_list as $k => $val) {
			$goods_list['sp_name'] = iunserializer($val['sp_name']);
			$goods_list['sp_val'] = iunserializer($val['sp_val']);
			$goods_val = iunserializer($val['goods_val']);
			if (!empty($goods_val) && is_array($goods_val)) {
				foreach ($goods_val as $key => $value) {
					$goods_val_keys = array_keys($goods_val);
					$goods_val_keys = 'i_' . implode('_', $goods_val_keys);
					$goods_list['spec'][$goods_val_keys] = array(
						'goodsid' => $val['id'],
						'sp_value' => $goods_val,
						'cprice' => $val['cprice'],
						'oprice' => $val['oprice'],
						'stock' => $val['stock'],
						'thumb' => tomedia($val['thumb']),
					);
				}
			}
		}
	}
}

if ($op == 'post') {
	if (checksubmit()) {
		$commonid = intval($_GPC['commonid']);
		$common_info = pdo_get('storex_goods', array('store_base_id' => $_GPC['storeid'], 'weid' => $_W['uniacid'], 'id' => $commonid));
		$spec_goods = array(
			'storeid' => $storeid,
			'uniacid' => $_W['uniacid'],
			'goodsid' => $commonid,
			'title' => $common_info['title'],
			'sub_title' => $common_info['sub_title'],
			'pcate' => $common_info['pcate'],
			'ccate' => $common_info['ccate'],
			'sp_name' => iserializer($_GPC['sp_name']),
			'sp_val' => iserializer($_GPC['sp_val']),
		);
		$goods_list = $_GPC['spec'];
		if (!empty($goods_list) && is_array($goods_list)) {
			$all_spec_list = pdo_getall('storex_spec_goods', array('storeid' => $storeid, 'uniacid' => $_W['uniacid'], 'goodsid' => $commonid), array('id'), 'id');
			$key_list = is_array($all_spec_list) ? array_keys($all_spec_list) : array();
			$goodsids = array();
			foreach ($goods_list as $key => $value) {
				if (in_array($value['goodsid'], $key_list)) {
					$goodsids[] = $value['goodsid'];
				}
				if (empty($value['goodsid'])) {
					$spec_goods['goods_val'] = iserializer($value['sp_value']);
					$spec_goods['cprice'] = $value['cprice'];
					$spec_goods['oprice'] = $value['oprice'];
					$spec_goods['stock'] = $value['stock'];
					$spec_goods['thumb'] = $value['thumb'];
					pdo_insert('storex_spec_goods', $spec_goods);
				} else {
					pdo_update('storex_spec_goods', array('sp_val' => $spec_goods['sp_val']), array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'goodsid' => $commonid));
					pdo_update('storex_spec_goods', array('goods_val' => iserializer($value['sp_value']), 'cprice' => $value['cprice'], 'oprice' => $value['oprice'], 'stock' => $value['stock'], 'thumb' => $value['thumb']), array('id' => $value['goodsid']));
				}
			}
			$diff_ids = array_diff($key_list, $goodsids);
			pdo_delete('storex_spec_goods', array('id' => $diff_ids));
		}
		itoast('编辑成功', referer(), 'success');
	}
}

include $this->template('store/shop_goods_spec');