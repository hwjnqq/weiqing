<?php

if (!pdo_fieldexists('hotel2_set', 'email')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `email` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '提醒接受邮箱' AFTER `tel`;");
}
if (!pdo_fieldexists('hotel2_set', 'mobile')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `mobile` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '提醒接受手机' AFTER `email`;");
}
if (!pdo_fieldexists('hotel2_set', 'template')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `template` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '发送模板消息' AFTER `mobile`;");
}
if (!pdo_fieldexists('hotel2_set', 'templateid')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `templateid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '模板ID' AFTER `template`;");
}
if (!pdo_fieldexists('hotel2_order', 'remark')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " ADD `remark` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '备注' AFTER `mobile`;");
}
if (pdo_fieldexists('hotel2_room', 'mprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " CHANGE `mprice` `mprice` VARCHAR(255) NOT NULL DEFAULT '' ;");
}
if (!pdo_fieldexists('hotel2_member', 'clerk')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_member') . " ADD `clerk`  VARCHAR(32) NOT NULL DEFAULT '' AFTER `status`;");
}
if (!pdo_fieldexists('hotel2_member', 'nickname')) {
	pdo_query("ALTER TABLE " . tablename('hotel2_member'). " ADD `nickname` VARCHAR(255) NOT NULL DEFAULT ''");
}
if (!pdo_fieldexists('hotel2_set', 'smscode')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `smscode` int(3) NOT NULL DEFAULT '0';");
}
if (!pdo_fieldexists('hotel2_room', 'service')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " ADD `service` int(10) NOT NULL DEFAULT '0';");
}
if (!pdo_fieldexists('hotel2_set', 'refund')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `refund` int(3) NOT NULL DEFAULT '0';");
}
if (!pdo_fieldexists('hotel2_order', 'comment')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " ADD `comment` int(3) NOT NULL DEFAULT '0';");
}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_hotel12_code`  (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL,
  `code` varchar(6) NOT NULL,
  `mobile` varchar(11) NOT NULL,
  `total` tinyint(3) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `createtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `openid` (`openid`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8");

$sql = "CREATE TABLE IF NOT EXISTS ".tablename('hotel2_comment')." (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) DEFAULT '0',
	`hotelid` int(11) DEFAULT '0',
	`uid` int(11) DEFAULT '0',
	`createtime` int(11) DEFAULT '0',
	`comment` varchar(255) DEFAULT '',
	PRIMARY KEY (`id`)
)ENGINE=MyISAM  DEFAULT CHARSET=utf8";
pdo_query($sql);
if (!pdo_fieldexists('hotel2_set', 'refuse_templateid')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `refuse_templateid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '拒绝提醒模板id';");
}
if (!pdo_fieldexists('hotel2_set', 'confirm_templateid')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `confirm_templateid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '确认提醒模板id';");
}
//hotel2_set表中添加酒店入住提醒模板id
if (!pdo_fieldexists('hotel2_set', 'check_in_templateid')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `check_in_templateid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '酒店已入住通知模板id';");
}
//hotel2_set表中添加酒店订单完成提醒模板id
if (!pdo_fieldexists('hotel2_set', 'finish_templateid')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `finish_templateid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '酒店订单完成通知模板id';");
}
//添加店员评分表
$sqls = "CREATE TABLE IF NOT EXISTS ".tablename('hotel2_comment_clerk'). "(
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `hotelid` int(11) DEFAULT '0',
  `orderid` int(25) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `comment` varchar(255) DEFAULT '',
  `clerkid` int(11) DEFAULT '0',
  `realname` varchar(20) DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
pdo_query($sqls);
//酒店订单表中添加店员评分字段
if (!pdo_fieldexists('hotel2_order', 'clerkcomment')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " ADD `clerkcomment` INT(11)  DEFAULT '0' COMMENT '店员评分';");
}
if (!pdo_fieldexists('hotel2', 'integral_rate')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2') . " ADD `integral_rate` INT(11) NOT NULL DEFAULT '0' COMMENT '在该酒店消费返积分的比例';");
}
//微酒店订单表的价格字段与房间表的价格字段的类型不一致
if (pdo_fieldexists('hotel2_order', 'oprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " CHANGE `oprice` `oprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_order', 'cprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " CHANGE `cprice` `cprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_order', 'mprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " CHANGE `mprice` `mprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_order', 'sum_price')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " CHANGE `sum_price` `sum_price` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_room', 'oprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " CHANGE `oprice` `oprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_room', 'cprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " CHANGE `cprice` `cprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_room', 'service')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " CHANGE `service` `service` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_room_price', 'oprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room_price') . " CHANGE `oprice` `oprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_room_price', 'cprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room_price') . " CHANGE `cprice` `cprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_room_price', 'mprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room_price') . " CHANGE `mprice` `mprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
//微酒店设置添加字段
if (!pdo_fieldexists('hotel2_set', 'nickname')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `nickname` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '提醒接收微信' ;");
}