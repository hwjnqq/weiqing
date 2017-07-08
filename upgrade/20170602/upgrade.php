<?php
/**
 * 更新mc_fans_tag_mapping表tagid字段类型：tinyint换成int(解决tagid大于256 时引起的bug)
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';
if(pdo_fieldexists('mc_fans_tag_mapping', 'tagid')) {
	pdo_query("ALTER TABLE ". tablename('mc_fans_tag_mapping') ." CHANGE `tagid` `tagid` INT(11) UNSIGNED NOT NULL COMMENT '公众号用户标签ID';");
}