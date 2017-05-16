<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('storelist');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'storelist';

if ($op == 'storelist') {
	$url = $this->createWebUrl('storelist', array('op' => 'display'));
	header("Location: {$url}");
	die;
}