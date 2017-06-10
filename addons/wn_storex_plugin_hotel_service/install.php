<?php

$sql = "
	CREATE TABLE IF NOT EXISTS `ims_storex_plugin_wifi` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) DEFAULT '0',
	  `wifi` varchar(1000) DEFAULT NULL,
	  `storeid` int(11) DEFAULT NULL COMMENT '酒店id',
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_plugin_tel` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) DEFAULT '0',
	  `tel` varchar(100) DEFAULT '',
	  `storeid` int(11) DEFAULT NULL COMMENT '酒店id',
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_plugin_room_item` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) DEFAULT '0',
	  `items` varchar(1000) DEFAULT '',
	  `storeid` int(11) DEFAULT NULL COMMENT '酒店id',
	  `openid` varchar(255) DEFAULT '',
	  `time` int(10) unsigned NOT NULL,
	  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态1是待确认，2是已确认',
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	
";

pdo_run($sql);