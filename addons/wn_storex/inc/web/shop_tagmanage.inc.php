<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('display', 'edit', 'delete', 'deleteall', 'showall', 'status');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_GPC['storeid']);
$store = $_W['wn_storex']['store_info'];
$store_type = $store['store_type'];

if ($op == 'display') {
	$tags = pdo_getall('storex_tags', array('storeid' => $storeid, 'uniacid' => $_W['uniacid']));
	include $this->template('store/shop_tags');
}

if ($op == 'edit') {
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		$tag = pdo_get('storex_tags', array('id' => $id));
		if (empty($tag)) {
			message('该标签不存在或是已经删除', referer(), 'error');
		}
	}
	
	if (checksubmit('submit')) {
		if (empty($_GPC['title'])) {
			message('请输入标签名称', referer(), 'error');
		}
		if (mb_strlen($_GPC['title'], "utf-8") > 3) {
			message('标签名称不要超过4个字符', referer(), 'error');
		}
		$insert = array(
			'uniacid' => intval($_W['uniacid']),
			'storeid' => $storeid,
			'title' => trim($_GPC['title']),
			'displayorder' => intval($_GPC['displayorder']),
			'status' => intval($_GPC['status']),
		);
		if (empty($id)) {
			pdo_insert('storex_tags', $insert);
			$msg = '添加成功！';
		} else {
			pdo_update('storex_tags', $insert, array('id' => $id));
			$msg = '标签信息更新成功！';
		}
		message($msg, $this->createWebUrl('shop_tagmanage', array('storeid' => $storeid)), 'success');
	}
	include $this->template('store/shop_tags');
}

if ($op == 'delete') {
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		pdo_delete('storex_tags', array('id' => $id, 'uniacid' => $_W['uniacid']));
		message('删除成功！', referer(), 'success');
	} else {
		message('操作失败！', referer(), 'error');
	}
}

if ($op == 'deleteall') {
	if (!empty($_GPC['idArr'])) {
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			pdo_delete('storex_tags', array('id' => $id, 'uniacid' => $_W['uniacid']));
		}
		message(error(0, '删除成功！'), '', 'ajax');
	} else {
		message(error(-1, '删除失败！'), '', 'ajax');
	}
}
if ($op == 'showall') {
	if ($_GPC['show_name'] == 'showall') {
		$show_status = 1;
	} else {
		$show_status = 2;
	}
	if (!empty($_GPC['idArr'])) {
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			if (!empty($id)) {
				pdo_update('storex_tags', array('status' => $show_status), array('id' => $id));
			}
		}
		message(error(0, '操作成功！'), '', 'ajax');
	} else {
		message(error(-1, '操作失败！'), '', 'ajax');
	}
}
if ($op == 'status') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		message('参数错误！', referer(), 'error');
	}
	$status = pdo_update('storex_tags', array('status' => $_GPC['status']), array('id' => $id));
	if (!empty($status)) {
		message('设置成功！', referer(), 'success');
	} else {
		message('操作失败！', referer(), 'error');
	}
}