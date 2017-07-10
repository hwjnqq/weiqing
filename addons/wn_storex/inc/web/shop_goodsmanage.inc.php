<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('display', 'edit', 'delete', 'deleteall', 'showall', 'status', 'copyroom');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_GPC['storeid']);
$store = $_W['wn_storex']['store_info'];
$store_type = $store['store_type'];

$parent = pdo_getall('storex_categorys', array('store_base_id' => $storeid, 'parentid' => 0), array(), 'id', array('parentid', 'displayorder DESC'));
if (empty($parent)) {
	message('请先给该店铺添加一级分类！', $this->createWebUrl('shop_category', array('storeid' => $storeid)), 'error');
}

$children = array();
$category = pdo_getall('storex_categorys', array('store_base_id' => $storeid), array(), 'id', array('parentid', 'displayorder DESC'));
if (!empty($category) && is_array($category)) {
	foreach ($category as $cid => $cate) {
		if (!empty($cate['parentid'])) {
			$children[$cate['parentid']][] = $cate;
		}
	}
}

//根据分类的一级id获取店铺的id
if (!empty($_GPC['category']['parentid'])) {
	$category_store = pdo_get('storex_categorys', array('id' => intval($_GPC['category']['parentid']), 'weid' => intval($_W['uniacid'])), array('id', 'store_base_id'));
}
$table = gettablebytype($store_type);
if ($store_type == STORE_TYPE_HOTEL) {
	$store_field = 'hotelid';
} else {
	$store_field = 'store_base_id';
}

if ($op == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$sql .= ' AND ' . $store_field . ' = ' . $storeid;
	$params = array();
	if (!empty($_GPC['title'])) {
		$sql .= ' AND r.title LIKE :keywordds';
		$params[':keywordds'] = "%{$_GPC['title']}%";
	}
	$hotelid_as = '';
	if ($store_type == STORE_TYPE_HOTEL) {
		$hotelid_as = ' r.hotelid AS store_base_id,';
		$join_condition = ' r.hotelid = h.id ';
	} else {
		$join_condition = ' r.store_base_id = h.id ';
	}
	$list = pdo_fetchall("SELECT r.*, " . $hotelid_as . " h.title AS hoteltitle FROM " . tablename($table) . " r LEFT JOIN " . tablename('storex_bases') . " h ON " . $join_condition . " WHERE r.weid = '{$_W['uniacid']}' $sql ORDER BY h.id, r.sortid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($table) . " r LEFT JOIN " . tablename('storex_bases') . " h ON " . $join_condition . " WHERE r.weid = '{$_W['uniacid']}' $sql", $params);
	$list = format_list($category, $list);
	$pager = pagination($total, $pindex, $psize);
	include $this->template('store/shop_goodslist');
}

if ($op == 'edit') {
	load()->func('tpl');
	$id = intval($_GPC['id']);
	if (!empty($category_store)){
		$store_base_id = $category_store['store_base_id'];
	}
	$usergroup_list = pdo_getall('mc_groups', array('uniacid' => $_W['uniacid']), array(), '', array('isdefault DESC', 'credit ASC'));
	if (!empty($id)) {
		$item = pdo_get($table, array('id' => $id));
		if (empty($store_type)) {
			$item['express_set'] = iunserializer($item['express_set']);
		}
		$store_base_id = $item[$store_field];
		if (empty($item)) {
			if ($store_type == STORE_TYPE_HOTEL) {
				message('抱歉，房型不存在或是已经删除！', '', 'error');
			} else {
				message('抱歉，商品不存在或是已经删除！', '', 'error');
			}
		}
		$piclist = iunserializer($item['thumbs']);
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['title'])) {
			message('请输入房型！', '', 'error');
		}
		if ($store['category_set'] == 1) {
			if (empty($_GPC['category']['parentid'])) {
				message('一级分类不能为空！', '', 'error');
			}
		}
		if ($store_type == STORE_TYPE_HOTEL && empty($_GPC['device'])) {
			message('商品说明不能为空！', '', 'error');
		}
		if (empty($_GPC['oprice']) || $_GPC['oprice'] <= 0 || empty($_GPC['cprice']) || $_GPC['cprice'] <= 0) {
			message('商品原价和优惠价不能为空！', '', 'error');
		}
		
		$common = array(
			'weid' => $_W['uniacid'],
			'title' => $_GPC['title'],
			'thumb'=>$_GPC['thumb'],
			'oprice' => $_GPC['oprice'],
			'cprice' => $_GPC['cprice'],
			'device' => $_GPC['device'],
			'score' => intval($_GPC['score']),
			'status' => $_GPC['status'],
			'sales' => $_GPC['sales'],
			'can_buy' => intval($_GPC['can_buy']),
			'sortid'=>intval($_GPC['sortid']),
			'sold_num' => intval($_GPC['sold_num']),
			'store_type' => intval($store_type),
		);
		if ($store_type == STORE_TYPE_HOTEL) {
			$is_house = 1;
		} else {
			$is_house = 2;
		}
		if ($storex_bases['category_set'] == 1) {
			$common['pcate'] = $_GPC['category']['parentid'];
			$common['ccate'] = $_GPC['category']['childid'];
			if (!empty($category) && !empty($category[$_GPC['category']['parentid']])) {
				$is_house = $category[$_GPC['category']['parentid']]['category_type'];
			} else {
				$is_house = 2;
			}
		}
		
		if (empty($store_type)) {
			$goods = array(
				'store_base_id' => $store_base_id,
			);
			$express_set = array(
				'express' => is_numeric($_GPC['express']) ? $_GPC['express'] : 0,
				'full_free' => is_numeric($_GPC['full_free']) ? $_GPC['full_free'] : 0,
			);
			$goods['express_set'] = iserializer($express_set);
		}
		$room = array(
			'hotelid' => $store_base_id,
			'breakfast' => $_GPC['breakfast'],
			'area' => $_GPC['area'],
			'area_show' => $_GPC['area_show'],
			'bed' => $_GPC['bed'],
			'bed_show' => $_GPC['bed_show'],
			'bedadd' => $_GPC['bedadd'],
			'bedadd_show' => $_GPC['bedadd_show'],
			'persons' => $_GPC['persons'],
			'persons_show' => $_GPC['persons_show'],
			'floor' => $_GPC['floor'],
			'floor_show' => $_GPC['floor_show'],
			'smoke' => $_GPC['smoke'],
			'smoke_show' => $_GPC['smoke_show'],
			'service' => intval($_GPC['service']),
			'is_house' => $is_house,
		);
	
		if (is_array($_GPC['thumbs'])) {
			$common['thumbs'] = serialize($_GPC['thumbs']);
		} else {
			$common['thumbs'] = serialize(array());
		}
		if ($store_type == STORE_TYPE_HOTEL) {
			$data = array_merge($room, $common);
			if (empty($id)) {
				pdo_insert($table, $data);
			} else {
				pdo_update($table, $data, array('id' => $id));
			}
			pdo_query("UPDATE " . tablename('storex_hotel') . " SET roomcount = (SELECT count(*) FROM " . tablename('storex_room') . " WHERE hotelid = :store_base_id AND is_house = :is_house) WHERE store_base_id = :store_base_id", array(':store_base_id' => $store_base_id, ':is_house' => $data['is_house']));
		} else {
			$data = array_merge($goods, $common);
			if (empty($id)) {
				pdo_insert($table, $data);
			} else {
				pdo_update($table, $data, array('id' => $id));
			}
		}
		message('商品信息更新成功！', $this->createWebUrl('shop_goodsmanage', array('storeid' => $storeid)), 'success');
	}
	include $this->template('store/shop_goodsedit');
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	pdo_delete($table, array('id' => $id, 'weid' => $_W['uniacid']));
	if ($store_type == STORE_TYPE_HOTEL) {
		pdo_query("UPDATE " . tablename('storex_hotel') . " SET roomcount = (SELECT count(*) FROM " . tablename('storex_room') . " WHERE hotelid = :store_base_id) WHERE store_base_id = :store_base_id", array(':store_base_id' => $store_base_id));
	}
	message('删除成功！', referer(), 'success');
}

