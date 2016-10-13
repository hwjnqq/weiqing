<?php

if (!pdo_fieldexists('research', 'mobile')) {
	pdo_query('ALTER TABLE ' . tablename('research') . " ADD `mobile` VARCHAR(11) NOT NULL DEFAULT '' COMMENT '通知手机号码' ;");
}

if (!pdo_fieldexists('research', 'alltotal')) {
	pdo_query('ALTER TABLE ' . tablename('research') . " ADD `alltotal` INT(10) UNSIGNED NOT NULL DEFAULT '100' COMMENT '预约总人数' AFTER `pretotal`;");
}