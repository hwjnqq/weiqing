<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display', 'post', 'delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$store = $_W['wn_storex']['store_info'];
$storeid = $store['id'];

if ($op == 'display') {
	load()->func('tpl');
	if (!empty($_GPC['displayorder'])) {
		foreach ($_GPC['displayorder'] as $id => $displayorder) {
			pdo_update('storex_categorys', array('displayorder' => $displayorder), array('id' => $id, 'weid' => $_W['uniacid']));
		}
		message('分类排序更新成功！', $this->createWebUrl('shop_category', array('op' => 'display', 'storeid' => $storeid)), 'success');
	}
	$children = array();
	$category = pdo_getall('storex_categorys', array('weid' => $_W['uniacid'], 'store_base_id' => $storeid), array(), '', array('store_base_id DESC', 'parentid ASC', 'displayorder DESC'));
	foreach ($category as $index => &$row_info) {
		$row_info['store_title'] = $store['title'];
		if (empty($row_info['store_title'])) {
			unset($category[$index]);
		}
		if (!empty($row_info['parentid'])) {
			if ($store['store_type'] != STORE_TYPE_HOTEL) {
				$children[$row_info['parentid']][] = $row_info;
			}
			unset($category[$index]);
		}
	}
	unset($row_info);
}

if ($op == 'post') {
	$parentid = intval($_GPC['parentid']);
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		$category = pdo_get('storex_categorys', array('id' => $id, 'weid' => $_W['uniacid']));
	} else {
		$category = array(
			'displayorder' => 0,
		);
	}
	if (!empty($parentid)) {
		$parent = pdo_get('storex_categorys', array('id' => $parentid), array('id', 'name', 'category_type'));
		if (empty($parent)) {
			message('抱歉，上级分类不存在或是已经被删除！', 'refresh', 'error');
		}
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['name'])) {
			message('抱歉，请输入分类名称！', 'refresh', 'error');
		}
		$category_type = empty($_GPC['category_type']) ? 2 : intval($_GPC['category_type']);
		if (!empty($parent)) {
			$category_type = $parent['category_type'];
		}
		$data = array(
			'weid' => $_W['uniacid'],
			'name' => $_GPC['name'],
			'enabled' => intval($_GPC['enabled']),
			'displayorder' => intval($_GPC['displayorder']),
			'isrecommand' => intval($_GPC['isrecommand']),
			'description' => $_GPC['description'],
			'parentid' => $parentid,
			'thumb' => $_GPC['thumb'],
			'category_type' => $category_type,
		);
		$data['store_base_id'] = $storeid;
		if (!empty($id)) {
			unset($data['parentid']);
			pdo_update('storex_categorys', $data, array('id' => $id, 'weid' => $_W['uniacid']));
			if ($data['category_type'] != $category['category_type'] && $store['store_type'] == STORE_TYPE_HOTEL) {
				pdo_update('storex_categorys', array('category_type' => $data['category_type']), array('parentid' => $id, 'weid' => $_W['uniacid']));
				pdo_update('storex_room', array('is_house' => $data['category_type']), array('pcate' => $id, 'weid' => $_W['uniacid'], 'hotelid' => $storeid));
			}
		} else {
			pdo_insert('storex_categorys', $data);
		}
		message('更新分类成功！', $this->createWebUrl('shop_category', array('op' => 'display', 'storeid' => $storeid)), 'success');
	}
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	$category = pdo_get('storex_categorys', array('id' => $id, 'weid' => intval($_W['uniacid'])), array('id', 'parentid', 'store_base_id'));
	if (empty($category)) {
		message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('shop_category', array('op' => 'display', 'storeid' => $storeid)), 'error');
	}
	$table = gettablebytype($store['store_type']);
	$relation = 'AND';
	$condition_goods['weid'] = $_W['uniacid'];
	if (empty($category['parentid'])) {
		$condition_goods['pcate'] = $id;
		$condition_category['parentid'] = $id;
		$relation = 'OR';
	} else {
		$condition_goods['ccate'] = $id;
		$condition_category['weid'] = $_W['uniacid'];
	}
	pdo_delete($table, $condition_goods);
	pdo_delete('storex_categorys', $condition_category, $relation);
	message('分类删除成功！', $this->createWebUrl('shop_category', array('op' => 'display', 'storeid' => $storeid)), 'success');
}
include $this->template('store/shop_category');