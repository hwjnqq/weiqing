<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('delete', 'deleteall', 'display');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

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
if ($op == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$id = intval($_GPC['id']);//商品id
	$store_type = intval($_GPC['store_type']);
	$table = gettablebytype($store_type);
	$store_base_id = intval($_GPC['store_base_id']);
	$comments = pdo_fetchall("SELECT c.*, g.title FROM " . tablename('storex_comment') . " AS c LEFT JOIN " .tablename($table). " AS g ON c.goodsid = g.id WHERE c.hotelid = :store_base_id AND c.goodsid = :id AND g.weid = :weid " . "LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':store_base_id' => $store_base_id, ':id' => $id, 'weid' => $_W['uniacid']));
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_comment') . " AS c LEFT JOIN " .tablename($table) . " AS g ON c.goodsid = g.id WHERE c.hotelid = :store_base_id AND c.goodsid = :id AND g.weid = :weid ", array(':store_base_id' => $store_base_id, ':id' => $id, 'weid' => $_W['uniacid']));
	if (!empty($comments) && is_array($comments)) {
		foreach ($comments as &$val) {
			$val['createtime'] = date('Y-m-d H:i:s', $val['createtime']);
			$uids[] = $val['uid'];
		}
		unset($val);
		if (!empty($uids) && is_array($uids)) {
			$user_info = pdo_getall('mc_mapping_fans', array('uid' => $uids), array('uid', 'nickname'), 'uid');
			foreach ($comments as &$infos) {
				$infos['user_info'] = array();
				if (!empty($user_info[$infos['uid']])) {
					$infos['user_info'] = $user_info[$infos['uid']];
				}
			}
			unset($infos);
		}
	}
	$pager = pagination($total, $pindex, $psize);
	include $this->template('goodscomment');
}