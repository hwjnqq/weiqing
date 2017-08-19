<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/18
 * Time: 13:30
 */
define('IN_SYS', true);
include_once __DIR__.'/../../framework/bootstrap.inc.php';

$isCreate = pdo_query("CREATE TABLE IF NOT EXISTS `ims_upgrade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dir` varchar(255) NOT NULL COMMENT 'upgrade 目录名',
  `createtime` int(11) NOT NULL COMMENT '创建时间',
  `batch` int(11) NOT NULL COMMENT '批次（暂不使用）',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8; ");



