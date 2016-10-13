<?php
/**
 * 超级外卖模块微站定义
 * @author strday
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;

$op = trim($_GPC['op']);

if($op == 'deliveryer') {
	$sid = intval($_GPC['sid']);
	$condition = ' where uniacid = :uniacid and sid = :sid';
	$params = array(':uniacid' => $_W['uniacid'], ':sid' => $sid);
	$data = pdo_fetchall('select * from ' . tablename('tiny_wmall_deliveryer') . $condition, $params);
	message(error(0, $data), '', 'ajax');
}