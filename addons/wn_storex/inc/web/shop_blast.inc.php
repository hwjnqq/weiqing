<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'edit', 'stat', 'setting');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_W['wn_storex']['store_info']['id']);
$store_info = $_W['wn_storex']['store_info'];

if ($op == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$clerk_list = pdo_getall('storex_clerk', array('weid' => $_W['uniacid'], 'storeid' => $storeid));
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_clerk') . " WHERE weid = :weid AND storeid = :storeid", array(':weid' => $_W['uniacid'], ':storeid' => $storeid));
	$pager = pagination($total, $pindex, $psize);
}

if ($op == 'edit') {
	$blast_message_info = pdo_get('storex_blast_message', array('uniacid' => $_W['uniacid'], 'clerkid' => $_GPC['clerkid'], 'storeid' => $storeid));
	$blast_message_info['type'] = !empty($blast_message_info) ? $blast_message_info['type'] : 1;
	if ($blast_message_info['type'] == 2) {
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
			'uid' => intval($_GPC['uid'])
		);
		if ($type == 1) {
			$data['content'] = trim($_GPC['content']);
		} elseif ($type == 2) {
			$data['content'] = trim($_GPC['image']);
		}
		$blast_message = pdo_get('storex_blast_message', array('uniacid' => $_W['uniacid'], 'clerkid' => $_GPC['clerkid'], 'storeid' => $storeid));
		if (empty($blast_message)) {
			$data['uniacid'] = $_W['uniacid'];
			$data['storeid'] = $storeid;
			pdo_insert('storex_blast_message', $data);
		} else {
			pdo_update('storex_blast_message', $data, array('id' => $blast_message['id']));
		}
		message('设置成功', referer(), 'success');
	}
}

if ($op == 'stat') {

}

if ($op == 'setting') {

}

include $this->template('store/shop_blast');