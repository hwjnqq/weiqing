<?php

$sql = "
	CREATE TABLE IF NOT EXISTS `ims_storex_plugin_smsset` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) DEFAULT '0',
	  `appkey` varchar(100) DEFAULT '',
	  `appsecret` varchar(255) DEFAULT '',
	  `sign` varchar(1000) DEFAULT '',
	  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态1是关闭，2是打开',
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_plugin_smsnotice` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) DEFAULT '0',
	  `notice` varchar(1000) DEFAULT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		
	CREATE TABLE `ims_storex_plugin_sms_logs` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) NOT NULL,
	  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '发送状态1为失败，2为成功',
	  `mobile` varchar(32) NOT NULL DEFAULT '' COMMENT '发送手机号',
	  `message` varchar(800) NOT NULL DEFAULT '' COMMENT '错误信息',
	  `time` int(11) NOT NULL DEFAULT '0' COMMENT '发送时间',
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";

pdo_run($sql);