<?php
/**
 * 升级微擎1.0脚本
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';
require IA_ROOT . '/web/common/common.func.php';
require IA_ROOT . '/framework/library/pinyin/pinyin.php';

$pinyin = new Pinyin_Pinyin();
$module_list = pdo_getall('modules', array('title_initial' => ''));
foreach ($module_list as $module) {
	$title = $pinyin->get_first_char($module['title']);
	pdo_update('modules', array('title_initial' => $title), array('mid' => $module['mid']));
}