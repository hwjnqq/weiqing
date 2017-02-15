<?php
//字段长度
if (pdo_fieldexists('business', 'content')) {
		$showcreatetable = pdo_fetch("SHOW CREATE TABLE" . tablename('business'));
	$find = strstr($showcreatetable['Create Table'], '`content` mediumtext');
	if (empty($find)) {
		pdo_query("ALTER TABLE ".tablename('business')." CHANGE `content` `content` mediumtext NOT NULL DEFAULT ''");
	}
}