<?php

$sql = "
	CREATE TABLE IF NOT EXISTS `ims_storex_plugin_group_activity` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`uniacid` int(11) NOT NULL,
		`storeid` int(11) NOT NULL,
		`title` varchar(50) NOT NULL,
		`displayorder` int(11) NOT NULL COMMENT '排序',
		`starttime` int(11) NOT NULL COMMENT '开始时间',
		`endtime` int(11) NOT NULL COMMENT '结束时间',
		`thumb` varchar(200) NOT NULL COMMENT '缩略图',
		`rule` text NOT NULL COMMENT '拼团规则',
		PRIMARY KEY (`id`)
  	) DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_plugin_activity_goods` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`uniacid` int(11) NOT NULL,
		`storeid` int(11) NOT NULL,
		`group_activity` int(11) NOT NULL COMMENT '活动id',
		`goods_id` int(11) NOT NULL,
		`number` int(11) NOT NULL COMMENT '参团人数',
		`spec_cprice` text CHARACTER SET utf8 NOT NULL COMMENT '参团商品价格',
		`is_spec` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1为规格商品，2为普通商品',
		PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_plugin_group` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`uniacid` int(11) NOT NULL,
		`storeid` int(11) NOT NULL,
		`group_activity_id` int(11) NOT NULL COMMENT '活动id',
		`activity_goodsid` int(11) NOT NULL COMMENT '拼团商品的设置id',
		`head` varchar(100) NOT NULL COMMENT '发起者openid',
		`member` text NOT NULL COMMENT '参与拼团的人员的openids',
		`start_time` int(11) NOT NULL COMMENT '开团时间',
		`over` tinyint(2) NOT NULL DEFAULT '2' COMMENT '1完成2未完成3已退款',
		PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;
";

pdo_run($sql);