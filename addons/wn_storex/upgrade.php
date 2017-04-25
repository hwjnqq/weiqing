<?php 
if (!pdo_fieldexists('storex_room', 'is_house')) {
	pdo_query("ALTER TABLE " . tablename('storex_room') . " ADD `is_house` INT(11) NOT NULL DEFAULT '1' COMMENT '是否是房型 1 是，2不是 ';");
}
if (!pdo_fieldexists('storex_comment', 'goodsid')) {
	pdo_query("ALTER TABLE " . tablename('storex_comment') . " ADD `goodsid` INT(11) NOT NULL COMMENT '评论商品的id';");
}
if (!pdo_fieldexists('storex_comment', 'comment_level')) {
	pdo_query("ALTER TABLE " . tablename('storex_comment') . " ADD `comment_level` TINYINT(11) NOT NULL COMMENT '评论商品的级别';");
}
if (!pdo_fieldexists('storex_order', 'track_number')) {
	pdo_query("ALTER TABLE " . tablename('storex_order') . " ADD `track_number` varchar(64) NOT NULL COMMENT '物流单号';");
}
if (!pdo_fieldexists('storex_order', 'express_name')) {
	pdo_query("ALTER TABLE " . tablename('storex_order') . " ADD `express_name` varchar(50) NOT NULL COMMENT '物流类型';");
}
if (!pdo_fieldexists('storex_bases', 'distance')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') . " ADD `distance` int(11) NOT NULL COMMENT '配送距离';");
}
if (pdo_fieldexists('storex_bases', 'lng')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') . " CHANGE `lng` `lng` DECIMAL(10,6) NULL DEFAULT '0.00';");
}
if (pdo_fieldexists('storex_bases', 'lat')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') . " CHANGE `lat` `lat` DECIMAL(10,6) NULL DEFAULT '0.00';");
}
$sql = "
	CREATE TABLE IF NOT EXISTS `ims_storex_clerk` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) DEFAULT '0',
	`userid` varchar(50) DEFAULT '',
	`from_user` varchar(50) DEFAULT '',
	`realname` varchar(255) DEFAULT '',
	`mobile` varchar(255) DEFAULT '',
	`score` int(11) DEFAULT '0' COMMENT '积分',
	`createtime` int(11) DEFAULT '0',
	`userbind` int(11) DEFAULT '0',
	`status` int(11) DEFAULT '0',
	`username` varchar(30) DEFAULT '' COMMENT '用户名',
	`password` varchar(200) DEFAULT '' COMMENT '密码',
	`salt` varchar(8) NOT NULL DEFAULT '' COMMENT '加密盐',
	`nickname` varchar(255) NOT NULL DEFAULT '',
	`permission` text NOT NULL COMMENT '店员权限',
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_notices` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL DEFAULT '0',
	`uid` int(10) unsigned NOT NULL DEFAULT '0',
	`type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1:公共消息，2:个人消息',
	`title` varchar(30) NOT NULL,
	`thumb` varchar(100) NOT NULL,
	`groupid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通知会员组。默认为所有会员',
	`content` text NOT NULL,
	`addtime` int(10) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `uid` (`uid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_notices_unread` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL DEFAULT '0',
	`notice_id` int(10) unsigned NOT NULL DEFAULT '0',
	`uid` int(10) unsigned NOT NULL DEFAULT '0',
	`is_new` tinyint(3) unsigned NOT NULL DEFAULT '1',
	`type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1:公共通知，2：个人通知',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `uid` (`uid`),
	KEY `notice_id` (`notice_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_sign_record` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL DEFAULT '0',
	`uid` int(10) unsigned NOT NULL DEFAULT '0',
	`credit` int(10) unsigned NOT NULL DEFAULT '0',
	`is_grant` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`addtime` int(10) unsigned NOT NULL DEFAULT '0',
	`year` smallint(4) NOT NULL COMMENT '签到的年',
	`month` smallint(2) NOT NULL COMMENT '签到的月',
	`day` smallint(2) NOT NULL COMMENT '签到的日',
	`remedy` tinyint(2) NOT NULL COMMENT '是否是补签 1 是补签,2 是额外',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `uid` (`uid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_sign_set` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL DEFAULT '0',
	`sign` varchar(1000) NOT NULL,
	`share` varchar(500) NOT NULL,
	`content` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_mc_card` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL,
	`title` varchar(100) NOT NULL DEFAULT '' COMMENT '会员卡名称',
	`color` varchar(255) NOT NULL DEFAULT '' COMMENT '会员卡字颜色',
	`background` varchar(255) NOT NULL DEFAULT '' COMMENT '背景设置',
	`logo` varchar(255) NOT NULL DEFAULT '' COMMENT 'logo图片',
	`format_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否用手机号作为会员卡号',
	`format` varchar(50) NOT NULL DEFAULT '' COMMENT '会员卡卡号规则',
	`description` varchar(512) NOT NULL DEFAULT '' COMMENT '会员卡说明',
	`fields` varchar(1000) NOT NULL DEFAULT '' COMMENT '会员卡资料',
	`snpos` int(11) NOT NULL DEFAULT '0',
	`status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用1:启用0:关闭',
	`business` text NOT NULL,
	`discount_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '折扣类型.1:满减,2:折扣',
	`discount` varchar(3000) NOT NULL DEFAULT '' COMMENT '各个会员组的优惠详情',
	`grant` varchar(3000) NOT NULL COMMENT '领卡赠送:积分,余额,优惠券',
	`grant_rate` varchar(20) NOT NULL DEFAULT '0' COMMENT '消费返积分比率',
	`offset_rate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分抵现比例',
	`offset_max` int(10) NOT NULL DEFAULT '0' COMMENT '每单最多可抵现金数量',
	`nums_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '计次是否开启，0为关闭，1为开启',
	`nums_text` varchar(15) NOT NULL COMMENT '计次名称',
	`nums` varchar(1000) NOT NULL DEFAULT '' COMMENT '计次规则',
	`times_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '计时是否开启，0为关闭，1为开启',
	`times_text` varchar(15) NOT NULL COMMENT '计时名称',
	`times` varchar(1000) NOT NULL DEFAULT '' COMMENT '计时规则',
	`params` longtext NOT NULL,
	`html` longtext NOT NULL,
	`recommend_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`sign_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '签到功能是否开启，0为关闭，1为开启',
	`brand_name` varchar(128) NOT NULL DEFAULT '' COMMENT '商户名字,',
	`notice` varchar(48) NOT NULL DEFAULT '' COMMENT '卡券使用提醒',
	`quantity` int(10) NOT NULL DEFAULT '0' COMMENT '会员卡库存',
	`max_increase_bonus` int(10) NOT NULL DEFAULT '0' COMMENT '用户单次可获取的积分上限',
	`least_money_to_use_bonus` int(10) NOT NULL DEFAULT '0' COMMENT '抵扣条件',
	`source` int(1) NOT NULL DEFAULT '1' COMMENT '1.系统会员卡，2微信会员卡',
	`card_id` varchar(250) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`)
	) ENGINE=MyISAMDEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_mc_card_members` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL,
	`uid` int(10) DEFAULT NULL,
	`openid` varchar(50) NOT NULL,
	`cid` int(10) NOT NULL DEFAULT '0',
	`cardsn` varchar(20) NOT NULL DEFAULT '',
	`mobile` varchar(11) NOT NULL COMMENT '注册手机号',
	`email` varchar(50) NOT NULL COMMENT '邮箱',
	`realname` varchar(255) NOT NULL COMMENT '真实姓名',
	`status` tinyint(1) NOT NULL,
	`createtime` int(10) unsigned NOT NULL,
	`nums` int(10) unsigned NOT NULL DEFAULT '0',
	`endtime` int(10) unsigned NOT NULL DEFAULT '0',
	`fields` varchar(2500) NOT NULL COMMENT '扩展的信息',
	PRIMARY KEY (`id`)
	) ENGINE=MyISAMDEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_mc_card_record` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL DEFAULT '0',
	`uid` int(10) unsigned NOT NULL DEFAULT '0',
	`type` varchar(15) NOT NULL,
	`model` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1：充值，2：消费',
	`fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '充值金额',
	`tag` varchar(10) NOT NULL COMMENT '次数|时长|充值金额',
	`note` varchar(255) NOT NULL,
	`remark` varchar(200) NOT NULL COMMENT '备注，只有管理员可以看',
	`addtime` int(10) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `uid` (`uid`),
	KEY `addtime` (`addtime`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	CREATE TABLE IF NOT EXISTS `ims_storex_activity_exchange` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) NOT NULL,
	  `title` varchar(100) NOT NULL COMMENT '物品名称',
	  `description` text NOT NULL COMMENT '描述信息',
	  `thumb` varchar(500) NOT NULL COMMENT '缩略图',
	  `type` tinyint(1) unsigned NOT NULL COMMENT '物品类型，1系统卡券，2微信呢卡券，3实物，4虚拟物品(未启用)，5营销模块操作次数',
	  `extra` varchar(3000) NOT NULL DEFAULT '' COMMENT '兑换产品属性 卡券自增id',
	  `credit` int(10) unsigned NOT NULL COMMENT '兑换积分数量',
	  `credittype` varchar(10) NOT NULL COMMENT '兑换积分类型',
	  `pretotal` int(11) NOT NULL COMMENT '每个人最大兑换次数',
	  `num` int(11) NOT NULL COMMENT '已兑换礼品数量',
	  `total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总量',
	  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
	  `starttime` int(10) unsigned NOT NULL,
	  `endtime` int(10) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `extra` (`extra`(333))
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='兑换表';
	CREATE TABLE IF NOT EXISTS `ims_storex_coupon` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(10) unsigned NOT NULL DEFAULT '0',
	  `acid` int(10) unsigned NOT NULL DEFAULT '0',
	  `card_id` varchar(50) NOT NULL,
	  `type` varchar(15) NOT NULL COMMENT '卡券类型',
	  `logo_url` varchar(150) NOT NULL,
	  `code_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'code类型（二维码/条形码/code码）',
	  `brand_name` varchar(15) NOT NULL COMMENT '商家名称',
	  `title` varchar(15) NOT NULL,
	  `sub_title` varchar(20) NOT NULL,
	  `color` varchar(15) NOT NULL,
	  `notice` varchar(15) NOT NULL COMMENT '使用说明',
	  `description` varchar(1000) NOT NULL,
	  `date_info` varchar(200) NOT NULL COMMENT '使用期限',
	  `quantity` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总库存',
	  `use_custom_code` tinyint(3) NOT NULL DEFAULT '0',
	  `bind_openid` tinyint(3) unsigned NOT NULL DEFAULT '0',
	  `can_share` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否可分享',
	  `can_give_friend` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否可转赠给朋友',
	  `get_limit` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '每人领取限制',
	  `service_phone` varchar(20) NOT NULL,
	  `extra` varchar(1000) NOT NULL,
	  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1:审核中,2:未通过,3:已通过,4:卡券被商户删除,5:未知',
	  `is_display` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否上架',
	  `is_selfconsume` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启自助核销',
	  `promotion_url_name` varchar(10) NOT NULL,
	  `promotion_url` varchar(100) NOT NULL,
	  `promotion_url_sub_title` varchar(10) NOT NULL,
	  `source` tinyint(3) unsigned NOT NULL DEFAULT '2' COMMENT '来源，1是系统，2是微信',
	  `dosage` int(10) unsigned DEFAULT '0' COMMENT '已领取数量',
	  PRIMARY KEY (`id`),
	  KEY `uniacid` (`uniacid`,`acid`),
	  KEY `card_id` (`card_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	CREATE TABLE IF NOT EXISTS `ims_storex_coupon_record` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(10) unsigned NOT NULL,
	  `acid` int(10) unsigned NOT NULL,
	  `card_id` varchar(50) NOT NULL,
	  `openid` varchar(50) NOT NULL,
	  `friend_openid` varchar(50) NOT NULL,
	  `givebyfriend` tinyint(3) unsigned NOT NULL,
	  `code` varchar(50) NOT NULL,
	  `hash` varchar(32) NOT NULL,
	  `addtime` int(10) unsigned NOT NULL,
	  `usetime` int(10) unsigned NOT NULL,
	  `status` tinyint(3) NOT NULL,
	  `clerk_name` varchar(15) NOT NULL,
	  `clerk_id` int(10) unsigned NOT NULL,
	  `store_id` int(10) unsigned NOT NULL,
	  `clerk_type` tinyint(3) unsigned NOT NULL,
	  `couponid` int(10) unsigned NOT NULL,
	  `uid` int(10) unsigned NOT NULL,
	  `grantmodule` varchar(255) NOT NULL,
	  `remark` varchar(255) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `uniacid` (`uniacid`,`acid`),
	  KEY `card_id` (`card_id`),
	  KEY `hash` (`hash`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	CREATE TABLE IF NOT EXISTS `ims_storex_coupon_store` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(10) NOT NULL,
	  `couponid` varchar(255) NOT NULL DEFAULT '',
	  `storeid` int(10) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`),
	  KEY `couponid` (`couponid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
pdo_run($sql);
if (!pdo_fieldexists('storex_bases', 'category_set')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') . " ADD `category_set` TINYINT NOT NULL DEFAULT '1' COMMENT '分类开启设置1开启，2关闭';");
}
if (!pdo_fieldexists('storex_bases', 'skin_style')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') . " ADD `skin_style` VARCHAR(48) NOT NULL DEFAULT 'display' COMMENT '皮肤选择';");
}
if (!pdo_fieldexists('storex_categorys', 'category_type')) {
	pdo_query("ALTER TABLE " . tablename('storex_categorys') . " ADD `category_type` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '分类类型 1 酒店，2,普通';");
}

$category = pdo_getall('storex_categorys');
$stores = pdo_getall('storex_bases', array(), array('id', 'store_type', 'skin_style'), 'id');
if (!empty($stores)) {
	foreach ($stores as $val) {
		if ($val['skin_style'] == 'style1') {
			pdo_update('storex_bases', array('skin_style' => 'display'), array('id' => $val['id']));
		}
	}
}
if (!empty($category) && !empty($stores)) {
	foreach ($category as &$info){
		if ($info['category_type'] != 2) {
			if (!empty($stores[$info['store_base_id']])) {
				if ($stores[$info['store_base_id']]['store_type'] == 1) {
					$data = array('category_type' => 1);
					$info['category_type'] = 1;
				} else {
					$data = array('category_type' => 2);
					$info['category_type'] = 2;
				}
				pdo_update('storex_categorys', $data, array('id' => $info['id']));
			}
			if (!empty($info['parentid'])) {
				pdo_update('storex_room', array('is_house' => $info['category_type']), array('id' => $val['id'], 'pcate' => $info['id']));
			}
		}
	}
}


if (!pdo_fieldexists('storex_set', 'extend_switch')) {
	pdo_query("ALTER TABLE " . tablename('storex_set') . " ADD `extend_switch` varchar(400) NOT NULL COMMENT '扩展开关';");
}
$extend = pdo_getall('modules_bindings', array('module' => 'wn_storex', 'entry' => 'menu', 'title' => '扩展功能', 'do' => 'extend'));
if (empty($extend)) {
	pdo_insert('modules_bindings', array('module' => 'wn_storex', 'entry' => 'menu', 'title' => '扩展功能', 'do' => 'extend', 'icon' => 'fa fa-puzzle-piece'));
} else {
	if (count($extend) > 1) {
		foreach ($extend as $key =>$value) {
			if ($value['icon'] == '') {
				pdo_delete('modules_bindings', array('eid' => $value['eid']));
				unset($extend[$key]);
			}
		}
	}
	if (count($extend) > 1) {
		array_pop($extend);
		foreach ($extend as $k => $val) {
			pdo_delete('modules_bindings', array('eid' => $val['eid']));
		}
	}
}
if (pdo_fieldexists('storex_bases', 'timeend')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') . " CHANGE `timeend` `timeend` VARCHAR(50) NOT NULL DEFAULT '0' COMMENT '运营结束时间';");
}
if (pdo_fieldexists('storex_bases', 'timestart')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') . " CHANGE `timestart` `timestart` VARCHAR(50) NOT NULL DEFAULT '0' COMMENT '运营开始时间';");
}
//删除会员价字段
if (pdo_fieldexists('storex_order', 'mprice')) {
	pdo_query("ALTER TABLE " . tablename('storex_order') ." DROP `mprice`;");
}
if (pdo_fieldexists('storex_room', 'mprice')) {
	pdo_query("ALTER TABLE " . tablename('storex_room') ." DROP `mprice`;");
}
if (pdo_fieldexists('storex_room_price', 'mprice')) {
	pdo_query("ALTER TABLE " . tablename('storex_room_price') ." DROP `mprice`;");
}
if (pdo_fieldexists('storex_goods', 'mprice')) {
	pdo_query("ALTER TABLE " . tablename('storex_goods') ." DROP `mprice`;");
}
//删除返积分比例字段
if (pdo_fieldexists('storex_bases', 'integral_rate')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') ." DROP `integral_rate`;");
}
//order表加入coupon字段，使用卡券的recordid
if (!pdo_fieldexists('storex_order', 'coupon')) {
	pdo_query("ALTER TABLE " . tablename('storex_order') ." ADD `coupon` INT NOT NULL COMMENT '使用卡券信息';");
}