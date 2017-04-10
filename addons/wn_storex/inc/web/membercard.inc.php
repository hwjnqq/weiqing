<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->model('mc');

$ops = array('display', 'post', 'delete');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'notice_list';

$setting = pdo_get('mc_card', array('uniacid' => $_W['uniacid']));

if ($op == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 30;
	$addtime = intval($_GPC['addtime']);
	$where = ' WHERE uniacid = :uniacid AND type = 1';
	$param = array(':uniacid' => $_W['uniacid']);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('storex_notices') . " {$where}", $param);
	$notices = pdo_getall('storex_notices', array('uniacid' => $_W['uniacid'], 'type' => 1), array(),'', 'id DESC', ($pindex - 1) * $psize . "," . $psize);
	$pager = pagination($total, $pindex, $psize);
}

include $this->template('membercard');