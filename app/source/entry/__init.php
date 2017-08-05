<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');

if (empty($action)) {
	$action = 'site';
}

$eid = intval($_GPC['eid']);
if(!empty($eid)) {
	$entry = module_entry($eid);
} else {
	$entry = array(
		'module' => $_GPC['m'],
		'do' => $_GPC['do'],
		'state' => $_GPC['state'],
		'direct' => 0,
	);
}
$moduels = uni_modules();
if (empty($moduels[$entry['module']])) {
	message('您访问的功能模块不存在，请重新进入');
}
if(empty($entry) || empty($entry['do'])) {
	message('非法访问.');
}
app_update_today_visit($entry['module']);
$_GPC['__entry'] = $entry['title'];
$_GPC['__state'] = $entry['state'];
$_GPC['state'] = $entry['state'];
$_GPC['m'] = $entry['module'];
$_GPC['do'] = $entry['do'];

$_W['current_module'] = $moduels[$entry['module']];
define('IN_MODULE', $entry['module']);