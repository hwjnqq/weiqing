<?php
/**
 * 升级微擎1.0脚本
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';
require IA_ROOT . '/web/common/common.func.php';

if(!pdo_fieldexists('site_category', 'multiid')) {
	pdo_query("ALTER TABLE ".tablename('site_category')." ADD `multiid` int(11) NOT NULL DEFAULT '0';");
}