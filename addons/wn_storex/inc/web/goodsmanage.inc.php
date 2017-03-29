<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('edit', 'delete', 'deleteall', 'showall', 'status', 'copyroom');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

if ($op == 'copyroom') {
	$store_base_id = intval($_GPC['store_base_id']);
	$id = intval($_GPC['id']);
	if (empty($store_base_id) || empty($id)) {
		message('参数错误', 'refresh', 'error');
	}
	$store_info = pdo_get('storex_bases', array('id' => $store_base_id, 'weid' => $_W['uniacid']), array('id', 'store_type'));
	if (!empty($store_info)) {
		$table = gettablebytype($store_info['store_type']);
	}else{
		message('店铺不存在！');
	}
	$item = pdo_get($table, array('id' => $id, 'weid' => $_W['uniacid']));
	unset($item['id']);
	$item['status'] = 0;
	pdo_insert($table, $item);
	$id = pdo_insertid();
	$url = $this->createWebUrl('goodsmanage', array('op' => 'edit', 'store_base_id' => $store_base_id, 'id' => $id, 'store_type' => $item['store_type']));
	header("Location: $url");
	exit;
}

$store_base_id = intval($_GPC['store_base_id']);
$stores = pdo_fetchall("SELECT * FROM " . tablename('storex_bases') . " WHERE weid = '{$_W['uniacid']}' ORDER BY store_type DESC, displayorder DESC", array(), 'id');
$sql = '';
$condition = array(':weid' => $_W['uniacid']);
$store_type = !empty($_GPC['store_type'])? intval($_GPC['store_type']) : 0;
if (!empty($store_base_id)){
	$sql = ' AND `store_base_id` = :store_base_id';
	$condition[':store_base_id'] = $store_base_id;
	foreach ($stores as $store_info){
		if ($store_info['id'] == $store_base_id){
			$store_type = $store_info['store_type'];
		} else {
			continue;
		}
	}
}
$sql = 'SELECT * FROM ' . tablename('storex_categorys') . ' WHERE `weid` = :weid '.$sql.' ORDER BY `parentid`, `displayorder` DESC';
$category = pdo_fetchall($sql, $condition, 'id');
if (!empty($category)) {
	$parent = $children = array();
	foreach ($category as $cid => $cate) {
		if (!empty($cate['parentid'])) {
			$children[$cate['parentid']][] = $cate;
		} else {
			$parent[$cate['id']] = $cate;
		}
	}
}
if (empty($parent)) {
	message('请先给该店铺添加一级分类！', '', 'error');
}
if (!empty($_GPC['store_base_id'])) {
	if (empty($stores[$_GPC['store_base_id']])){
		message('抱歉，店铺不存在或是已经删除！', '', 'error');
	}
}
$storex_bases = $stores[$_GPC['store_base_id']];
//根据分类的一级id获取店铺的id
$category_store = pdo_get('storex_categorys', array('id' => intval($_GPC['category']['parentid']), 'weid' => intval($_W['uniacid'])), array('id', 'store_base_id'));
$table = gettablebytype($store_type);
if ($store_type == 1) {
	$store_field = 'hotelid';
} else {
	$store_field = 'store_base_id';
}

