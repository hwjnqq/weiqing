<?php

if (!pdo_fieldexists('hotel2_set', 'email')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `email` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '提醒接受邮箱' AFTER `tel`;");
}
if (!pdo_fieldexists('hotel2_set', 'mobile')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `mobile` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '提醒接受手机' AFTER `email`;");
}
if (!pdo_fieldexists('hotel2_set', 'template')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `template` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '发送模板消息' AFTER `mobile`;");
}
if (!pdo_fieldexists('hotel2_set', 'templateid')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `templateid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '模板ID' AFTER `template`;");
}
if (!pdo_fieldexists('hotel2_order', 'remark')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " ADD `remark` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '备注' AFTER `mobile`;");
}
if (!pdo_fieldexists('hotel2_member', 'clerk')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_member') . " ADD `clerk`  VARCHAR(32) NOT NULL DEFAULT '' AFTER `status`;");
}
if (!pdo_fieldexists('hotel2_member', 'nickname')) {
	pdo_query("ALTER TABLE " . tablename('hotel2_member'). " ADD `nickname` VARCHAR(255) NOT NULL DEFAULT ''");
}
if (!pdo_fieldexists('hotel2_set', 'smscode')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `smscode` int(3) NOT NULL DEFAULT '0';");
}
if (!pdo_fieldexists('hotel2_room', 'service')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " ADD `service` int(10) NOT NULL DEFAULT '0';");
}
if (!pdo_fieldexists('hotel2_set', 'refund')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `refund` int(3) NOT NULL DEFAULT '0';");
}
if (!pdo_fieldexists('hotel2_order', 'comment')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " ADD `comment` int(3) NOT NULL DEFAULT '0';");
}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_hotel12_code`  (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL,
  `openid` varchar(50) NOT NULL,
  `code` varchar(6) NOT NULL,
  `mobile` varchar(11) NOT NULL,
  `total` tinyint(3) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `createtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `openid` (`openid`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8");

$sql = "CREATE TABLE IF NOT EXISTS ".tablename('hotel2_comment')." (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) DEFAULT '0',
	`hotelid` int(11) DEFAULT '0',
	`uid` int(11) DEFAULT '0',
	`createtime` int(11) DEFAULT '0',
	`comment` varchar(255) DEFAULT '',
	PRIMARY KEY (`id`)
)ENGINE=MyISAM  DEFAULT CHARSET=utf8";
pdo_query($sql);
if (!pdo_fieldexists('hotel2_set', 'refuse_templateid')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `refuse_templateid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '拒绝提醒模板id';");
}
if (!pdo_fieldexists('hotel2_set', 'confirm_templateid')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `confirm_templateid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '确认提醒模板id';");
}
//hotel2_set表中添加酒店入住提醒模板id
if (!pdo_fieldexists('hotel2_set', 'check_in_templateid')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `check_in_templateid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '酒店已入住通知模板id';");
}
//hotel2_set表中添加酒店订单完成提醒模板id
if (!pdo_fieldexists('hotel2_set', 'finish_templateid')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `finish_templateid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '酒店订单完成通知模板id';");
}
//添加店员评分表
$sqls = "CREATE TABLE IF NOT EXISTS ".tablename('hotel2_comment_clerk'). "(
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
pdo_query($sqls);
//酒店订单表中添加店员评分字段
if (!pdo_fieldexists('hotel2_order', 'clerkcomment')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " ADD `clerkcomment` INT(11)  DEFAULT '0' COMMENT '店员评分';");
}
if (!pdo_fieldexists('hotel2', 'integral_rate')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2') . " ADD `integral_rate` INT(11) NOT NULL DEFAULT '0' COMMENT '在该酒店消费返积分的比例';");
}
//微酒店订单表的价格字段与房间表的价格字段的类型不一致
if (pdo_fieldexists('hotel2_order', 'oprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " CHANGE `oprice` `oprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_order', 'cprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " CHANGE `cprice` `cprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_order', 'mprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " CHANGE `mprice` `mprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_order', 'sum_price')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " CHANGE `sum_price` `sum_price` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_room', 'oprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " CHANGE `oprice` `oprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_room', 'cprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " CHANGE `cprice` `cprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_room', 'service')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " CHANGE `service` `service` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_room_price', 'oprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room_price') . " CHANGE `oprice` `oprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_room_price', 'cprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room_price') . " CHANGE `cprice` `cprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
if (pdo_fieldexists('hotel2_room_price', 'mprice')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room_price') . " CHANGE `mprice` `mprice` DECIMAL(10,2) NULL DEFAULT '0.00' ;");
}
//微酒店设置添加字段
if (!pdo_fieldexists('hotel2_set', 'nickname')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_set') . " ADD `nickname` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '提醒接收微信' ;");
}

//万能小店
//备份hotel2表
pdo_query('ALTER TABLE ' . tablename('hotel2') . " RENAME TO ims_hotel2_beifen");

//hotel2_room添加字段
if (!pdo_fieldexists('hotel2_room', 'pcate')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " ADD `pcate` int(10) unsigned NOT NULL DEFAULT '0';");
}
if (!pdo_fieldexists('hotel2_room', 'ccate')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " ADD `ccate` int(10) unsigned NOT NULL DEFAULT '0';");
}
if (!pdo_fieldexists('hotel2_room', 'reserve_device')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " ADD `reserve_device` text COMMENT '预定说明';");
}
if (!pdo_fieldexists('hotel2_room', 'can_reserve')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " ADD `can_reserve` int(11) NOT NULL DEFAULT '1' COMMENT '预定设置';");
}
if (!pdo_fieldexists('hotel2_room', 'can_buy')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " ADD `can_buy` int(11) NOT NULL DEFAULT '1' COMMENT '购买设置';");
}
if (!pdo_fieldexists('hotel2_room', 'sold_num')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " ADD `sold_num` int(11) NOT NULL DEFAULT '0' COMMENT '已售的数量';");
}
if (!pdo_fieldexists('hotel2_room', 'store_type')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_room') . " ADD `store_type` int(8) NOT NULL DEFAULT '0' COMMENT '所属店铺类型';");
}

