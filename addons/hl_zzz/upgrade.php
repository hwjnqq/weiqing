<?php

$userIndexs = $winnnerIndexs = array();
$indexSql = 'SHOW index FROm ' . tablename('zzz_user');
$indexs = pdo_fetchall($indexSql);
foreach ($indexs as $index) {
	$userIndexs[] = $index['Key_name'];
}
$indexSql = 'SHOW index FROm ' . tablename('zzz_winner');
$indexs = pdo_fetchall($indexSql);
foreach ($indexs as $index) {
	$winnnerIndexs[] = $index['Key_name'];
}


if (pdo_fieldexists('zzz_user', 'from_user')) {
	pdo_query('ALTER TABLE ' . tablename('zzz_user') . " CHANGE `from_user` `fanid` INT(10) UNSIGNED NOT NULL COMMENT '粉丝ID';");
}

if (pdo_fieldexists('zzz_winner', 'from_user')) {
	pdo_query('ALTER TABLE ' . tablename('zzz_winner') . " CHANGE `from_user` `fanid` INT(10) UNSIGNED NOT NULL COMMENT '粉丝ID';");
}

if (pdo_fieldexists('zzz_user', 'fanid') && !in_array('idx_fanid', $userIndexs)) {
	pdo_query('ALTER TABLE ' . tablename('zzz_user') . ' ADD INDEX `idx_fanid` (`fanid`)');
}

if (pdo_fieldexists('zzz_user', 'rid') && !in_array('idx_rid', $userIndexs)) {
	pdo_query('ALTER TABLE ' . tablename('zzz_user') . 'ADD INDEX `idx_rid` (`rid`)');
}

if (pdo_fieldexists('zzz_winner', 'fanid') && !in_array('idx_fanid', $winnnerIndexs)) {
	pdo_query('ALTER TABLE ' . tablename('zzz_winner') . ' ADD INDEX `idx_fanid` (`fanid`)');
}

if (pdo_fieldexists('zzz_reply', 'maxlottery')) {
	pdo_query('ALTER TABLE ' . tablename('zzz_reply') . " CHANGE `maxlottery` `maxlottery` TINYINT(3) UNSIGNED NOT NULL COMMENT '系统每天赠送次数';");
}

if (!pdo_fieldexists('zzz_reply', 'sharevalue')) {
	pdo_query('ALTER TABLE ' . tablename('zzz_reply') . " ADD `sharevalue` INT(10) UNSIGNED NOT NULL COMMENT '分享赠送体力'");
}

if (!pdo_fieldexists('zzz_user', 'sharevalue')) {
	pdo_query('ALTER TABLE ' . tablename('zzz_user') . " ADD `sharevalue` INT(10) UNSIGNED NOT NULL COMMENT '分享获得体力'");
}



$createTable = "
	CREATE TABLE IF NOT EXISTS `ims_zzz_share` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	 `rid` int(10) unsigned NOT NULL COMMENT '规则ID',
	 `fanid` int(10) unsigned NOT NULL COMMENT '粉丝ID',
	 `sharefid` int(10) unsigned NOT NULL COMMENT '分享者ID',
	 PRIMARY KEY (`id`),
	 KEY `rid` (`rid`,`fanid`,`sharefid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8
";

pdo_query($createTable);

if (!pdo_fieldexists('zzz_reply', 'uniacid')) {
	pdo_query('ALTER TABLE ' . tablename('zzz_reply') . ' ADD `uniacid` INT(10) UNSIGNED NOT NULL AFTER `rid`');
}