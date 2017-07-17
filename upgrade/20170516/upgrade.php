<?php
/**
 * 微擎1.0缓存表key的长度问题
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

//发送消息缓存key的长度问题（原为200）  修改为100
if(pdo_fieldexists('core_cache', 'key')) {
    pdo_query("ALTER TABLE ". tablename('core_cache') ." MODIFY  `key` VARCHAR(100) NOT NULL COMMENT '缓存键名';");
}