//hotel2_order添加字段
if (!pdo_fieldexists('hotel2_order', 'mode_distribute')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " ADD `mode_distribute` INT(11) NULL COMMENT '配送方式 1自提 ，2配送';");
}
if (!pdo_fieldexists('hotel2_order', 'order_time')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " ADD `order_time` INT(11) NOT NULL DEFAULT '0' COMMENT '自提是自提时间，配送是配送时间';");
}
if (!pdo_fieldexists('hotel2_order', 'addressid')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " ADD `addressid` INT(11) NOT NULL COMMENT '配送选择的地址id';");
}
if (!pdo_fieldexists('hotel2_order', 'goods_status')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " ADD `goods_status` INT(11) NOT NULL COMMENT '货物状态：1未发送，2已发送，3已收货';");
}
if (!pdo_fieldexists('hotel2_order', 'action')) {
	pdo_query('ALTER TABLE ' . tablename('hotel2_order') . " ADD `action` INT(11) NOT NULL DEFAULT '2' COMMENT '1预定 2购买';");
}

//添加表
pdo_query("CREATE TABLE IF NOT EXISTS `ims_hotel2` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`store_base_id` int(11) NOT NULL COMMENT '店铺基表对应的id',
		`weid` int(11) DEFAULT '0',
		`ordermax` int(11) DEFAULT '0',
		`numsmax` int(11) DEFAULT '0',
		`daymax` int(11) DEFAULT '0',
		`roomcount` int(11) DEFAULT '0',
		`sales` text,
		`level` int(11) DEFAULT '0',
		`device` text,
		`brandid` int(11) DEFAULT '0',
		`businessid` int(11) DEFAULT '0',
		PRIMARY KEY (`id`),
		KEY `indx_weid` (`weid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8");

pdo_query("CREATE TABLE IF NOT EXISTS `ims_store_bases` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`weid` int(11) DEFAULT '0',
		`title` varchar(255) DEFAULT '',
		`lng` decimal(10,2) DEFAULT '0.00',
		`lat` decimal(10,2) DEFAULT '0.00',
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
		`integral_rate` int(11) NOT NULL DEFAULT '0' COMMENT '在该店铺消费返积分的比例',
		`store_type` int(8) NOT NULL DEFAULT '0' COMMENT '店铺类型',
		`extend_table` varchar(50) DEFAULT NULL COMMENT '该店铺对应的扩张表',
		`timestart` int(11) NOT NULL DEFAULT '0' COMMENT '运营开始时间',
		`timeend` int(11) NOT NULL DEFAULT '0' COMMENT '运营结束时间',
		PRIMARY KEY (`id`),
		KEY `indx_weid` (`weid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8");

pdo_query("CREATE TABLE IF NOT EXISTS `ims_store_categorys` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`weid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属帐号',
		`name` varchar(50) NOT NULL COMMENT '分类名称',
		`thumb` varchar(255) NOT NULL COMMENT '分类图片',
		`store_base_id` int(11) NOT NULL COMMENT '该分类属于哪个店铺的',
		`parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID,0为第一级',
		`isrecommand` int(10) DEFAULT '0',
		`description` varchar(500) NOT NULL COMMENT '分类介绍',
		`displayorder` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
		`enabled` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开启',
		PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8");

pdo_query("CREATE TABLE IF NOT EXISTS `ims_store_goods` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`store_base_id` int(11) DEFAULT '0',
		`weid` int(11) DEFAULT '0',
		`pcate` int(10) unsigned NOT NULL DEFAULT '0',
		`ccate` int(10) unsigned NOT NULL DEFAULT '0',
		`title` varchar(255) DEFAULT '',
		`thumb` varchar(255) DEFAULT '',
		`oprice` decimal(10,2) DEFAULT '0.00',
		`cprice` decimal(10,2) DEFAULT '0.00',
		`mprice` varchar(255) NOT NULL DEFAULT '',
		`thumbs` text,
		`device` text,
		`reserve_device` text COMMENT '预定说明',
		`status` int(11) DEFAULT '0',
		`sales` text,
		`can_reserve` int(11) NOT NULL DEFAULT '1' COMMENT '预定设置',
		`can_buy` int(11) NOT NULL DEFAULT '1' COMMENT '购买设置',
		`isshow` int(11) DEFAULT '0',
		`score` int(11) DEFAULT '0' COMMENT '购买商品积分',
		`sortid` int(11) DEFAULT '0',
		`sold_num` int(11) NOT NULL DEFAULT '0' COMMENT '已售的数量',
		`store_type` int(8) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`),
		KEY `indx_weid` (`weid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8");

//store_bases 字段
$store_base = array(
		'id',
		'weid',
		'title',
		'lng',
		'lat',
		'address',
		'location_p',
		'location_c',
		'location_a',
		'status',
		'phone',
		'mail',
		'thumb',
		'thumborder',
		'description',
		'content',
		'store_info',
		'traffic',
		'thumbs',
		'detail_thumbs',
		'displayorder',
		'integral_rate',
		'store_type',
		'extend_table',
		'timestart',
		'timeend',
);

//hotel2 现有字段
$hotel2 = array(
		'store_base_id',
		'weid',
		'ordermax',
		'numsmax',
		'daymax',
		'roomcount',
		'sales',
		'level',
		'device',
		'brandid',
		'businessid',
);

//备份hotel2表，将备份表的数据分到store_bases表和扩展表hotel2
$hotel2_beifen = pdo_getall('hotel2_beifen');
if(!empty($hotel2_beifen)){
	foreach(hotel2_beifen as $val){
		$store_insert = array();
		foreach($store_base as $field){
			if(isset($val[$field])){
				$store_insert[$field] = $val[$field];
			}
			if($field == 'extend_table'){
				$store_insert[$field] = 'hotel2';
			}
			if($field == 'store_type'){
				$store_insert[$field] = 1;
			}
		}
		pdo_insert('store_bases', $store_insert);
		$hotel2_insert = array();
		foreach($hotel2 as $hotel2_field){
			if(isset($val[$hotel2_field])){
				$hotel2_insert[$hotel2_field] = $val[$hotel2_field];
			}
			if($hotel2_field == 'store_base_id'){
				$hotel2_insert[$hotel2_field] = $val['id'];
			}
		}
		pdo_insert('hotel2', $hotel2_insert);
	}
}

//给每个店铺添加一个默认的分类
$store_bases = pdo_getall('store_bases');
if(!empty($store_bases)){
	foreach($store_bases as $store_info){
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
		);
		pdo_insert('store_categorys', $category_insert);
	}
}

//给hotel2_room新加的字段赋值
// pcate	一级分类id
// ccate	二级分类
// reserve_device	预定说明
// can_reserve		能否预定
// can_buy			能否购买
// sold_num			商品卖的数量
// store_type		所属店铺的类型
$store_categorys = pdo_getall('store_categorys');
$hotel2_room = pdo_getall('hotel2_room');
if(!empty($hotel2_room)){
	foreach($hotel2_room as $room_info){
		$update_room = array(
				'can_reserve' => 1,
				'can_buy' => 1,
		);
		if(!empty($store_bases)){
			foreach($store_bases as $store_info){
				if($room_info['weid'] == $store_info['weid'] && $room_info['hotelid'] == $store_info['id']){
					$update_room['store_type'] = $store_info['store_type'];
				}
			}
		}
		if(!empty($store_categorys)){
			foreach($store_categorys as $category_info){
				if($category_info['store_base_id'] == $room_info['hotelid'] && $category_info['weid'] == $room_info['weid']){
					$update_room['pcate'] = $category_info['id'];
				}
			}
		}
		pdo_update('hotel2_room', $update_room, array('weid' => $room_info['weid'], 'id' => $room_info['id']));
	}
}