<?php

if (!pdo_fieldexists('weihaom_wb_reply', 'uniacid')) {
	pdo_query('ALTER TABLE ' . tablename('weihaom_wb_reply') . ' ADD `uniacid` INT(10) UNSIGNED NOT NULL AFTER `rid`');
}