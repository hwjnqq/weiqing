<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'base');
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

if ($op == 'base') {
	$blast_set = pdo_get('storex_blast_set', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
	if (checksubmit()) {
		$data = array(
			'bg_image' => trim($_GPC['bg_image']),
			'tail' => trim($_GPC['tail'])
		);
		$blast_setting = pdo_get('storex_blast_set', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array('id'));
		if (empty($blast_setting)) {
			$data['uniacid'] = $_W['uniacid'];
			$data['storeid'] = $storeid;
			pdo_insert('storex_blast_set', $data);
		} else {
			pdo_update('storex_blast_set', $data, array('id' => $blast_setting['id']));
		}
		message('设置成功', '', 'success');
	}
}

include $this->template('store/shop_blast');