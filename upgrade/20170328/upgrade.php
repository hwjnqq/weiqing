<?php
/**
 * 微擎1.0内测用户云参数错误，导致提示升级模块到最新版本的bug
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

//新增微站文章管理->未排序时，文章按修改时间倒序排序
if(!pdo_fieldexists('site_article', 'edittime')) {
	pdo_query("ALTER TABLE ". tablename('site_article') ." ADD `edittime` INT(10) NOT NULL COMMENT '修改时间' AFTER `createtime`;");
}