<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display', 'delete', 'deleteall', 'post', 'reply');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_GPC['storeid']);
$store = $_W['wn_storex']['store_info'];
if ($op == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$table = gettablebytype($store['store_type']);
	$id = intval($_GPC['id']);//商品id
	$param = array(':store_base_id' => $storeid, ':weid' => $_W['uniacid']);
	if (!empty($id)) {
		$condition = " AND c.goodsid = :id ";
		$param[':id'] = $id;
	}
	$search_title = trim($_GPC['title']);
	if (!empty($search_title) && empty($id)) {
		$condition = " AND g.title like :title ";
		$param[':title'] = "%{$search_title}%";
	}
	
	$comments = pdo_fetchall("SELECT c.*, g.title FROM " . tablename('storex_comment') . " AS c LEFT JOIN " .tablename($table) . " AS g ON c.goodsid = g.id WHERE c.hotelid = :store_base_id AND g.weid = :weid " . $condition . " ORDER BY c.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $param, 'id');
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_comment') . " AS c LEFT JOIN " . tablename($table) . " AS g ON c.goodsid = g.id WHERE c.hotelid = :store_base_id AND g.weid = :weid " . $condition, $param);
	if (!empty($comments) && is_array($comments)) {
		foreach ($comments as $key => &$val) {
			$val['createtime'] = date('Y-m-d H:i:s', $val['createtime']);
			if ($val['type'] == 3) {
				$comments[$val['cid']]['reply'][] = $val;
				unset($comments[$key]);
			}
		}
		unset($val);
	}
	$pager = pagination($total, $pindex, $psize);
	include $this->template('store/shop_comment');
}

if ($op == 'post') {
	$id = intval($_GPC['id']);
	$table = gettablebytype($store['store_type']);
	if ($store['store_type'] == 1) {
		$store_base_id = 'hotelid';
	} else {
		$store_base_id = 'store_base_id';
	}
	$goods_list = pdo_getall($table, array('weid' => $_W['uniacid'], $store_base_id => $storeid), array(), 'id');
	$comment_info = pdo_get('storex_comment', array('id' => $id));
	if (checksubmit()) {
		$id = intval($_GPC['id']);
		$comment_info = pdo_get('storex_comment', array('id' => $id));
		$insert = array(
			'createtime' => time(),
			'comment' => trim($_GPC['comment']),
			'goodsid' => intval($_GPC['goodsid']),
			'type' => 2,
			'thumb' => trim($_GPC['thumb']),
			'nickname' => trim($_GPC['nickname']),
			'comment_level' => $_GPC['comment_level']
		);
		if (empty($insert['goodsid']) || empty($insert['nickname']) || empty($insert['comment']) || empty($insert['comment_level'])) {
			message('请完善填写信息', '', 'error');
		}
		if (empty($comment_info)) {
			$insert['hotelid'] = $storeid;
			$insert['uniacid'] = $_W['uniacid'];
			pdo_insert('storex_comment', $insert);
		} else {
			pdo_update('storex_comment', $insert, array('id' => $id));
		}
		message('编辑成功', $this->createWebUrl('shop_comment', array('storeid' => $storeid)), 'success');
	}
	include $this->template('store/shop_commentedit');
}

if ($op == 'reply') {
	if ($_W['ispost'] && $_W['isajax']) {
		$insert = array(
			'uniacid' => intval($_W['uniacid']),
			'hotelid' => $storeid,
			'comment' => trim($_GPC['comment']),
			'type' => 3,
			'goodsid' => intval($_GPC['goodsid']),
			'cid' => intval($_GPC['cid']),
			'nickname' => '商家回复',
			'createtime' => time(),
			'comment_level' => 0
		);
		if (empty($insert['comment']) || empty($insert['goodsid']) || empty($insert['cid']) || empty($insert['goodsid'])) {
			message(error(-1, '数据有误,请刷新重试'), '', 'ajax');
		}
		pdo_insert('storex_comment', $insert);
		$id = pdo_insertid();
		if (!empty($id)) {
			message(error(0, '回复成功'), '', 'ajax');
		} else {
			message(error(-1, '回复失败'), '', 'ajax');
		}
	}
}

if ($op == 'delete') {
	$cid = intval($_GPC['cid']);
	if (!empty($cid)) {
		pdo_delete('storex_comment', array('id' => $cid));
		message('删除成功！', referer(), 'success');
	} else {
		message('参数错误！', referer(), 'error');
	}
}

if ($op == 'deleteall') {
	if (!empty($_GPC['idArr']) && is_array($_GPC['idArr'])) {
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			pdo_delete('storex_comment', array('id' => $id));
		}
		message(error(0, '删除成功！'), '', 'ajax');
	} else {
		message(error(-1, '删除失败！'), '', 'ajax');
	}
}