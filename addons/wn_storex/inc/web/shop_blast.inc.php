<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display');
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

include $this->template('store/shop_blast');