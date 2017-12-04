<?php

function goods_get_list_common($condition, $pageinfo) {
	global $_W, $_GPC;
	$pindex = $pageinfo['pindex'];
	$psize = $pageinfo['psize'];
	$storeid = $_W['wn_storex']['store_info']['id'];
	$sql .= ' AND store_base_id = ' . $storeid;
	if (!empty($condition['title'])) {
		$sql .= ' AND r.title LIKE :keywords';
		$params[':keywords'] = "%{$condition['title']}%";
	}
	if (!empty($condition['category_id'])) {
		$category_id = intval($condition['category_id']);
		$sql .= ' AND ( r.pcate = :category_id OR r.ccate = :category_id)';
		$params[':category_id'] = $category_id;
	}
	$list = pdo_fetchall("SELECT r.*, h.title AS store_title FROM " . tablename('storex_goods') . " r LEFT JOIN " . tablename('storex_bases') . " h ON r.store_base_id = h.id WHERE r.weid = '{$_W['uniacid']}' $sql ORDER BY h.id, r.sortid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_goods') . " r LEFT JOIN " . tablename('storex_bases') . " h ON r.store_base_id = h.id WHERE r.weid = '{$_W['uniacid']}' $sql", $params);
	return array('list' => $list, 'total' => $total);
}

function goods_get_list_hotel($condition, $pageinfo) {
	global $_W, $_GPC;
	$pindex = $pageinfo['pindex'];
	$psize = $pageinfo['psize'];
	$storeid = $_W['wn_storex']['store_info']['id'];
	$sql .= ' AND store_base_id = ' . $storeid;
	if (!empty($condition['title'])) {
		$sql .= ' AND r.title LIKE :keywords';
		$params[':keywords'] = "%{$condition['title']}%";
	}
	if (!empty($condition['category_id'])) {
		$category_id = intval($condition['category_id']);
		$sql .= ' AND ( r.pcate = :category_id OR r.ccate = :category_id)';
		$params[':category_id'] = $category_id;
	}
	$list = pdo_fetchall("SELECT r.*, h.title AS store_title FROM " . tablename('storex_room') . " r LEFT JOIN " . tablename('storex_bases') . " h ON r.store_base_id = h.id WHERE r.weid = '{$_W['uniacid']}' $sql ORDER BY h.id, r.sortid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_room') . " r LEFT JOIN " . tablename('storex_bases') . " h ON r.store_base_id = h.id WHERE r.weid = '{$_W['uniacid']}' $sql", $params);
	return array('list' => $list, 'total' => $total);
}
