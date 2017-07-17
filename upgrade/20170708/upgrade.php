<?php
/**
 * 更新ims_mc_members表avatar字段类型：varchar(64)换成varchar(140)(解决粉丝注册会员头像链接长度超出64)
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';
if (pdo_fieldexists('mc_members', 'avatar')) {
	pdo_query("ALTER TABLE ". tablename('mc_members') ." MODIFY  `avatar` VARCHAR(140) NOT NULL COMMENT '头像链接';");
}