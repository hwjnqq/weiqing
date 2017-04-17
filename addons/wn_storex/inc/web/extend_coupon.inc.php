<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('couponmanage', 'couponconsume');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'couponmanage';

if ($op == 'couponmanage') {
	$url = $this->createWebUrl('couponmanage', array('op' => 'display'));
	header("Location: {$url}");
	die;
}

if ($op == 'couponconsume') {
	$url = $this->createWebUrl('noticemanage', array('op' => 'notice_list'));
	header("Location: {$url}");
	die;
}
