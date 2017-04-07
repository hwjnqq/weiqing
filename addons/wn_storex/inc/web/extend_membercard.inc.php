<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('card_setting', 'member_list', 'signmanage', 'noticemanage');
$op = in_array($_GPC['op'], $ops) ? trim($_GPC['op']) : 'card_setting';

if ($op == 'signmanage') {
	$url = $this->createWebUrl('signmanage');
	header("Location: {$url}");
	die;
}

if ($op == 'noticemanage') {
	$url = $this->createWebUrl('noticemanage');
	header("Location: {$url}");
	die;
}

include $this->template('extend_membercard');