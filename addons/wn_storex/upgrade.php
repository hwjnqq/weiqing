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
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='兑换表';
		
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
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		
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
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_coupon_store` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) NOT NULL,
	`couponid` varchar(255) NOT NULL DEFAULT '',
	`storeid` int(10) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `couponid` (`couponid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_mc_member_property` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) NOT NULL,
	`property` varchar(200) NOT NULL DEFAULT '' COMMENT '当前公众号用户属性',
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户属性设置表';

	CREATE TABLE IF NOT EXISTS `ims_storex_coupon_activity` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) NOT NULL,
	`msg_id` int(10) NOT NULL DEFAULT '0',
	`status` int(10) NOT NULL DEFAULT '1',
	`title` varchar(255) NOT NULL DEFAULT '',
	`type` int(3) NOT NULL DEFAULT '0' COMMENT '1 发送系统卡券 2发送微信卡券',
	`thumb` varchar(255) NOT NULL DEFAULT '',
	`coupons` int(11) NOT NULL COMMENT '选择派发的卡券的id',
	`description` varchar(255) NOT NULL DEFAULT '‘’',
	`members` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_activity_stores` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL,
	`business_name` varchar(50) NOT NULL,
	`branch_name` varchar(50) NOT NULL,
	`category` varchar(255) NOT NULL,
	`province` varchar(15) NOT NULL,
	`city` varchar(15) NOT NULL,
	`district` varchar(15) NOT NULL,
	`address` varchar(50) NOT NULL,
	`longitude` varchar(15) NOT NULL,
	`latitude` varchar(15) NOT NULL,
	`telephone` varchar(20) NOT NULL,
	`photo_list` varchar(10000) NOT NULL,
	`avg_price` int(10) unsigned NOT NULL,
	`recommend` varchar(255) NOT NULL,
	`special` varchar(255) NOT NULL,
	`introduction` varchar(255) NOT NULL,
	`open_time` varchar(50) NOT NULL,
	`location_id` int(10) unsigned NOT NULL,
	`status` tinyint(3) unsigned NOT NULL COMMENT '1 审核通过 2 审核中 3审核未通过',
	`source` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1为系统门店，2为微信门店',
	`message` varchar(500) NOT NULL,
	`store_base_id` int(11) NOT NULL COMMENT '普通店铺添加为微信门店',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `location_id` (`location_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

	CREATE TABLE IF NOT EXISTS `ims_storex_activity_clerks` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL,
	`uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联users表uid',
	`storeid` int(10) unsigned NOT NULL DEFAULT '0',
	`name` varchar(20) NOT NULL,
	`password` varchar(20) NOT NULL,
	`mobile` varchar(20) NOT NULL,
	`openid` varchar(50) NOT NULL,
	`nickname` varchar(30) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `password` (`password`),
	KEY `openid` (`openid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='积分兑换店员表';
	
	CREATE TABLE IF NOT EXISTS `ims_storex_paycenter_order` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL DEFAULT '0',
	`uid` int(10) unsigned NOT NULL DEFAULT '0',
	`pid` int(10) unsigned NOT NULL DEFAULT '0',
	`clerk_id` int(10) unsigned NOT NULL DEFAULT '0',
	`store_id` int(10) unsigned NOT NULL DEFAULT '0',
	`clerk_type` tinyint(3) unsigned NOT NULL DEFAULT '2',
	`uniontid` varchar(40) NOT NULL,
	`transaction_id` varchar(40) NOT NULL,
	`type` varchar(10) NOT NULL COMMENT '支付方式',
	`trade_type` varchar(10) NOT NULL COMMENT '支付类型:刷卡支付,扫描支付',
	`body` varchar(255) NOT NULL COMMENT '商品信息',
	`fee` varchar(15) NOT NULL COMMENT '商品费用',
	`final_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '优惠后应付价格',
	`credit1` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '抵消积分',
	`credit1_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '积分抵消金额',
	`credit2` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '余额支付金额',
	`cash` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '线上支付金额',
	`remark` varchar(255) NOT NULL,
	`auth_code` varchar(30) NOT NULL,
	`openid` varchar(50) NOT NULL,
	`nickname` varchar(50) NOT NULL COMMENT '付款人',
	`follow` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否关注公众号',
	`status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '线上支付状态',
	`credit_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '积分,余额的交易状态.0:未扣除,1:已扣除',
	`paytime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
	`createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_users_permission` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL,
	`uid` int(10) unsigned NOT NULL,
	`type` varchar(30) NOT NULL,
	`permission` varchar(10000) NOT NULL,
	`url` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_activity_clerk_menu` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) NOT NULL,
	`displayorder` int(4) NOT NULL,
	`pid` int(6) NOT NULL,
	`group_name` varchar(20) NOT NULL,
	`title` varchar(20) NOT NULL,
	`icon` varchar(50) NOT NULL,
	`url` varchar(200) NOT NULL,
	`type` varchar(20) NOT NULL,
	`permission` varchar(50) NOT NULL,
	`system` int(2) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_sales` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL,
	`storeid` int(10) unsigned NOT NULL,
	`cumulate` decimal(10,2) DEFAULT '0.00',
	`date` varchar(8) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`,`date`)
	) DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_refund_logs` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`type` varchar(20) NOT NULL DEFAULT '',
	`uniacid` int(11) NOT NULL,
	`orderid` int(10) unsigned NOT NULL DEFAULT '0',
	`storeid` int(10) unsigned NOT NULL DEFAULT '0',
	`out_refund_no` varchar(40) NOT NULL COMMENT '商户退款订单号',
	`refund_fee` decimal(10,2) NOT NULL,
	`total_fee` decimal(10,2) NOT NULL,
	`status` tinyint(4) NOT NULL DEFAULT '0',
	`time` int(11) DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `orderid` (`orderid`),
	KEY `storeid` (`storeid`),
	KEY `uniacid` (`uniacid`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_homepage` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL DEFAULT '0',
	`storeid` int(10) unsigned NOT NULL DEFAULT '0',
	`type` varchar(15) NOT NULL COMMENT '首页块类型',
	`items` longtext NOT NULL,
	`displayorder` int(10) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `storeid` (`storeid`)
	) DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_order_logs` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`time` int(11) NOT NULL COMMENT '操作时间',
	`before_change` tinyint(2) NOT NULL COMMENT '更改前的状态',
	`after_change` tinyint(2) NOT NULL COMMENT '更改后的状态',
	`type` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '状态类型',
	`remark` varchar(500) CHARACTER SET utf8 NOT NULL COMMENT '内容',
	`uid` int(10) NOT NULL,
	`clerk_id` int(11) NOT NULL COMMENT '店员id，0 后台操作',
	`clerk_type` tinyint(3) NOT NULL COMMENT '1线上操作，2系统后台，3店员',
	`orderid` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `orderid` (`orderid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
	unset($info);
}


if (!pdo_fieldexists('storex_set', 'extend_switch')) {
	pdo_query("ALTER TABLE " . tablename('storex_set') . " ADD `extend_switch` varchar(400) NOT NULL COMMENT '扩展开关';");
}
$extend = pdo_getall('modules_bindings', array('module' => 'wn_storex', 'entry' => 'menu', 'title' => '扩展功能', 'do' => 'extend'));
if (empty($extend)) {
	pdo_insert('modules_bindings', array('module' => 'wn_storex', 'entry' => 'menu', 'title' => '扩展功能', 'do' => 'extend', 'icon' => 'fa fa-puzzle-piece'));
} else {
	if (count($extend) > 1) {
		foreach ($extend as $key => $value) {
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
if (!pdo_fieldexists('storex_coupon_record', 'granttype')) {
	pdo_query("ALTER TABLE " . tablename('storex_coupon_record') ." ADD `granttype` tinyint(4) NOT NULL COMMENT '获取卡券的方式：1 兑换，2 扫码，3派发';");
}
if (!pdo_fieldexists('storex_set', 'source')) {
	pdo_query("ALTER TABLE " . tablename('storex_set') . " ADD `source` TINYINT NOT NULL DEFAULT '2' COMMENT '卡券类型，1为系统卡券，2为微信卡券';");
}
if (!pdo_fieldexists('storex_room', 'express_set')) {
	pdo_query("ALTER TABLE " . tablename('storex_room') . " ADD `express_set` TEXT NOT NULL COMMENT '运费设置';");
}
if (!pdo_fieldexists('storex_goods', 'express_set')) {
	pdo_query("ALTER TABLE " . tablename('storex_goods') . " ADD `express_set` TEXT NOT NULL COMMENT '运费设置';");
}
if (!pdo_fieldexists('storex_sign_set', 'status')) {
	pdo_query("ALTER TABLE " . tablename('storex_sign_set') . " ADD `status` TINYINT(2) NOT NULL COMMENT '开启状态 1开启，2关闭';");
}

if (!pdo_fieldexists('storex_order', 'static_price')) {
	pdo_query("ALTER TABLE " . tablename('storex_order') . " ADD `static_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '初始订单的价格，不可更改';");
	$orders = pdo_getall('storex_order', array(), array('id', 'sum_price'));
	if (!empty($orders) && is_array($orders)) {
		foreach ($orders as $info) {
			pdo_update('storex_order', array('static_price' => $info['sum_price']), array('id' => $info['id']));
		}
	}
}
if (!pdo_fieldexists('storex_order', 'refund_status')) {
	pdo_query("ALTER TABLE " . tablename('storex_order') . " ADD `refund_status` TINYINT(2) NOT NULL COMMENT '退款状态 1退款中，2成功，3失败';");
}
//删除商圈和品牌不必要功能
$delete_fields = array('ordermax', 'numsmax', 'daymax', 'sales', 'level', 'brandid', 'businessid');
foreach ($delete_fields as $field) {
	if (pdo_fieldexists('storex_hotel', $field)) {
		pdo_query("ALTER TABLE " . tablename('storex_hotel') . " DROP " . $field);
	}
}
if (pdo_fieldexists('storex_bases', 'extend_table')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') . " DROP `extend_table`;");
}

//酒店的分类只有一级分类，将已有的二级分类的数据兼容到一级下
pdo_update('storex_room', array('ccate' => 0));
pdo_delete('storex_categorys', array('category_type' => 1, 'parentid !=' => 0));

$subscribes = array('user_get_card', 'user_del_card', 'user_consume_card',);
$subscribes = iserializer($subscribes);
pdo_update('modules', array('subscribes' => $subscribes), array('name' => 'wn_storex'));

//order表paytype修改
if (pdo_fieldexists('storex_order', 'paytype')) {
	pdo_query("ALTER TABLE " . tablename('storex_order') . " CHANGE `paytype` `paytype` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';");
}
$order_lists = pdo_getall('storex_order');
$pay_type_list = array('credit', 'wechat', 'delivery', 'alipay');
if (!empty($order_lists) && is_array($order_lists)) {
	foreach ($order_lists as $key => $value) {
		if (!empty($value['paytype']) && !in_array($value['paytype'], $pay_type_list)) {
			if (in_array($value['paytype'], array('1', '21', '22', '3'))) {
				if ($value['paytype'] == 1) {
					$update['paytype'] = 'credit';
				} elseif ($value['paytype'] == 21) {
					$update['paytype'] = 'wechat';
				} elseif ($value['paytype'] == 22) {
					$update['paytype'] = 'alipay';
				} elseif ($value['paytype'] == 3) {
					$update['paytype'] = 'delivery';
				}
				pdo_update('storex_order', $update, array('id' => $value['id']));
			}
		}
	}
}
//删除弃用的业务菜单
pdo_delete('modules_bindings', array('module' => 'wn_storex', 'entry' => 'menu', 'do' => 'business'));
pdo_delete('modules_bindings', array('module' => 'wn_storex', 'entry' => 'menu', 'do' => 'brand'));
pdo_delete('modules_bindings', array('module' => 'wn_storex', 'entry' => 'menu', 'do' => 'goodscategory'));
pdo_delete('modules_bindings', array('module' => 'wn_storex', 'entry' => 'menu', 'do' => 'goodsmanage'));
pdo_delete('modules_bindings', array('module' => 'wn_storex', 'entry' => 'menu', 'do' => 'order'));

//商品增加副标题
if (!pdo_fieldexists('storex_room', 'sub_title')) {
	pdo_query("ALTER TABLE " . tablename('storex_room') . " ADD `sub_title` VARCHAR(12) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '副标题';");
}
if (!pdo_fieldexists('storex_goods', 'sub_title')) {
	pdo_query("ALTER TABLE " . tablename('storex_goods') . " ADD `sub_title` VARCHAR(12) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '副标题';");
}


//处理mobile更新遗留的js，css和svg文件
load()->func('file');
$js_file_trees = file_tree(IA_ROOT . '/addons/wn_storex/template/style/mobile/js');
$css_file_trees = file_tree(IA_ROOT . '/addons/wn_storex/template/style/mobile/css');
$svg_file_trees = file_tree(IA_ROOT . '/addons/wn_storex/template/style/mobile/img');
$current_js_files = array(
	IA_ROOT . '/addons/wn_storex/template/style/mobile/js/black.20170715139.js',
	IA_ROOT . '/addons/wn_storex/template/style/mobile/js/display.20170715139.js',
	IA_ROOT . '/addons/wn_storex/template/style/mobile/js/manifest.20170715139.js',
	IA_ROOT . '/addons/wn_storex/template/style/mobile/js/vendor.20170715139.js',
	IA_ROOT . '/addons/wn_storex/template/style/mobile/js/service.20170715139.js'
);
$current_css_files = array(
	IA_ROOT . '/addons/wn_storex/template/style/mobile/css/black.20170715139.css',
	IA_ROOT . '/addons/wn_storex/template/style/mobile/css/display.20170715139.css',
	IA_ROOT . '/addons/wn_storex/template/style/mobile/css/service.20170715139.css',
);
$current_svg_files = array(
	IA_ROOT . '/addons/wn_storex/template/style/mobile/img/storex.20170715139.svg',
);
$css_diff_files = array_diff($css_file_trees, $current_css_files);
$js_diff_files = array_diff($js_file_trees, $current_js_files);
$svg_diff_files = array_diff($svg_file_trees, $current_svg_files);
if (!empty($js_diff_files)) {
	foreach ($js_diff_files as $value) {
		file_delete($value);
	}
}
if (!empty($css_diff_files)) {
	foreach ($css_diff_files as $value) {
		file_delete($value);
	}
}
if (!empty($svg_diff_files)) {
	foreach ($svg_diff_files as $value) {
		file_delete($value);
	}
}
$unused_files = array(
	IA_ROOT . '/addons/wn_storex/template/manage.html',
	IA_ROOT . '/addons/wn_storex/template/query.html',
	IA_ROOT . '/addons/wn_storex/template/form.html',
	IA_ROOT . '/addons/wn_storex/template/brand.html',
	IA_ROOT . '/addons/wn_storex/template/brand_form.html',
	IA_ROOT . '/addons/wn_storex/template/business.html',
	IA_ROOT . '/addons/wn_storex/template/business_form.html',
	IA_ROOT . '/addons/wn_storex/template/business_query.html',
	IA_ROOT . '/addons/wn_storex/template/category.html',
	IA_ROOT . '/addons/wn_storex/template/room.html',
	IA_ROOT . '/addons/wn_storex/template/room_form.html',
	IA_ROOT . '/addons/wn_storex/template/order.html',
	IA_ROOT . '/addons/wn_storex/template/order_form.html',
	IA_ROOT . '/addons/wn_storex/template/goodscomment.html',
	IA_ROOT . '/addons/wn_storex/template/room_price.html',
	IA_ROOT . '/addons/wn_storex/template/room_price_list.html',
	IA_ROOT . '/addons/wn_storex/template/room_price_lot.html',
	IA_ROOT . '/addons/wn_storex/template/room_price_lot_list.html',
	IA_ROOT . '/addons/wn_storex/template/room_status.html',
	IA_ROOT . '/addons/wn_storex/template/room_status_list.html',
	IA_ROOT . '/addons/wn_storex/template/room_status_lot.html',
	IA_ROOT . '/addons/wn_storex/template/room_status_lot_list.html',
	IA_ROOT . '/addons/wn_storex/inc/web/business.inc.php',
	IA_ROOT . '/addons/wn_storex/inc/web/brand.inc.php',
	IA_ROOT . '/addons/wn_storex/inc/web/goodscategory.inc.php',
	IA_ROOT . '/addons/wn_storex/inc/web/goodsmanage.inc.php',
	IA_ROOT . '/addons/wn_storex/inc/web/goodscomment.inc.php',
	IA_ROOT . '/addons/wn_storex/inc/web/order.inc.php',
	IA_ROOT . '/addons/wn_storex/inc/web/room_price.inc.php',
	IA_ROOT . '/addons/wn_storex/inc/web/room_status.inc.php',
);
if (!empty($unused_files)) {
	foreach ($unused_files as $file) {
		file_delete($file);
	}
}