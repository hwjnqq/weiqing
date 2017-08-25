<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'post', 'delete', 'default');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_W['wn_storex']['store_info']['id']);
$store_info = $_W['wn_storex']['store_info'];

if ($op == 'display') {
	$clerkid = intval($_GPC['clerkid']);
	if (empty($clerkid)) {
		message('信息错误，请重新进入', referer(), 'error');
	}
	$clerk_info = pdo_get('storex_clerk', array('weid' => $_W['uniacid'], 'id' => $clerkid));
	$message_list = pdo_getall('storex_blast_message', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid, 'clerkid' => $clerkid));
}

if ($op == 'post') {
	$id = intval($_GPC['id']);
	$blast_message_info = pdo_get('storex_blast_message', array('uniacid' => $_W['uniacid'], 'clerkid' => $_GPC['clerkid'], 'storeid' => $storeid, 'id' => $id));
	$blast_message_info['type'] = !empty($blast_message_info) ? $blast_message_info['type'] : 1;
	if ($blast_message_info['type'] == 3) {
		$blast_message_info['image'] = $blast_message_info['content'];
		$blast_message_info['content'] = '';
	}
	if (checksubmit()) {
		$type = intval($_GPC['type']);
		$data = array(
			'type' => $type,
			'title' => trim($_GPC['title']),
			'status' => intval($_GPC['status']),
			'time' => time(),
			'clerkid' => intval($_GPC['clerkid']),
			'uid' => intval($_GPC['uid']),
			'isdefault' => 2
		);
		if ($type == 1) {
			$data['content'] = trim($_GPC['content']);
		} elseif ($type == 3) {
			$data['content'] = trim($_GPC['image']);
		}
		if (empty($data['title']) || empty($data['content'])) {
			message('信息填写不完整', '', 'error');
		}
		$blast_message_list = pdo_getall('storex_blast_message', array('uniacid' => $_W['uniacid'], 'clerkid' => $_GPC['clerkid'], 'storeid' => $storeid), array('id'), 'id');
		if (empty($blast_message_list)) {
			$data['isdefault'] = 1;
		}
		if (empty($blast_message_list[$_GPC['id']])) {
			$data['uniacid'] = $_W['uniacid'];
			$data['storeid'] = $storeid;
			pdo_insert('storex_blast_message', $data);
		} else {
			pdo_update('storex_blast_message', $data, array('id' => $_GPC['id']));
		}
		message('设置成功', referer(), 'success');
	}
}

if ($op == 'delete') {
	$blast_message = pdo_get('storex_blast_message', array('uniacid' => $_W['uniacid'], 'id' => $_GPC['id'], 'storeid' => $_GPC['storeid'], 'clerkid' => $_GPC['clerkid']), array('id'));
	if (!empty($blast_message)) {
		pdo_delete('storex_blast_message', array('id' => $_GPC['id']));
		message('删除成功', referer(), 'success');
	}
	message('信息不存在', referer(), 'error');
}

if ($op == 'default') {
	$blast_message = pdo_get('storex_blast_message', array('uniacid' => $_W['uniacid'], 'id' => $_GPC['id'], 'storeid' => $_GPC['storeid'], 'clerkid' => $_GPC['clerkid']), array('id'));
	if (!empty($blast_message)) {
		pdo_update('storex_blast_message', array('isdefault' => 2), array('clerkid' => $_GPC['clerkid']));
		pdo_update('storex_blast_message', array('isdefault' => 1), array('id' => $_GPC['id']));
		message('修改成功', referer(), 'success');
	}
	message('信息不存在', referer(), 'error');
}

include $this->template('store/shop_blast_message');