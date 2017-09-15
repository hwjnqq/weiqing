<?php 
$sql = "
	CREATE TABLE IF NOT EXISTS `ims_storex_hotel` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`store_base_id` int(11) NOT NULL COMMENT '店铺基表对应的id',
	`weid` int(11) DEFAULT '0',
	`roomcount` int(11) DEFAULT '0',
	`device` text,
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_brand` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) DEFAULT '0',
	`title` varchar(255) DEFAULT '',
	`displayorder` int(11) DEFAULT '0',
	`status` int(11) DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`),
	KEY `indx_displayorder` (`displayorder`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_business` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) DEFAULT '0',
	`title` varchar(255) DEFAULT '',
	`location_p` varchar(255) DEFAULT '',
	`location_c` varchar(255) DEFAULT '',
	`location_a` varchar(255) DEFAULT '',
	`displayorder` int(11) DEFAULT '0',
	`status` int(11) DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_comment` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) DEFAULT '0',
	`hotelid` int(11) DEFAULT '0',
	`uid` int(11) DEFAULT '0',
	`createtime` int(11) DEFAULT '0',
	`comment` varchar(255) DEFAULT '',
	`goodsid` int(11) NOT NULL COMMENT '评论商品的id',
	`comment_level` tinyint(11) NOT NULL COMMENT '评论商品的级别',
	`type` int(10) unsigned DEFAULT '1' COMMENT '回复类型，1为用户，2为虚拟，3为管理员回复',
	`cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当type为3，管理员回复时评价id',
	`nickname` varchar(255) NOT NULL,
	`thumb` varchar(64) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_comment_clerk` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) DEFAULT '0',
	`hotelid` int(11) DEFAULT '0',
	`orderid` int(25) DEFAULT '0',
	`createtime` int(11) DEFAULT '0',
	`comment` varchar(255) DEFAULT '',
	`clerkid` int(11) DEFAULT '0',
	`realname` varchar(20) DEFAULT NULL,
	`grade` int(11) DEFAULT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_member` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) DEFAULT '0',
	`userid` varchar(50) DEFAULT '',
	`from_user` varchar(50) DEFAULT '',
	`realname` varchar(255) DEFAULT '',
	`mobile` varchar(255) DEFAULT '',
	`phone` varchar(11) NOT NULL COMMENT '只接收验证码的手机号',
	`email` varchar(50) NOT NULL COMMENT '只接收验证码的邮箱',
	`score` int(11) DEFAULT '0' COMMENT '积分',
	`createtime` int(11) DEFAULT '0',
	`userbind` int(11) DEFAULT '0',
	`status` int(11) DEFAULT '0',
	`clerk` varchar(32) NOT NULL DEFAULT '',
	`username` varchar(30) DEFAULT '' COMMENT '用户名',
	`password` varchar(200) DEFAULT '' COMMENT '密码',
	`salt` varchar(8) NOT NULL DEFAULT '' COMMENT '加密盐',
	`islogin` tinyint(3) NOT NULL DEFAULT '0',
	`isauto` tinyint(1) NOT NULL DEFAULT '0' COMMENT '自动添加，0否，1是',
	`nickname` varchar(255) NOT NULL DEFAULT '',
	`credit_password` varchar(200) NOT NULL COMMENT '余额支付密码',
	`credit_salt` varchar(8) NOT NULL COMMENT '加密盐',
	`password_lock` varchar(24) NOT NULL COMMENT '改密码的依据',
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_order` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) DEFAULT '0',
	`hotelid` int(11) DEFAULT '0',
	`roomid` int(11) DEFAULT '0',
	`spec_id` int(11) NOT NULL COMMENT '规格对应的商品id',
	`spec_info` text NOT NULL COMMENT '商品规格信息',
	`memberid` int(11) DEFAULT '0',
	`openid` varchar(255) DEFAULT '',
	`name` varchar(255) DEFAULT '',
	`mobile` varchar(255) DEFAULT '',
	`remark` varchar(255) DEFAULT '',
	`btime` int(11) DEFAULT '0',
	`etime` int(11) DEFAULT '0',
	`style` varchar(255) DEFAULT '',
	`nums` int(11) DEFAULT '0',
	`oprice` decimal(10,2) DEFAULT '0.00',
	`cprice` decimal(10,2) DEFAULT '0.00',
	`info` text,
	`time` int(11) DEFAULT '0',
	`status` int(11) DEFAULT '0',
	`mode_distribute` int(11) NOT NULL COMMENT '配送方式  1自提 ，2配送',
	`track_number` varchar(64) NOT NULL COMMENT '物流单号',
	`express_name` varchar(50) NOT NULL COMMENT '物流类型',
	`order_time` int(11) NOT NULL DEFAULT '0' COMMENT '自提是自提时间，配送是配送时间',
	`addressid` int(11) NOT NULL COMMENT '配送选择的地址id',
	`goods_status` int(11) NOT NULL COMMENT '货物状态：1未发送，2已发送，3已收货',
	`refund_status` tinyint(2) NOT NULL DEFAULT '2' COMMENT '1是退款，2是不退款',
	`paytype` varchar(20) NOT NULL DEFAULT '',
	`action` int(11) NOT NULL DEFAULT '2' COMMENT '1预定  2购买',
	`paystatus` int(11) DEFAULT '0',
	`comment` int(3) NOT NULL DEFAULT '0',
	`msg` text,
	`mngtime` int(11) DEFAULT '0',
	`contact_name` varchar(30) NOT NULL DEFAULT '' COMMENT '联系人',
	`day` tinyint(2) NOT NULL DEFAULT '0' COMMENT '住几晚',
	`sum_price` decimal(10,2) DEFAULT '0.00',
	`ordersn` varchar(30) DEFAULT '',
	`clerkcomment` int(11) DEFAULT '0',
	`coupon` int(11) NOT NULL COMMENT '使用卡券信息',
	`static_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '初始订单的价格，不可更改',
	`roomitemid` varchar(200) NOT NULL COMMENT '房间号ID',
	`newuser` int(4) NOT NULL COMMENT '0未使用新用户活动，1已使用',
	`market_types` varchar(48) NOT NULL COMMENT '订单使用店铺内活动的类型',
	`agentid` int(11) NOT NULL COMMENT '销售员id',
	`is_package` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否为套餐，1是普通商品，2是套餐',
	`cost_credit` decimal(10,2) NOT NULL COMMENT '抵扣的积分',
	`replace_money` decimal(10,2) NOT NULL COMMENT '抵扣的钱',
	PRIMARY KEY (`id`),
	KEY `indx_hotelid` (`hotelid`),
	KEY `indx_weid` (`weid`),
	KEY `indx_roomid` (`roomid`),
	KEY `indx_memberid` (`memberid`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_reply` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) NOT NULL,
	`rid` int(11) NOT NULL,
	`hotelid` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`),
	KEY `indx_rid` (`rid`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_room` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`store_base_id` int(11) DEFAULT '0',
	`weid` int(11) DEFAULT '0',
	`pcate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '一级分类id',
	`ccate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '二级分类id',
	`title` varchar(255) DEFAULT '',
	`sub_title` varchar(12) NOT NULL COMMENT '副标题',
	`thumb` varchar(255) DEFAULT '',
	`oprice` decimal(10,2) DEFAULT '0.00',
	`cprice` decimal(10,2) DEFAULT '0.00',
	`thumbs` text,
	`device` text,
	`area` varchar(255) DEFAULT '',
	`floor` varchar(255) DEFAULT '',
	`smoke` varchar(255) DEFAULT '',
	`bed` varchar(255) DEFAULT '',
	`persons` int(11) DEFAULT '0',
	`bedadd` varchar(30) DEFAULT '',
	`status` int(11) DEFAULT '0',
	`recycle` tinyint(2) NOT NULL DEFAULT '2' COMMENT '1在回收站，2不在',
	`can_buy` int(11) NOT NULL DEFAULT '1' COMMENT '购买设置',
	`isshow` int(11) DEFAULT '0',
	`sales` text,
	`displayorder` int(11) DEFAULT '0',
	`area_show` int(11) DEFAULT '0',
	`floor_show` int(11) DEFAULT '0',
	`smoke_show` int(11) DEFAULT '0',
	`bed_show` int(11) DEFAULT '0',
	`persons_show` int(11) DEFAULT '0',
	`bedadd_show` int(11) DEFAULT '0',
	`score` int(11) DEFAULT '0' COMMENT '订房积分',
	`breakfast` tinyint(3) DEFAULT '0' COMMENT '0无早 1单早 2双早',
	`sortid` int(11) NOT NULL DEFAULT '0' COMMENT '房间id，排序时使用',
	`sold_num` int(11) NOT NULL DEFAULT '0' COMMENT '已售的数量',
	`nickname` varchar(255) NOT NULL DEFAULT '',
	`service` decimal(10,2) DEFAULT '0.00',
	`store_type` int(8) NOT NULL DEFAULT '1' COMMENT '所属店铺类型',
	`is_house` int(11) NOT NULL DEFAULT '1' COMMENT '是否是房型 1 是，2不是',
	`express_set` text NOT NULL COMMENT '运费设置',
	PRIMARY KEY (`id`),
	KEY `indx_hotelid` (`store_base_id`),
	KEY `indx_weid` (`weid`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_room_price` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) DEFAULT '0',
	`hotelid` int(11) DEFAULT '0',
	`roomid` int(11) DEFAULT '0',
	`roomdate` int(11) DEFAULT '0',
	`thisdate` varchar(255) NOT NULL DEFAULT '' COMMENT '当天日期',
	`oprice` decimal(10,2) DEFAULT '0.00',
	`cprice` decimal(10,2) DEFAULT '0.00',
	`num` varchar(255) DEFAULT '-1',
	`status` int(11) DEFAULT '1',
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`),
	KEY `indx_hotelid` (`hotelid`),
	KEY `indx_roomid` (`roomid`),
	KEY `indx_roomdate` (`roomdate`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_set` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) DEFAULT '0',
	`user` tinyint(1) DEFAULT '0' COMMENT '用户类型0微信用户1独立用户',
	`reg` tinyint(1) DEFAULT '0' COMMENT '是否允许注册0禁止注册1允许注册',
	`bind` tinyint(1) DEFAULT '0' COMMENT '是否绑定',
	`regcontent` text COMMENT '注册提示',
	`ordertype` tinyint(1) DEFAULT '0' COMMENT '预定类型0电话预定1电话和网络预订',
	`is_unify` tinyint(1) DEFAULT '0' COMMENT '0使用各分店电话,1使用统一电话',
	`tel` varchar(20) DEFAULT '' COMMENT '统一电话',
	`email` varchar(255) NOT NULL DEFAULT '' COMMENT '提醒接受邮箱',
	`mobile` varchar(32) NOT NULL DEFAULT '' COMMENT '提醒接受手机',
	`template` varchar(32) DEFAULT NULL COMMENT '发送模板消息',
	`templateid` varchar(255) NOT NULL,
	`paytype1` tinyint(1) DEFAULT '0',
	`paytype2` tinyint(1) DEFAULT '0',
	`paytype3` tinyint(1) DEFAULT '0',
	`version` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0单酒店版1多酒店版',
	`location_p` varchar(50) DEFAULT '',
	`location_c` varchar(50) DEFAULT '',
	`location_a` varchar(50) DEFAULT '',
	`smscode` int(3) NOT NULL DEFAULT '0',
	`refund` int(3) NOT NULL DEFAULT '0',
	`refuse_templateid` varchar(255) NOT NULL DEFAULT '' COMMENT '提醒接受邮箱',
	`confirm_templateid` varchar(255) NOT NULL DEFAULT '' COMMENT '提醒接受邮箱',
	`check_in_templateid` varchar(255) NOT NULL DEFAULT '' COMMENT '酒店已入住通知模板id',
	`finish_templateid` varchar(255) NOT NULL DEFAULT '' COMMENT '酒店订单完成通知模板id',
	`nickname` varchar(20) NOT NULL COMMENT '提醒接收微信',
	`extend_switch` varchar(400) NOT NULL COMMENT '扩展开关',
	`source` tinyint(4) NOT NULL DEFAULT '2' COMMENT '卡券类型，1为系统卡券，2为微信卡券',
	`location` tinyint(2) NOT NULL DEFAULT '1' COMMENT '是否开启定位1开2关',
	`credit_pay` tinyint(2) NOT NULL DEFAULT '1' COMMENT '积分抵扣设置1开启，2关闭',
	`credit_ratio` int(11) NOT NULL COMMENT '抵扣比例',
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

	CREATE TABLE IF NOT EXISTS `ims_storex_code` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`weid` int(10) unsigned NOT NULL,
	`openid` varchar(50) NOT NULL,
	`code` varchar(6) NOT NULL,
	`mobile` varchar(11) NOT NULL,
	`email` varchar(50) NOT NULL COMMENT '邮箱',
	`total` tinyint(3) unsigned NOT NULL,
	`status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '1未使用，2已使用',
	`createtime` int(10) unsigned NOT NULL,
	PRIMARY KEY (`id`),
	KEY `openid` (`openid`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_bases` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`distance` int(11) NOT NULL COMMENT '配送距离',
	`weid` int(11) DEFAULT '0',
	`title` varchar(255) DEFAULT '',
	`skin_style` varchar(48) NOT NULL DEFAULT 'display' COMMENT '皮肤选择',
	`lng` decimal(10,6) DEFAULT '0.00',
	`lat` decimal(10,6) DEFAULT '0.00',
	`address` varchar(255) DEFAULT '',
	`location_p` varchar(50) DEFAULT '',
	`location_c` varchar(50) DEFAULT '',
	`location_a` varchar(50) DEFAULT '',
	`status` int(11) DEFAULT '0',
	`phone` varchar(255) DEFAULT '',
	`mail` varchar(255) DEFAULT '',
	`thumb` varchar(255) DEFAULT '',
	`thumborder` varchar(255) DEFAULT '',
	`description` text,
	`content` text,
	`store_info` text COMMENT '关于我们',
	`traffic` text,
	`thumbs` text,
	`detail_thumbs` text COMMENT '详情页图片',
	`displayorder` int(11) DEFAULT '0',
	`store_type` int(8) NOT NULL DEFAULT '0' COMMENT '店铺类型',
	`timestart` varchar(50) NOT NULL DEFAULT '0' COMMENT '运营开始时间',
	`timeend` varchar(50) NOT NULL DEFAULT '0' COMMENT '运营结束时间',
	`refund` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否可退款 1可以 ，2不可以',
	`emails` varchar(200) NOT NULL COMMENT '接收所有邮箱',
	`phones` varchar(200) NOT NULL COMMENT '接收所有电话',
	`openids` varchar(200) NOT NULL COMMENT '接收所有微信',
	`market_status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '营销开关',
	`max_replace` decimal(10,2) NOT NULL COMMENT '最大抵扣金额',
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_categorys` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`weid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属帐号',
	`name` varchar(50) NOT NULL COMMENT '分类名称',
	`thumb` varchar(255) NOT NULL COMMENT '分类图片',
	`store_base_id` int(11) NOT NULL COMMENT '该分类属于哪个店铺的',
	`category_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '分类类型  1 酒店，2,普通',
	`parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID,0为第一级',
	`isrecommand` int(10) DEFAULT '0',
	`description` varchar(500) NOT NULL COMMENT '分类介绍',
	`spec` varchar(1500) NOT NULL DEFAULT '' COMMENT '规格详情',
	`displayorder` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
	`enabled` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开启',
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_goods` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`store_base_id` int(11) DEFAULT '0',
	`weid` int(11) DEFAULT '0',
	`pcate` int(10) unsigned NOT NULL DEFAULT '0',
	`ccate` int(10) unsigned NOT NULL DEFAULT '0',
	`title` varchar(255) DEFAULT '',
	`sub_title` varchar(12) NOT NULL COMMENT '副标题',
	`thumb` varchar(255) DEFAULT '',
	`oprice` decimal(10,2) DEFAULT '0.00',
	`cprice` decimal(10,2) DEFAULT '0.00',
	`thumbs` text,
	`device` text,
	`status` int(11) DEFAULT '0',
	`recycle` tinyint(2) NOT NULL DEFAULT '2' COMMENT '1在回收站，2不在',
	`sales` text,
	`can_buy` int(11) NOT NULL DEFAULT '1' COMMENT '购买设置',
	`isshow` int(11) DEFAULT '0',
	`score` int(11) DEFAULT '0' COMMENT '购买商品积分',
	`sortid` int(11) DEFAULT '0',
	`sold_num` int(11) NOT NULL DEFAULT '0' COMMENT '已售的数量',
	`store_type` int(8) NOT NULL DEFAULT '0',
	`express_set` text NOT NULL COMMENT '运费设置',
	`tag` int(11) NOT NULL,
	`unit` varchar(12) NOT NULL COMMENT '单位',
	`weight` double NOT NULL COMMENT '重量',
	`stock` int(11) NOT NULL DEFAULT '-1' COMMENT '库存',
	`stock_control` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1永不减库存，2拍下减库存，3付款减库存',
	`min_buy` int(11) NOT NULL DEFAULT '1' COMMENT '单次最小购买',
	`max_buy` int(11) NOT NULL DEFAULT '-1' COMMENT '单次最多购买 -1不限',
	`agent_ratio` varchar(300) NOT NULL COMMENT '分销比例',
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`)
	) DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_clerk` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) DEFAULT '0',
	`userid` int(11) DEFAULT '0',
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
	`storeid` int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`),
	KEY `storeid` (`storeid`)
	) DEFAULT CHARSET=utf8;
		
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
	) DEFAULT CHARSET=utf8;

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
	) DEFAULT CHARSET=utf8;
	
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
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_sign_set` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL DEFAULT '0',
	`sign` varchar(1000) NOT NULL,
	`share` varchar(500) NOT NULL,
	`content` text NOT NULL,
	`status` tinyint(2) NOT NULL COMMENT '开启状态 1开启，2关闭',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`)
	) DEFAULT CHARSET=utf8;
	
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
	) DEFAULT CHARSET=utf8;

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
	) DEFAULT CHARSET=utf8;

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
	) DEFAULT CHARSET=utf8;

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
	) DEFAULT CHARSET=utf8 COMMENT='兑换表';
		
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
	) DEFAULT CHARSET=utf8;
		
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
	`granttype` tinyint(4) NOT NULL COMMENT '获取卡券的方式：1 兑换，2 扫码，3派发',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`,`acid`),
	KEY `card_id` (`card_id`),
	KEY `hash` (`hash`)
	) DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_coupon_store` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) NOT NULL,
	`couponid` varchar(255) NOT NULL DEFAULT '',
	`storeid` int(10) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `couponid` (`couponid`)
	) DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_mc_member_property` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) NOT NULL,
	`property` varchar(200) NOT NULL DEFAULT '' COMMENT '当前公众号用户属性',
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8 COMMENT='用户属性设置表';

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
	) DEFAULT CHARSET=utf8;
	
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
	) DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
	
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
	) DEFAULT CHARSET=utf8 COMMENT='积分兑换店员表';
	
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
	) DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_users_permission` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL,
	`uid` int(10) unsigned NOT NULL,
	`type` varchar(30) NOT NULL,
	`permission` varchar(10000) NOT NULL,
	`url` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;

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
	) DEFAULT CHARSET=utf8;
	
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
	`type` varchar(100) NOT NULL COMMENT '首页块类型',
	`items` longtext NOT NULL,
	`displayorder` int(10) unsigned NOT NULL DEFAULT '0',
	`is_wxapp` tinyint(2) NOT NULL DEFAULT '2' COMMENT '是不是小程序1是2不是',
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
	) DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_tags` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) NOT NULL,
	`storeid` int(11) NOT NULL,
	`title` varchar(48) NOT NULL COMMENT '标签名称',
	`status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态 1开启，2关闭',
	`displayorder` int(10) NOT NULL COMMENT '排序',
	PRIMARY KEY (`id`)
	)  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_room_items` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL DEFAULT '0',
	`storeid` int(10) unsigned NOT NULL DEFAULT '0',
	`roomid` int(10) unsigned NOT NULL DEFAULT '0',
	`roomnumber` varchar(100) NOT NULL COMMENT '房间号',
	`status` int(11) DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `storeid` (`storeid`),
	KEY `roomid` (`roomid`)
	) DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_market` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) unsigned NOT NULL DEFAULT '0',
	`storeid` int(11) unsigned NOT NULL DEFAULT '0',
	`type` varchar(15) NOT NULL COMMENT '活动类型',
	`items` varchar(1000) NOT NULL,
	`status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态',
	`starttime` int(11) unsigned NOT NULL,
	`endtime` int(11) unsigned NOT NULL,
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `storeid` (`storeid`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_member_level` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) NOT NULL,
	`storeid` int(11) NOT NULL COMMENT '店铺id',
	`title` varchar(24) NOT NULL COMMENT '名称',
	`ask` int(11) NOT NULL COMMENT '条件',
	`level` int(8) NOT NULL COMMENT '等级',
	`status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态 1开启，2关闭',
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_goods_extend` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`defined` text NOT NULL COMMENT '自定义字段的键值',
	`goodsid` int(11) NOT NULL COMMENT '商品id',
	`uniacid` int(11) NOT NULL,
	`storeid` int(11) NOT NULL COMMENT '酒店id',
	`goods_table` varchar(24) NOT NULL COMMENT '商品表名',
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_plugin_room_goods` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) DEFAULT '0',
	`storeid` int(11) DEFAULT NULL COMMENT '酒店id',
	`title` varchar(255) DEFAULT '',
	`price` decimal(10,2) DEFAULT '0.00',
	`status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态1是待确认，2是已确认',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `storeid` (`storeid`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_article_category` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) DEFAULT '0',
	`storeid` int(11) DEFAULT NULL COMMENT '酒店id',
	`title` varchar(30) NOT NULL,
	`displayorder` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`status` tinyint(3) unsigned NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `storeid` (`storeid`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_article` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uniacid` int(10) unsigned NOT NULL,
	`storeid` int(10) unsigned NOT NULL,
	`pcate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '一级分类',
	`title` varchar(100) NOT NULL DEFAULT '',
	`description` varchar(100) NOT NULL DEFAULT '',
	`content` mediumtext NOT NULL,
	`thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
	`source` varchar(255) NOT NULL DEFAULT '' COMMENT '来源',
	`author` varchar(50) NOT NULL COMMENT '作者',
	`displayorder` int(10) unsigned NOT NULL DEFAULT '0',
	`createtime` int(10) unsigned NOT NULL DEFAULT '0',
	`click` int(10) unsigned NOT NULL DEFAULT '0',
	`type` varchar(10) NOT NULL DEFAULT '',
	`credit` varchar(255) NOT NULL DEFAULT '',
	`status` tinyint(3) unsigned NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `storeid` (`storeid`)
	) DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_agent_apply` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) DEFAULT '0',
	`storeid` int(11) DEFAULT '0',
	`openid` varchar(50) NOT NULL,
	`uid` int(10) unsigned NOT NULL,
	`orderids` longtext,
	`status` tinyint(3) DEFAULT '0' COMMENT '1,待审核，2审核通过，3拒绝',
	`applytime` int(11) DEFAULT '0',
	`paytime` int(11) DEFAULT '0',
	`refusetime` int(11) DEFAULT '0',
	`income` decimal(10,2) DEFAULT '0.00',
	`outcome` decimal(10,2) NOT NULL DEFAULT '0.00',
	`alipay` varchar(50) NOT NULL DEFAULT '',
	`realname` varchar(50) NOT NULL DEFAULT '',
	`tel` varchar(20) NOT NULL DEFAULT '',
	`reason` varchar(50) NOT NULL DEFAULT '' COMMENT '拒绝原因',
	`level` int(10) unsigned DEFAULT '0' COMMENT '等级',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `storeid` (`storeid`),
	KEY `status` (`status`)
	) DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_agent_level` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) NOT NULL,
	`storeid` int(11) NOT NULL COMMENT '店铺id',
	`title` varchar(24) NOT NULL COMMENT '名称',
	`need` int(11) NOT NULL COMMENT '条件',
	`status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态 1开启，2关闭',
	`isdefault` tinyint(2) NOT NULL COMMENT '是否是默认1是,0不是',
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_sales_package` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`storeid` int(11) DEFAULT '0',
	`uniacid` int(11) DEFAULT '0',
	`title` varchar(255) DEFAULT '',
	`sub_title` varchar(12) NOT NULL COMMENT '副标题',
	`thumb` varchar(255) DEFAULT '',
	`price` decimal(10,2) DEFAULT '0.00',
	`express` decimal(10,2) DEFAULT '0.00',
	`goodsids` varchar(1000) DEFAULT '',
	`status` int(11) DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `storeid` (`storeid`)
	) DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_agent_log` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) NOT NULL,
	`uid` int(10) NOT NULL,
	`agentid` int(11) NOT NULL COMMENT '分销员id',
	`orderid` int(11) NOT NULL COMMENT '订单id',
	`storeid` int(11) NOT NULL,
	`goodid` int(11) NOT NULL,
	`sumprice` decimal(10,2) NOT NULL COMMENT '订单总价',
	`money` decimal(10,2) NOT NULL COMMENT '抽成',
	`rate` varchar(20) CHARACTER SET utf8mb4 NOT NULL COMMENT '抽成比例百分比',
	`time` int(11) NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_agent_apply_log` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) NOT NULL,
	`uid` int(10) NOT NULL,
	`ordersn` varchar(30) NOT NULL COMMENT '订单号',
	`agentid` int(11) NOT NULL,
	`storeid` int(11) NOT NULL,
	`money` decimal(10,2) NOT NULL COMMENT '提现金额',
	`time` int(11) NOT NULL COMMENT '申请时间',
	`status` tinyint(4) NOT NULL COMMENT '提现状态0未成功1成功',
	`mngtime` int(11) NOT NULL COMMENT '管理员操作时间',
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_goods_package` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`packageid` int(11) DEFAULT '0',
	`storeid` int(11) DEFAULT '0',
	`uniacid` int(11) DEFAULT '0',
	`goodsid` int(11) DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `uniacid` (`uniacid`),
	KEY `storeid` (`storeid`)
	) DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_room_assign` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) NOT NULL,
	`storeid` int(11) NOT NULL,
	`roomid` int(11) NOT NULL,
	`roomitemid` int(11) NOT NULL,
	`time` int(11) NOT NULL COMMENT '房间在此时间内不空闲',
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_admin_logs` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) NOT NULL,
	`uid` int(11) NOT NULL COMMENT '操作者id',
	`username` varchar(50) NOT NULL,
	`time` int(11) NOT NULL COMMENT '操作时间',
	`storeid` int(11) NOT NULL COMMENT '店铺id',
	`content` varchar(500) NOT NULL COMMENT '操作内容',
	`op` varchar(24) NOT NULL,
	`do` varchar(24) NOT NULL,
	`url` varchar(500) NOT NULL COMMENT '访问的url地址',
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_blast_message` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) NOT NULL,
	  `storeid` int(10) unsigned NOT NULL,
	  `type` int(11) DEFAULT NULL,
	  `time` int(11) DEFAULT NULL,
	  `title` varchar(255) NOT NULL,
	  `content` varchar(600) NOT NULL,
	  `status` int(11) NOT NULL,
	  `clerkid` int(11) NOT NULL,
	  `uid` int(10) unsigned NOT NULL DEFAULT '0',
	  `isdefault` tinyint(4) NOT NULL DEFAULT '2' COMMENT '1为默认，2为不是默认',
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_blast_user` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) NOT NULL,
	  `storeid` int(10) unsigned NOT NULL,
	  `clerkid` int(10) unsigned NOT NULL,
	  `time` int(11) DEFAULT NULL,
	  `uuid` varchar(255) NOT NULL,
	  `openid` varchar(255) NOT NULL,
	  `redirect_uri` varchar(500) NOT NULL,
	  `wxuin` varchar(500) NOT NULL,
	  `wxsid` varchar(500) NOT NULL,
	  `pass_ticket` varchar(1000) NOT NULL,
	  `post_url_header` varchar(800) NOT NULL,
	  `synckey` varchar(1000) NOT NULL,
	  `username` varchar(400) NOT NULL DEFAULT '',
	  `skey` varchar(500) NOT NULL,
	  `contact` longtext NOT NULL,
	  `cookie` varchar(1000) NOT NULL,
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_blast_stat` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) NOT NULL,
	  `storeid` int(10) unsigned NOT NULL,
	  `clerkid` int(11) NOT NULL,
	  `msgid` int(11) NOT NULL,
	  `type` int(11) DEFAULT NULL,
	  `time` int(11) DEFAULT NULL,
	  `date` varchar(8) NOT NULL,
	  `num` int(10) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_clerk_pay` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) NOT NULL,
	  `clerkid` int(11) NOT NULL COMMENT '店员id',
	  `type` varchar(24) NOT NULL,
	  `money` decimal(10,2) NOT NULL,
	  `openid` varchar(100) NOT NULL COMMENT '扫码用户',
	  `time` int(11) NOT NULL COMMENT '扫码时间',
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `ims_storex_spec` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `name` varchar(100) CHARACTER SET utf8 NOT NULL,
	  `displayorder` tinyint(4) NOT NULL,
	  `uniacid` int(10) unsigned NOT NULL,
	  `storeid` int(10) unsigned NOT NULL,
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8 COMMENT='规格名称表';

	CREATE TABLE IF NOT EXISTS `ims_storex_spec_value` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(10) unsigned NOT NULL,
	  `storeid` int(10) unsigned NOT NULL,
	  `specid` int(10) unsigned NOT NULL,
	  `name` varchar(100) CHARACTER SET utf8 NOT NULL,
	  `displayorder` tinyint(4) NOT NULL,
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8 COMMENT='规格值表';

	CREATE TABLE IF NOT EXISTS `ims_storex_spec_goods` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `storeid` int(11) DEFAULT '0',
	  `uniacid` int(11) DEFAULT '0',
	  `goodsid` int(11) DEFAULT '0',
	  `pcate` int(10) unsigned NOT NULL DEFAULT '0',
	  `ccate` int(10) unsigned NOT NULL DEFAULT '0',
	  `title` varchar(255) DEFAULT '',
	  `sub_title` varchar(12) NOT NULL COMMENT '副标题',
	  `sp_name` varchar(255) DEFAULT NULL,
	  `sp_val` varchar(1000) NOT NULL DEFAULT '' COMMENT '已选中商品规格',
	  `goods_val` text COMMENT '商品规格属性',
	  `thumb` varchar(255) DEFAULT '',
	  `oprice` decimal(10,2) DEFAULT '0.00',
	  `cprice` decimal(10,2) DEFAULT '0.00',
	  `status` int(11) DEFAULT '0',
	  `stock` int(11) NOT NULL DEFAULT '-1' COMMENT '库存',
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_wxcard_reply` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `rid` int(10) unsigned NOT NULL DEFAULT '0',
	  `title` varchar(30) NOT NULL,
	  `card_id` varchar(50) NOT NULL,
	  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对应卡券表里的id',
	  `brand_name` varchar(30) NOT NULL,
	  `logo_url` varchar(255) NOT NULL,
	  `success` varchar(255) NOT NULL,
	  `error` varchar(255) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `rid` (`rid`)
	) DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_share_set` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) NOT NULL,
	  `storeid` int(11) NOT NULL,
	  `type` varchar(50) NOT NULL COMMENT '分享类型',
	  `title` varchar(200) NOT NULL COMMENT '标题',
	  `thumb` varchar(200) NOT NULL COMMENT '图标',
	  `content` varchar(500) NOT NULL COMMENT '描述',
	  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',
	  `link` varchar(200) NOT NULL COMMENT '链接',
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_goods_activity` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `storeid` int(11) DEFAULT '0',
	  `uniacid` int(11) DEFAULT '0',
	  `title` varchar(255) DEFAULT '',
	  `price` decimal(10,2) DEFAULT '0.00',
	  `nums` int(10) unsigned NOT NULL,
	  `starttime` int(10) unsigned NOT NULL,
	  `endtime` int(10) unsigned NOT NULL,
	  `goodsid` int(10) unsigned NOT NULL,
	  `type` tinyint(1) DEFAULT '1',
	  `status` int(11) DEFAULT '1',
	  `is_spec` tinyint(1) DEFAULT '1',
	  `specid` int(10) unsigned NOT NULL,
	  `sell_nums` int(10) unsigned NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `uniacid` (`uniacid`),
	  KEY `storeid` (`storeid`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_blast_set` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) unsigned NOT NULL,
	  `storeid` int(11) unsigned NOT NULL,
	  `bg_image` varchar(500) NOT NULL,
	  `tail` varchar(255) NOT NULL,
	  PRIMARY KEY (`id`)
	)DEFAULT CHARSET=utf8;
";

pdo_run($sql);

$ewei_hotel_table = array(
	'hotel2_brand' => 'storex_brand',
	'hotel2_business' => 'storex_business',
	'hotel2_comment' => 'storex_comment',
	'hotel2_comment_clerk' => 'storex_comment_clerk',
	'hotel2_member' => 'storex_member',
	'hotel2_order' => 'storex_order',
	'hotel2_reply' => 'storex_reply',
	'hotel2_room' => 'storex_room',
	'hotel2_room_price' => 'storex_room_price',
	'hotel2_set' => 'storex_set',
	'hotel12_code' => 'storex_code',
);
$we7_storex_table = array(
	'storex_hotel',
	'storex_brand',
	'storex_business',
	'storex_comment',
	'storex_comment_clerk',
	'storex_member',
	'storex_order',
	'storex_reply',
	'storex_room',
	'storex_room_price',
	'storex_set',
	'storex_code',
	'storex_bases',
	'storex_categorys',
	'storex_goods',
);
foreach ($we7_storex_table as $key => $value) {
	$sql = 'TRUNCATE TABLE ' . tablename($value);
	pdo_query($sql);
}

load()->model('module');
$module = module_fetch('ewei_hotel');

//已经安装了微酒店
if (!empty($module)){
	//微酒店所有表的字段
	$hotel2_all_table = array(
		'hotel2_brand' => array('id', 'weid', 'title', 'displayorder', 'status',),
		'hotel2_business' => array('id', 'weid', 'title', 'location_p', 'location_c', 'location_a', 'displayorder', 'status',),
		'hotel2_comment' => array('id', 'uniacid', 'hotelid', 'uid', 'createtime', 'comment',),
		'hotel2_comment_clerk' => array('id', 'uniacid', 'hotelid', 'orderid', 'createtime', 'comment', 'clerkid', 'realname', 'grade',),
		'hotel2_member' => array('id', 'weid', 'userid', 'from_user', 'realname', 'mobile', 'score', 'createtime', 'userbind', 'status',
			'username', 'password', 'salt', 'islogin', 'isauto', 'clerk', 'nickname',
		),
		'hotel2_order' => array('id', 'weid', 'hotelid', 'roomid', 'memberid', 'openid', 'name', 'mobile', 'remark', 'btime',
			'etime', 'style', 'nums', 'oprice', 'cprice', 'info', 'time', 'status', 'paytype',
			'paystatus', 'msg', 'mngtime', 'contact_name', 'day', 'sum_price', 'ordersn', 'comment', 'clerkcomment',
		),
		'hotel2_reply' => array('id', 'weid', 'rid', 'hotelid',),
		'hotel2_room' => array('id', 'hotelid', 'weid', 'title', 'thumb', 'oprice', 'cprice', 'thumbs', 'device',
			'area', 'floor', 'smoke', 'bed', 'persons', 'bedadd', 'status', 'isshow', 'sales', 'displayorder',
			'area_show', 'floor_show', 'smoke_show', 'bed_show', 'persons_show', 'bedadd_show', 'score', 'breakfast', 'sortid', 'service',
		),
		'hotel2_room_price' => array('id', 'weid', 'hotelid', 'roomid', 'roomdate', 'thisdate', 'oprice', 'cprice', 'num',
			'status',
		),
		'hotel2_set' => array('id', 'weid', 'user', 'reg', 'bind', 'regcontent', 'ordertype', 'is_unify', 'tel', 'email',
			'mobile', 'template', 'templateid', 'paytype1', 'paytype2', 'paytype3', 'version', 'location_p', 'location_c', 'location_a',
			'smscode', 'refund', 'refuse_templateid', 'confirm_templateid', 'check_in_templateid', 'finish_templateid', 'nickname',
		),
		'hotel12_code' => array('id', 'weid', 'openid', 'code', 'mobile', 'total', 'status', 'createtime',),
	);
	//将原数据填入新的表中
	foreach ($ewei_hotel_table as $hotel2_table => $storex_table){
		if (pdo_tableexists($hotel2_table) && !empty($hotel2_all_table[$hotel2_table])) {
			$hotel2_data = pdo_getall($hotel2_table);
			if (!empty($hotel2_data)) {
				foreach ($hotel2_data as $val) {
					$insert = array();
					foreach ($hotel2_all_table[$hotel2_table] as $field) {
						if (isset($val[$field])) {
							if ($hotel2_table == 'hotel2_order' && $field == 'paytype') {
								if ($val[$field] == 1) {
									$insert[$field] = 'credit';
								} elseif ($val[$field] == 21) {
									$insert[$field] = 'wechat';
								} elseif ($val[$field] == 22) {
									$insert[$field] = 'alipay';
								} elseif ($val[$field] == 3) {
									$insert[$field] = 'delivery';
								} else {
									$insert[$field] = '';
								}
							} else {
								$insert[$field] = $val[$field];
							}
							if ($hotel2_table == 'hotel2_room' && $field == 'hotelid') {
								unset($insert[$field]);
								$insert['store_base_id'] = $val[$field];
							}
						}
					}
					pdo_insert($storex_table, $insert);
				}
			}
		}
	}
	
	//storex_bases 字段
	$storex_base = array('id', 'weid', 'title', 'lng', 'lat', 'address', 'location_p', 'location_c', 'location_a', 'status', 'phone', 'mail', 'thumb', 'thumborder', 'description', 'content', 'store_info', 'traffic', 'thumbs', 'detail_thumbs', 'displayorder', 'store_type', 'timestart', 'timeend');
	
	//storex_hotel 现有字段
	$storex_hotel = array(
		'store_base_id',
		'weid',
		'roomcount',
		'device',
	);
	//微酒店的hotel2表，将hotel2的数据分到storex_bases表和扩展表storex_hotel
	$hotel2_beifen = pdo_getall('hotel2');
	if (!empty($hotel2_beifen)) {
		foreach ($hotel2_beifen as $val) {
			$store_insert = array();
			foreach ($storex_base as $field) {
				if (isset($val[$field])) {
					$store_insert[$field] = $val[$field];
				}
				if ($field == 'store_type') {
					$store_insert[$field] = 1;
				}
			}
			pdo_insert('storex_bases', $store_insert);
			$hotel2_insert = array();
			foreach ($storex_hotel as $hotel2_field) {
				if (isset($val[$hotel2_field])) {
					$hotel2_insert[$hotel2_field] = $val[$hotel2_field];
				}
				if ($hotel2_field == 'store_base_id') {
					$hotel2_insert[$hotel2_field] = $val['id'];
				}
			}
			pdo_insert('storex_hotel', $hotel2_insert);
		}
	}
	
	//给每个店铺添加一个默认的分类
	$storex_bases = pdo_getall('storex_bases');
	if (!empty($storex_bases)) {
		foreach ($storex_bases as $store_info) {
			$category_insert = array(
				'weid' => $store_info['weid'],
				'name' => '房型',
				'thumb' => '',
				'store_base_id' => $store_info['id'],
				'parentid' => 0,
				'isrecommand' => 1,
				'description' => '房型',
				'displayorder' => '',
				'enabled' => 1,
				'category_type' => 1
			);
			pdo_insert('storex_categorys', $category_insert);
		}
	}
	
	//给hotel2_room新加的字段赋值
	// pcate	一级分类id
	// ccate	二级分类
	// can_buy			能否购买
	// sold_num			商品卖的数量
	// store_type		所属店铺的类型
	$store_categorys = pdo_getall('storex_categorys');
	$storex_room = pdo_getall('storex_room');
	if (!empty($storex_room)) {
		foreach ($storex_room as $room_info) {
			$update_room = array(
				'can_buy' => 1,
				'is_house' => 1,
			);
			if (!empty($store_bases)) {
				foreach ($store_bases as $store_info) {
					if ($room_info['weid'] == $store_info['weid'] && $room_info['store_base_id'] == $store_info['id']) {
						$update_room['store_type'] = $store_info['store_type'];
					}
				}
			}
			if (!empty($store_categorys)) {
				foreach ($store_categorys as $category_info) {
					if ($category_info['store_base_id'] == $room_info['store_base_id'] && $category_info['weid'] == $room_info['weid']) {
						$update_room['pcate'] = $category_info['id'];
					}
				}
			}
			pdo_update('storex_room', $update_room, array('weid' => $room_info['weid'], 'id' => $room_info['id']));
		}
	}
}
