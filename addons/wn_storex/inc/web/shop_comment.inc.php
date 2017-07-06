<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('delete', 'deleteall', 'display');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_GPC['storeid']);
$store = $_W['wn_storex']['store_info'];

if ($op == 'delete') {
	$cid = intval($_GPC['cid']);
	pdo_delete('storex_comment', array('id' => $cid));
	message('删除成功！', referer(), 'success');
}
if ($op == 'deleteall') {
	foreach ($_GPC['idArr'] as $k => $id) {
		$id = intval($id);
		pdo_delete('storex_comment', array('id' => $id));
	}
	message(error(0, '删除成功！'), '', 'ajax');
}
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
	
	$comments = pdo_fetchall("SELECT c.*, g.title FROM " . tablename('storex_comment') . " AS c LEFT JOIN " .tablename($table). " AS g ON c.goodsid = g.id WHERE c.hotelid = :store_base_id AND g.weid = :weid " . $condition . "LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $param);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_comment') . " AS c LEFT JOIN " .tablename($table) . " AS g ON c.goodsid = g.id WHERE c.hotelid = :store_base_id AND g.weid = :weid " . $condition, $param);
	if (!empty($comments)) {
		foreach ($comments as $k => $val) {
			$comments[$k]['createtime'] = date('Y-m-d :H:i:s', $val['createtime']);
			$uids[] = $val['uid'];
		}
		if (!empty($uids)) {
			$user_info = pdo_getall('mc_members', array('uid' => $uids), array('uid', 'avatar', 'nickname'), 'uid');
			if (!empty($user_info)) {
				foreach ($user_info as &$val) {
					if (!empty($val['avatar'])) {
						$val['avatar'] = tomedia($val['avatar']);
					}
				}
				unset($val);
			}
			foreach ($comments as $key => $infos) {
				$comments[$key]['user_info'] = array();
				if (!empty($user_info[$infos['uid']])) {
					$comments[$key]['user_info'] = $user_info[$infos['uid']];
				}
			}
		}
	}
	$pager = pagination($total, $pindex, $psize);
	include $this->template('store/shop_comment');
}