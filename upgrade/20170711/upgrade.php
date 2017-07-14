<?php
/**
 * ims_users表添加副创始人字段
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';
if (!pdo_fieldexists('users', 'founder_groupid')) {
	pdo_query("ALTER TABLE " .tablename('users') . " ADD COLUMN `founder_groupid` TINYINT(4) NOT NULL DEFAULT 0  COMMENT '管理组，1是创始人，2是副创始人' AFTER `groupid`;");
}
if (!pdo_fieldexists('users', 'vice_founder_id')) {
	pdo_query("ALTER TABLE " . tablename('users') . " ADD COLUMN `vice_founder_id` int(10) NOT NULL DEFAULT 0 COMMENT '副创始人uid' AFTER `uid`;");
}
if (!pdo_fieldexists('uni_group', 'vice_founder_id')) {
	pdo_query("ALTER TABLE " . tablename('uni_group') . " ADD COLUMN `vice_founder_id` int(10) NOT NULL DEFAULT 0 COMMENT '副创始人uid' AFTER `id`;");
}
if (!pdo_fieldexists('users_group', 'vice_founder_id')) {
	pdo_query("ALTER TABLE " . tablename('users_group') . " ADD COLUMN `vice_founder_id` int(10) NOT NULL DEFAULT 0 COMMENT '副创始人uid' AFTER `id`;");
}