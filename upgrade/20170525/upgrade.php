<?php
/**
 * 更新rule表containtype字段数据：image换成images（与1.0之前版本统一）
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

$getall_containtype_data = pdo_getall('rule', array('containtype <>' => ''));
foreach ($getall_containtype_data as $containtype_val) {
	$types = explode(',', $containtype_val['containtype']);
	if (in_array('image', $types)) {
		$new_containtype_val = str_replace('image', 'images', $containtype_val['containtype']);
		pdo_update('rule', array('containtype' => $new_containtype_val), array('id' => $containtype_val['id']));
	}
}