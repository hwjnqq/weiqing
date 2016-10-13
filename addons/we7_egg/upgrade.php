<?php

if(!pdo_fieldexists('egg_reply', 'periodlottery')) {
	pdo_query("ALTER TABLE `ims_egg_reply` ADD `periodlottery` SMALLINT( 10 ) UNSIGNED NOT NULL DEFAULT '1' COMMENT '0为无周期';");
}

if(pdo_fieldexists('egg_award', 'activation_code')) {
	pdo_query("ALTER TABLE ".tablename('egg_award')." CHANGE `activation_code` `activation_code` text;");
}

if (!pdo_fieldexists('egg_reply', 'title')) {
	pdo_query('ALTER TABLE ' . tablename('egg_reply') . " ADD `title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '活动标题' AFTER `rid`;");
}

if (!pdo_fieldexists('egg_reply', 'starttime')) {
	pdo_query('ALTER TABLE ' . tablename('egg_reply') . " ADD `starttime` INT(10) UNSIGNED NOT NULL COMMENT '开始时间' AFTER `misscredit`;");
}

if (!pdo_fieldexists('egg_reply', 'endtime')) {
	pdo_query('ALTER TABLE ' . tablename('egg_reply') . " ADD `endtime` INT(10) UNSIGNED NOT NULL COMMENT '结束时间' AFTER `starttime`;");
}

if (!pdo_fieldexists('egg_reply', 'uniacid')) {
	pdo_query("ALTER TABLE " . tablename('egg_reply'). " ADD `uniacid` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`;");
	pdo_query("ALTER TABLE " . tablename('egg_reply'). " ADD INDEX `uniacid` (`uniacid`);");
}

if (!pdo_fieldexists('egg_winner', 'uniacid')) {
	pdo_query("ALTER TABLE " . tablename('egg_winner'). " ADD `uniacid` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`;");
	pdo_query("ALTER TABLE " . tablename('egg_winner'). " ADD INDEX `uniacid` (`uniacid`);");
}

if (!pdo_fieldexists('egg_winner', 'uid')) {
	pdo_query("ALTER TABLE " . tablename('egg_winner'). " ADD `uid` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `aid`;");
	pdo_query("ALTER TABLE " . tablename('egg_winner'). " ADD INDEX `uid` (`uid`);");
}

if (!pdo_fieldexists('egg_winner', 'isaward')) {
	pdo_query("ALTER TABLE " . tablename('egg_winner'). " ADD `isaward` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `uid`;");
}

if (!pdo_fieldexists('egg_winner', 'credit')) {
	pdo_query("ALTER TABLE " . tablename('egg_winner'). " ADD `credit` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `description`;");
}

if (pdo_fieldexists('egg_reply', 'rule')) {
	pdo_query("ALTER TABLE " . tablename('egg_reply'). "CHANGE `rule` `rule` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '规则';");
}
if (!pdo_fieldexists('egg_reply', 'maxaward')) {
	pdo_query('ALTER TABLE ' . tablename('egg_reply') . " ADD `maxaward` tinyint(3) NOT NULL DEFAULT '0' COMMENT '中奖次数限制';");
}