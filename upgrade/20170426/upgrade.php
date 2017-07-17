<?php
/**
 * 微擎1.0内测用户云参数错误，导致提示升级模块到最新版本的bug
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

//图片字段长度不够用（原为255）
if(!pdo_fieldexists('news_reply', 'thumb')) {
	pdo_query("ALTER TABLE ". tablename('news_reply') ." CHANGE `thumb` `thumb` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '封面图片';");
	pdo_query("ALTER TABLE ". tablename('news_reply') ." CHANGE `media_id` `media_id` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信素材：图文素材的media_id；本地素材：attach_id';");
}