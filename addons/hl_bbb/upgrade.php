<?php

$createTable = "
	CREATE TABLE IF NOT EXISTS `ims_bbb_share` (
	 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	 `rid` int(10) unsigned NOT NULL COMMENT '规则ID',
	 `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
	 `share_uid` int(10) unsigned NOT NULL COMMENT '分享者ID',
	 `createtime` char(8) NOT NULL DEFAULT '' COMMENT '创建时间',
	 PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8
";

pdo_query($createTable);

if (!pdo_fieldexists('bbb_reply', 'uniacid')) {
	pdo_query('ALTER TABLE ' . tablename('bbb_reply') . ' ADD `uniacid` INT(10) UNSIGNED NOT NULL AFTER `rid`');
}