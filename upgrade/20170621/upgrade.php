<?php
/**
 * we7_coupon 系统卡券 改为非系统模块
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

pdo_update('modules', array('issystem' => 0), array('name' => 'we7_coupon'));

$uni_groups = pdo_getall('uni_group');
if (!empty($uni_groups) && is_array($uni_groups)) {
	foreach ($uni_groups as $group) {
		$modules = iunserializer($group['modules']);
		if (is_array($modules) && array_search('we7_coupon', $modules) === false) {
			$modules[] = 'we7_coupon';
		}
		$modules = iserializer($modules);
		pdo_update('uni_group', array('modules' => $modules), array('id' => $group['id']));
	}
}
cache_build_uni_group();