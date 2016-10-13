<?php

if (pdo_fieldexists('lxy_marry_list', 'copyright')) {
	pdo_query('ALTER TABLE ' . tablename('lxy_marry_list') . " CHANGE `copyright` `copyright` VARCHAR(512) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '版权'");
}

if (!pdo_fieldexists('lxy_marry_list', 'bg_pic')) {
	pdo_query('ALTER TABLE ' . tablename('lxy_marry_list') . " ADD `bg_pic` VARCHAR(255) NOT NULL COMMENT '背景图片' AFTER `art_pic`");
}
