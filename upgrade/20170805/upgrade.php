<?php
/**
 * 添加访问统计表
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

pdo_query("CREATE TABLE IF NOT EXISTS `ims_stat_visit` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uniacid` int(10) NOT NULL,
`module` varchar(100) NOT NULL,
`count` int(10) unsigned NOT NULL,
`date` date NOT NULL,
PRIMARY KEY (`id`),
KEY `date` (`date`) USING BTREE,
KEY `module` (`module`) USING BTREE,
KEY `uniacid` (`uniacid`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");