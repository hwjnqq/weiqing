<?php
if(!pdo_fieldexists('qywpweb', 'picture3')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `picture3`  varchar(100) NOT NULL COMMENT '模板3';");
}

if(!pdo_fieldexists('qywpweb', 'isck')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `isck`  tinyint(1) NOT NULL DEFAULT '0' COMMENT '计次周期0为按天，1为整个活动期';");
}

if(!pdo_fieldexists('qywpweb', 'isxf')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `isxf`  tinyint(1) NOT NULL DEFAULT '0' COMMENT '消费码0为停用，1为启用';");
}

if(!pdo_fieldexists('qywpweb', 'adstatus')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `adstatus` tinyint(1) NOT NULL DEFAULT '0' COMMENT '网络广告0为停用，1为启用';");
}

if(!pdo_fieldexists('qywpweb', 'jcok')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `jcok` tinyint(1) NOT NULL DEFAULT '0' COMMENT '自助剪裁0为停用，1为启用';");
}

if(!pdo_fieldexists('qywpweb', 'ad1type')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `ad1type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '广告格式0为图片，1为视频';");
}

if(!pdo_fieldexists('qywpweb', 'dcmaxnum')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `dcmaxnum`  tinyint(2) NOT NULL DEFAULT '1';");
}

if(!pdo_fieldexists('qywpweb', 'adtimed')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `adtimed` int(5) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('qywpweb', 'adtime1')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `adtime1` int(5) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('qywpweb', 'acode')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `acode` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'apage')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `apage` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'wifis')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `wifis` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'wifif')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `wifif` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'compass')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `compass` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'ad1url1')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `ad1url1` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'ad1url2')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `ad1url2` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'ad1url3')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `ad1url3` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'ad2url1')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `ad2url1` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'ad2url2')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `ad2url2` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'ad2url3')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `ad2url3` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'ad3url1')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `ad3url1` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'ad3url2')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `ad3url2` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'ad3url3')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `ad3url3` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'ad4url1')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `ad4url1` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'ad4url2')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `ad4url2` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb', 'ad4url3')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb')." ADD `ad4url3` varchar(100) NOT NULL;");
}

if(!pdo_fieldexists('qywpweb_xfm', 'fenpei')) {
	pdo_query("ALTER TABLE ".tablename('qywpweb_xfm')." ADD `fenpei` varchar(100) NOT NULL;");
}

$sql = "
CREATE TABLE IF NOT EXISTS `ims_qywpweb_xfm` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '规则id',
  `weid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'weid',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为未用，1为已用',
  `fenpei` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为未分配，1为已分配',
  `stype` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为系统分配，1为客户分配',
  `xfm` int(10) NOT NULL DEFAULT '0',
  `create_time` int(11) DEFAULT NULL,
  `use_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

";

pdo_run($sql);

