<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

$storeid = intval($_W['wn_storex']['store_info']['id']);
$store_info = $_W['wn_storex']['store_info'];

if ($op == 'display') {
	$message_list = pdo_getall('storex_blast_message', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid), array('id', 'title', 'status'), 'id');
	$stat_list = pdo_getall('storex_blast_stat', array('uniacid' => $_W['uniacid'], 'storeid' => $storeid));
	if (!empty($stat_list) && is_array($stat_list)) {
		foreach ($stat_list as $key => &$value) {
			$value['title'] = $message_list[$value['msgid']]['title'];
			$value['status'] = $message_list[$value['msgid']]['status'];
		}
		unset($value);
	}
}

include $this->template('store/shop_blast_stat');