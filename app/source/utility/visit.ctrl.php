<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/app/source/utility/style.ctrl.php : v b53c8ba00893 : 2014/06/16 12:17:57 : RenChao $
 */
defined('IN_IA') or exit('Access Denied');

load()->model('app');

$dos = array('showjs');
$do = in_array($do, $dos) ? $do : 'showjs';
if ($do == 'showjs') {
	$module_name = empty($_GPC['m']) ? 'wesite' : trim($_GPC['m']);
	$url = url('utility/visit/update', array('module_name' => $module_name));
	$visitjs = '$.post("'. $url .'", function(data){})';
	echo $visitjs;
	exit;
}