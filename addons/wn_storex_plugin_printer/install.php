<?php

$sql = "
	CREATE TABLE IF NOT EXISTS `ims_storex_plugin_printer` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(10) unsigned NOT NULL DEFAULT '0',
	  `storeid` int(10) unsigned NOT NULL COMMENT '店铺ID',
	  `name` varchar(40) NOT NULL,
	  `user` varchar(80) NOT NULL COMMENT '云账号',
	  `key` varchar(50) NOT NULL,
	  `sn` varchar(100) NOT NULL COMMENT '打印机编码sn',
	  `header` varchar(50) NOT NULL,
	  `footer` varchar(50) NOT NULL,
	  `qrcode` varchar(1000) NOT NULL,
	  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
	  PRIMARY KEY (`id`),
	  KEY `uniacid` (`uniacid`),
	  KEY `storeid` (`storeid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_plugin_printer_set` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(10) unsigned NOT NULL DEFAULT '0',
	  `storeid` int(10) unsigned NOT NULL COMMENT '店铺ID',
	  `printerids` varchar(800) NOT NULL COMMENT '打印机ID',
	  PRIMARY KEY (`id`),
	  KEY `uniacid` (`uniacid`),
	  KEY `storeid` (`storeid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	
	CREATE TABLE `ims_storex_plugin_print_logs` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(10) unsigned NOT NULL DEFAULT '0',
	  `storeid` int(10) unsigned NOT NULL COMMENT '店铺ID',
	  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '打印状态1为失败，2为成功',
	  `message` varchar(800) NOT NULL DEFAULT '' COMMENT '错误信息',
	  `time` int(11) NOT NULL DEFAULT '0' COMMENT '时间',
	  PRIMARY KEY (`id`),
	  KEY `uniacid` (`uniacid`),
	  KEY `storeid` (`storeid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";

pdo_run($sql);