if ($op == 'deleteall') {
	foreach ($_GPC['idArr'] as $k => $id) {
		$id = intval($id);
		pdo_delete($table, array('id' => $id, 'weid' => $_W['uniacid']));
		if ($store_type == STORE_TYPE_HOTEL) {
			pdo_query("UPDATE " . tablename('storex_hotel') . " SET roomcount = (SELECT count(*) FROM " . tablename('storex_room') . " WHERE hotelid = :hotelid) WHERE id = :hotelid", array(':hotelid' => $id));
		}
	}
	message(error(0, '删除成功！'), '', 'ajax');
}
if ($op == 'showall') {
	if ($_GPC['show_name'] == 'showall') {
		$show_status = 1;
	} else {
		$show_status = 0;
	}
	foreach ($_GPC['idArr'] as $k => $id) {
		$id = intval($id);
		if (!empty($id)) {
			pdo_update($table, array('status' => $show_status), array('id' => $id));
		}
	}
	message(error(0, '操作成功！'), '', 'ajax');
}
if ($op == 'status') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		message('抱歉，传递的参数错误！', '', 'error');
	}
	$temp = pdo_update($table, array('status' => $_GPC['status']), array('id' => $id));
	if ($temp == false) {
		message('抱歉，刚才操作数据失败！', '', 'error');
	} else {
		message('状态设置成功！', referer(), 'success');
	}
}

if ($op == 'copyroom') {
	$id = intval($_GPC['id']);
	if (empty($storeid) || empty($id)) {
		message('参数错误', 'refresh', 'error');
	}
	$item = pdo_get($table, array('id' => $id, 'weid' => $_W['uniacid']));
	unset($item['id']);
	$item['status'] = 0;
	pdo_insert($table, $item);
	$id = pdo_insertid();
	$url = $this->createWebUrl('shop_goodsmanage', array('op' => 'edit', 'storeid' => $storeid, 'id' => $id));
	header("Location: $url");
	exit;
}