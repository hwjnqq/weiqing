<?php
load()->model('cache');
$we7_coupon = pdo_get('modules', array('name' => 'we7_coupon'));
if (!empty($we7_coupon)) {
	pdo_update('modules', array('issystem' => '1', 'settings' => '2'), array('mid' => $we7_coupon['mid']));
}
cache_build_account_modules();

//处理activity_store字段
if (pdo_fieldexists('activity_stores', 'type')) {
	if (pdo_fieldexists('activity_stores', 'source')) {
		$sql = "ALTER TABLE `ims_activity_stores` DROP `source`;";
		pdo_run($sql);
	}
	$sql = "ALTER TABLE `ims_activity_stores` CHANGE `type` `source` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1为系统门店，2为微信门店';";
	pdo_run($sql);
}
if (!pdo_indexexists('activity_stores', 'location_id')) {
	$sql = "ALTER TABLE `ims_activity_stores` ADD INDEX `location_id` (`location_id`);";
	pdo_run($sql);
}

if (pdo_fieldexists('activity_stores', 'sid')) {
	$sql = "ALTER TABLE `ims_activity_stores` DROP `sid`;";
	pdo_run($sql);
	$sql = "ALTER TABLE `ims_activity_stores` DROP `offset_type`;";
	pdo_run($sql);
}
if (pdo_fieldexists('activity_stores', 'opentime')) {
	$sql = "ALTER TABLE `ims_activity_stores` DROP `opentime`;";
	pdo_run($sql);
}

//coupon表加dosage字段
if (!pdo_fieldexists('coupon', 'dosage')) {
	$sql = "ALTER TABLE `ims_coupon` ADD `dosage` INT UNSIGNED NOT NULL DEFAULT '0';";
	pdo_run($sql);
}

//修改coupon表source字段
if (pdo_fieldexists('coupon', 'source')) {
	$sql = "ALTER TABLE " . tablename('coupon') . " CHANGE `source` `source` TINYINT(3) UNSIGNED NOT NULL DEFAULT '2'";
	pdo_query($sql);
}

//修改之前type类型的值
$sql = <<<EOF
UPDATE `ims_coupon` SET type = '1' WHERE type = 'discount';
UPDATE `ims_coupon` SET type = '2' WHERE type = 'cash';
UPDATE `ims_coupon` SET type = '3' WHERE type = 'groupon';
UPDATE `ims_coupon` SET type = '4' WHERE type = 'gift';
UPDATE `ims_coupon` SET type = '5' WHERE type = 'general_coupon';
EOF;
pdo_run($sql);
$sql = <<<EOF
	ALTER TABLE `ims_coupon_modules` DROP `card_id`;
	ALTER TABLE `ims_coupon` DROP `location_id_list`;
	ALTER TABLE `ims_coupon` DROP `url_name_type`;
	ALTER TABLE `ims_coupon` DROP `custom_url`;
EOF;
pdo_run($sql);
//修改qrcode表url字段的长度
if (pdo_fieldexists('qrcode', 'url')) {
	$sql ="ALTER TABLE " .tablename('qrcode') . " CHANGE `url` `url` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
	pdo_query($sql);
}

//修改coupon_modules表cid改为couponid
if (pdo_fieldexists('coupon_modules', 'cid')) {
	if (pdo_fieldexists('coupon_modules', 'couponid')) {
		$sql = "ALTER TABLE " . tablename('coupon_modules') . " DROP `couponid`";
		pdo_query($sql);
	}
	$sql = "ALTER TABLE " . tablename('coupon_modules') . " CHANGE `cid` `couponid` INT(10) UNSIGNED NOT NULL DEFAULT '0'";
	pdo_query($sql);
}

if (!pdo_indexexists('activity_exchange', 'extra')) {
	//修改activity_exchange表extra字段加索引
	$sql = "ALTER TABLE `ims_activity_exchange` ADD INDEX(`extra`);";
	pdo_query($sql);
}




if (!pdo_fieldexists('coupon_record', 'uid')) {
	pdo_run("ALTER TABLE `ims_coupon_record` ADD `uid` INT(10) UNSIGNED NOT NULL DEFAULT '0';");
}
if (!pdo_fieldexists('coupon_record', 'remark')) {
	pdo_run("ALTER TABLE `ims_coupon_record` ADD `grantmodule` VARCHAR(255) NOT NULL DEFAULT '', ADD `remark` VARCHAR(255) NOT NULL DEFAULT '';");
}
if (pdo_fieldexists('coupon_record', 'outer_id')) {
	pdo_run("ALTER TABLE ims_coupon_record DROP outer_id, DROP INDEX outer_id;");
}
if (!pdo_fieldexists('coupon_record', 'couponid')) {
	pdo_run("ALTER TABLE `ims_coupon_record` ADD `couponid` INT(10) UNSIGNED NOT NULL DEFAULT '0';");
}