if ($op == 'edit') {
	$card_setting = pdo_get('mc_card', array('uniacid' => $_W['uniacid']));
	$card_status =  $card_setting['status'];
	$id = intval($_GPC['id']);
	if (!empty($category_store)){
		$store_base_id = $category_store['store_base_id'];
	}
	$usergroup_list = pdo_fetchall("SELECT * FROM ".tablename('mc_groups')." WHERE uniacid = :uniacid ORDER BY isdefault DESC,credit ASC", array(':uniacid' => $_W['uniacid']));
	if (!empty($id)) {
		$item = pdo_fetch("SELECT * FROM " . tablename($table) . " WHERE id = :id", array(':id' => $id));
		$store_base_id = $item[$store_field];
		if (empty($item)) {
			if ($store_type == 1) {
				message('抱歉，房型不存在或是已经删除！', '', 'error');
			} else {
				message('抱歉，商品不存在或是已经删除！', '', 'error');
			}
		}
		$piclist = iunserializer($item['thumbs']);
		$item['mprice'] = iunserializer($item['mprice']);
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['store_base_id'])) {
			message('请选择店铺！', '', 'error');
		}
		if (empty($_GPC['title'])) {
			message('请输入房型！');
		}
		if ($storex_bases['category_set'] == 1) {
			if (empty($_GPC['category']['parentid'])) {
				message('一级分类不能为空！', '', 'error');
			}
		}
		if ($store_type == 1 && empty($_GPC['device'])) {
			message('商品说明不能为空！', '', 'error');
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
			'can_reserve' => intval($_GPC['can_reserve']),
			'reserve_device' => $_GPC['reserve_device'],
			'can_buy' => intval($_GPC['can_buy']),
			'sortid'=>intval($_GPC['sortid']),
			'sold_num' => intval($_GPC['sold_num']),
			'store_type' => intval($_GPC['store_type'])
		);
		if ($storex_bases['category_set'] == 1) {
			$common['pcate'] = $_GPC['category']['parentid'];
			$common['ccate'] = $_GPC['category']['childid'];
		}
		$goods = array(
			'store_base_id' => $store_base_id,
		);
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
			'is_house' => intval($_GPC['is_house']),
		);
	
		if (!empty($card_status)) {
			$group_mprice = array();
			foreach ($_GPC['mprice'] as $user_group => $mprice) {
				$group_mprice[$user_group] = empty($mprice)? '1' : min(1, $mprice);
			}
			$common['mprice'] = iserializer($group_mprice);
		}
		if (is_array($_GPC['thumbs'])){
			$common['thumbs'] = serialize($_GPC['thumbs']);
		} else {
			$common['thumbs'] = serialize(array());
		}
		if ($store_type == 1) {
			$data = array_merge($room, $common);
			if (empty($id)) {
				pdo_insert($table, $data);
			} else {
				pdo_update($table, $data, array('id' => $id));
			}
			pdo_query("update " . tablename('storex_hotel') . " set roomcount=(select count(*) from " . tablename('storex_room') . " where hotelid=:store_base_id AND is_house=:is_house) where store_base_id=:store_base_id", array(":store_base_id" => $store_base_id, ':is_house' => $data['is_house']));
		} else {
			$data = array_merge($goods, $common);
			if (empty($id)) {
				pdo_insert($table, $data);
			} else {
				pdo_update($table, $data, array('id' => $id));
			}
		}
		message('商品信息更新成功！', $this->createWebUrl('goodsmanage', array('store_type' => $data['store_type'])), 'success');
	}
	include $this->template('room_form');
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	pdo_delete($table, array('id' => $id, 'weid' => $_W['uniacid']));
	if ($store_type == 1) {
		pdo_query("update " . tablename('storex_hotel') . " set roomcount=(select count(*) from " . tablename('storex_room') . " where hotelid=:store_base_id) where store_base_id=:store_base_id", array(":store_base_id" => $store_base_id));
	}
	message('删除成功！', referer(), 'success');
}

if ($op == 'deleteall') {
	foreach ($_GPC['idArr'] as $k => $id) {
		$id = intval($id);
		pdo_delete($table, array('id' => $id, 'weid' => $_W['uniacid']));
		if ($store_type == 1) {
			pdo_query("update " . tablename('storex_hotel') . " set roomcount=(select count(*) from " . tablename('storex_room') . " where hotelid=:hotelid) where id=:hotelid", array(":hotelid" => $id));
		}
	}
	$this->web_message('删除成功！', '', 0);
	exit();
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
	$this->web_message('操作成功！', '', 0);
	exit();
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
if ($op == 'display') {
	$storex_bases = pdo_fetch("select title from " . tablename('storex_bases') . "where store_type=:store_type limit 1", array(":store_type" => $store_type));
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$sql = "";
	$params = array();
	if (!empty($_GPC['title'])) {
		$sql .= ' AND r.title LIKE :keywordds';
		$params[':keywordds'] = "%{$_GPC['title']}%";
	}
	if (!empty($_GPC['hoteltitle'])) {
		$sql .= ' AND h.title LIKE :keywords';
		$params[':keywords'] = "%{$_GPC['hoteltitle']}%";
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$hotelid_as = '';
	if ($store_type == 1) {
		$hotelid_as = ' r.hotelid AS store_base_id,';
		$join_condition = ' r.hotelid = h.id ';
	} else {
		$join_condition = ' r.store_base_id = h.id ';
	}
	$list = pdo_fetchall("SELECT r.*," .$hotelid_as." h.title AS hoteltitle FROM " . tablename($table) . " r left join " . tablename('storex_bases') . " h on ".$join_condition." WHERE r.weid = '{$_W['uniacid']}' $sql ORDER BY h.id, r.sortid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($table) . " r left join " . tablename('storex_bases') . " h on ".$join_condition." WHERE r.weid = '{$_W['uniacid']}' $sql", $params);
	$list = format_list($category, $list);
	$pager = pagination($total, $pindex, $psize);
	include $this->template('room');
}