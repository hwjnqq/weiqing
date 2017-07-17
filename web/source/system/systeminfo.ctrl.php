<?php
/**
 * 系统信息
 * [WeEngine System] Copyright (c) 2013 WE7.CC
*/
defined('IN_IA') or exit('Access Denied');

load()->model('system');

$_W['page']['title'] = '系统信息 - 工具  - 系统管理';

$info = array(
	'uid' => $_W['uid'],
	'account' => $_W['account'] ? $_W['account']['name'] : '',
	'os' => php_uname(),
	'php' => phpversion(),
	'sapi' => $_SERVER['SERVER_SOFTWARE'] ? $_SERVER['SERVER_SOFTWARE'] : php_sapi_name(),
);

//上传许可
$size = 0;
$size = @ini_get('upload_max_filesize');
if ($size) {
	$size = bytecount($size);
}
if ($size > 0) {
	$ts = @ini_get('post_max_size');
	if ($ts) {
		$ts = bytecount($size);
	}
	if ($ts > 0) {
		$size = min($size, $ts);
	}
	$ts = @ini_get('memory_limit');
	if ($ts) {
		$ts = bytecount($size);
	}
	if ($ts > 0) {
		$size = min($size, $ts);
	}
}
if (empty($size)) {
	$size = '';
} else {
	$size = sizecount($size);
}
$info['limit'] = $size;

//服务器 MySQL 版本
$sql = 'SELECT VERSION();';
$info['mysql']['version'] = pdo_fetchcolumn($sql);

$tables = pdo_fetchall("SHOW TABLE STATUS LIKE '".$_W['config']['db']['tablepre']."%'");
$size = 0;
foreach ($tables as &$table) {
	$size += $table['Data_length'] + $table['Index_length'];
}

if (empty($size)) {
	$size = '';
} else {
	$size = sizecount($size);
}

//当前数据库尺寸
$info['mysql']['size'] = $size;
//当前附件根目录
$info['attach']['url'] = $_W['attachurl'];
//当前附件尺寸
$path = IA_ROOT . '/' . $_W['config']['upload']['attachdir'];
$size = dir_size($path);
if (empty($size)) {
	$size = '';
} else {
	$size = sizecount($size);
}
$info['attach']['size'] = $size;

template('system/systeminfo');