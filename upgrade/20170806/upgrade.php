<?php
/**
 * 副创始人组数据表
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

pdo_query("CREATE TABLE IF NOT EXISTS `ims_users_founder_group` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`name` varchar(50) NOT NULL,
`package` varchar(5000) NOT NULL DEFAULT '',
`maxaccount` int(10) unsigned NOT NULL DEFAULT '0',
`maxsubaccount` int(10) unsigned NOT NULL COMMENT '子公号最多添加数量，为0为不可以添加',
`timelimit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户组有效期限',
`maxwxapp` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");