<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('storelist', 'paycenterwxmicro');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'storelist';

if ($op == 'storelist') {
	$url = $this->createWebUrl('storelist', array('op' => 'display'));
	header("Location: {$url}");
	die;
}

if ($op == 'paycenterwxmicro') {
	$url = $this->createWebUrl('paycenterwxmicro', array('op' => 'display'));
	header("Location: {$url}");
	die;
}
