<?php 
$sql = "
	CREATE TABLE IF NOT EXISTS `ims_storex_activity_exchange` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) NOT NULL,
	`title` varchar(100) NOT NULL COMMENT '物品名称',
	`description` text NOT NULL COMMENT '描述信息',
	`thumb` varchar(500) NOT NULL COMMENT '缩略图',
	`type` tinyint(1) unsigned NOT NULL COMMENT '物品类型，1系统卡券，2微信呢卡券，3实物，4虚拟物品(未启用)，5营销模块操作次数',
	`extra` varchar(3000) NOT NULL DEFAULT '' COMMENT '兑换产品属性 卡券自增id',
	`credit` int(10) unsigned NOT NULL COMMENT '兑换积分数量',
	`credittype` varchar(10) NOT NULL COMMENT '兑换积分类型',
	`pretotal` int(11) NOT NULL COMMENT '每个人最大兑换次数',
	`num` int(11) NOT NULL COMMENT '已兑换礼品数量',
	`total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总量',
	`status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
	`starttime` int(10) unsigned NOT NULL,
	`endtime` int(10) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `extra` (`extra`(333))
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='兑换表';

	CREATE TABLE IF NOT EXISTS `ims_storex_activity_exchange_trades` (
	  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(10) unsigned NOT NULL COMMENT '统一公号',
	  `uid` int(10) unsigned NOT NULL COMMENT '用户(粉丝)id',
	  `exid` int(10) unsigned NOT NULL COMMENT '兑换产品 exchangeid',
	  `type` int(10) unsigned NOT NULL,
	  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交换记录创建时间',
	  `num` int(11) NOT NULL COMMENT '数量',
	  PRIMARY KEY (`tid`),
	  KEY `uniacid` (`uniacid`,`uid`,`exid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='真实物品兑换记录表';
	
	CREATE TABLE IF NOT EXISTS `ims_storex_activity_exchange_trades_shipping` (
	  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(10) unsigned NOT NULL,
	  `exid` int(10) unsigned NOT NULL,
	  `uid` int(10) unsigned NOT NULL,
	  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '订单状态，0为正常，-1为关闭，1为已发货，2为已完成',
	  `createtime` int(10) unsigned NOT NULL,
	  `province` varchar(30) NOT NULL,
	  `city` varchar(30) NOT NULL,
	  `district` varchar(30) NOT NULL,
	  `address` varchar(255) NOT NULL,
	  `zipcode` varchar(6) NOT NULL,
	  `mobile` varchar(30) NOT NULL,
	  `name` varchar(30) NOT NULL COMMENT '收件人',
	  PRIMARY KEY (`tid`),
	  KEY `uniacid` (`uniacid`),
	  KEY `uid` (`uid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='真实物品兑换发货表';

";

pdo_run($sql);