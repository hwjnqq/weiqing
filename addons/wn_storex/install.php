<?php 
$sql = "CREATE TABLE IF NOT EXISTS `ims_storex_hotel` (
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
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_brand` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) DEFAULT '0',
	`title` varchar(255) DEFAULT '',
	`displayorder` int(11) DEFAULT '0',
	`status` int(11) DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`),
	KEY `indx_displayorder` (`displayorder`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

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
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_comment` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) DEFAULT '0',
	`hotelid` int(11) DEFAULT '0',
	`uid` int(11) DEFAULT '0',
	`createtime` int(11) DEFAULT '0',
	`comment` varchar(255) DEFAULT '',
	`goodsid` int(11) NOT NULL COMMENT '评论商品的id',
	`comment_level` tinyint(11) NOT NULL COMMENT '评论商品的级别',
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

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
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		
	CREATE TABLE IF NOT EXISTS `ims_storex_member` (
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
	`clerk` varchar(32) NOT NULL DEFAULT '',
	`username` varchar(30) DEFAULT '' COMMENT '用户名',
	`password` varchar(200) DEFAULT '' COMMENT '密码',
	`salt` varchar(8) NOT NULL DEFAULT '' COMMENT '加密盐',
	`islogin` tinyint(3) NOT NULL DEFAULT '0',
	`isauto` tinyint(1) NOT NULL DEFAULT '0' COMMENT '自动添加，0否，1是',
	`nickname` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_order` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) DEFAULT '0',
	`hotelid` int(11) DEFAULT '0',
	`roomid` int(11) DEFAULT '0',
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
	`mprice` decimal(10,2) DEFAULT '0.00',
	`info` text,
	`time` int(11) DEFAULT '0',
	`status` int(11) DEFAULT '0',
	`mode_distribute` int(11) NOT NULL COMMENT '配送方式  1自提 ，2配送',
	`track_number` varchar(64) NOT NULL COMMENT '物流单号',
	`express_name` varchar(50) NOT NULL COMMENT '物流类型',
	`order_time` int(11) NOT NULL DEFAULT '0' COMMENT '自提是自提时间，配送是配送时间',
	`addressid` int(11) NOT NULL COMMENT '配送选择的地址id',
	`goods_status` int(11) NOT NULL COMMENT '货物状态：1未发送，2已发送，3已收货',
	`paytype` int(11) DEFAULT '0',
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
	PRIMARY KEY (`id`),
	KEY `indx_hotelid` (`hotelid`),
	KEY `indx_weid` (`weid`),
	KEY `indx_roomid` (`roomid`),
	KEY `indx_memberid` (`memberid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_reply` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) NOT NULL,
	`rid` int(11) NOT NULL,
	`hotelid` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`),
	KEY `indx_rid` (`rid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_room` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`hotelid` int(11) DEFAULT '0',
	`weid` int(11) DEFAULT '0',
	`pcate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '一级分类id',
	`ccate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '二级分类id',
	`title` varchar(255) DEFAULT '',
	`thumb` varchar(255) DEFAULT '',
	`oprice` decimal(10,2) DEFAULT '0.00',
	`cprice` decimal(10,2) DEFAULT '0.00',
	`mprice` varchar(255) NOT NULL DEFAULT '',
	`thumbs` text,
	`device` text,
	`reserve_device` text COMMENT '预定说明',
	`area` varchar(255) DEFAULT '',
	`floor` varchar(255) DEFAULT '',
	`smoke` varchar(255) DEFAULT '',
	`bed` varchar(255) DEFAULT '',
	`persons` int(11) DEFAULT '0',
	`bedadd` varchar(30) DEFAULT '',
	`status` int(11) DEFAULT '0',
	`can_reserve` int(11) NOT NULL DEFAULT '1' COMMENT '预定设置',
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
	PRIMARY KEY (`id`),
	KEY `indx_hotelid` (`hotelid`),
	KEY `indx_weid` (`weid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_room_price` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) DEFAULT '0',
	`hotelid` int(11) DEFAULT '0',
	`roomid` int(11) DEFAULT '0',
	`roomdate` int(11) DEFAULT '0',
	`thisdate` varchar(255) NOT NULL DEFAULT '' COMMENT '当天日期',
	`oprice` decimal(10,2) DEFAULT '0.00',
	`cprice` decimal(10,2) DEFAULT '0.00',
	`mprice` decimal(10,2) DEFAULT '0.00',
	`num` varchar(255) DEFAULT '-1',
	`status` int(11) DEFAULT '1',
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`),
	KEY `indx_hotelid` (`hotelid`),
	KEY `indx_roomid` (`roomid`),
	KEY `indx_roomdate` (`roomdate`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

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
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

	CREATE TABLE IF NOT EXISTS `ims_storex_code` (
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
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_bases` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`distance` int(11) NOT NULL COMMENT '配送距离',
	`weid` int(11) DEFAULT '0',
	`title` varchar(255) DEFAULT '',
	`skin_style` varchar(48) NOT NULL DEFAULT 'display' COMMENT '皮肤选择',
	`category_set` tinyint(4) NOT NULL DEFAULT '1' COMMENT '分类开启设置1开启，2关闭',
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
	`integral_rate` int(11) NOT NULL DEFAULT '0' COMMENT '在该店铺消费返积分的比例',
	`store_type` int(8) NOT NULL DEFAULT '0' COMMENT '店铺类型',
	`extend_table` varchar(50) NOT NULL COMMENT '该店铺对应的扩张表',
	`timestart` int(11) NOT NULL DEFAULT '0' COMMENT '运营开始时间',
	`timeend` int(11) NOT NULL DEFAULT '0' COMMENT '运营结束时间',
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

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
	`displayorder` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
	`enabled` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开启',
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_goods` (
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
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	
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
			'etime', 'style', 'nums', 'oprice', 'cprice', 'mprice', 'info', 'time', 'status', 'paytype',
			'paystatus', 'msg', 'mngtime', 'contact_name', 'day', 'sum_price', 'ordersn', 'comment', 'clerkcomment',
		),
		'hotel2_reply' => array('id', 'weid', 'rid', 'hotelid',),
		'hotel2_room' => array('id', 'hotelid', 'weid', 'title', 'thumb', 'oprice', 'cprice', 'mprice', 'thumbs', 'device',
			'area', 'floor', 'smoke', 'bed', 'persons', 'bedadd', 'status', 'isshow', 'sales', 'displayorder',
			'area_show', 'floor_show', 'smoke_show', 'bed_show', 'persons_show', 'bedadd_show', 'score', 'breakfast', 'sortid', 'service',
		),
		'hotel2_room_price' => array('id', 'weid', 'hotelid', 'roomid', 'roomdate', 'thisdate', 'oprice', 'cprice', 'mprice', 'num',
			'status',
		),
		'hotel2_set' => array('id', 'weid', 'user', 'reg', 'bind', 'regcontent', 'ordertype', 'is_unify', 'tel', 'email',
			'mobile', 'template', 'templateid', 'paytype1', 'paytype2', 'paytype3', 'version', 'location_p', 'location_c', 'location_a',
			'smscode', 'refund', 'refuse_templateid', 'confirm_templateid', 'check_in_templateid', 'finish_templateid', 'nickname',
		),
		'hotel12_code' => array('id', 'weid', 'openid', 'code', 'mobile', 'total', 'status', 'createtime',),
	);
	//将原数据填入新的表中
	foreach($ewei_hotel_table as $hotel2_table => $storex_table){
		if (pdo_tableexists($hotel2_table) && !empty($hotel2_all_table[$hotel2_table])) {
			$hotel2_data = pdo_getall($hotel2_table);
			if(!empty($hotel2_data)){
				foreach ($hotel2_data as $val){
					$insert = array();
					foreach($hotel2_all_table[$hotel2_table] as $field){
						if (isset($val[$field])) {
							$insert[$field] = $val[$field];
						}
					}
					pdo_insert($storex_table, $insert);
				}
			}
		}
	}
	
	//storex_bases 字段
	$storex_base = array('id', 'weid', 'title', 'lng', 'lat', 'address', 'location_p', 'location_c', 'location_a', 'status',
		'phone', 'mail', 'thumb', 'thumborder', 'description', 'content', 'store_info', 'traffic', 'thumbs', 'detail_thumbs',
		'displayorder', 'integral_rate', 'store_type', 'extend_table', 'timestart', 'timeend',
	);
	
	//storex_hotel 现有字段
	$storex_hotel = array(
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
	//微酒店的hotel2表，将hotel2的数据分到storex_bases表和扩展表storex_hotel
	$hotel2_beifen = pdo_getall('hotel2');
	if(!empty($hotel2_beifen)){
		foreach($hotel2_beifen as $val){
			$store_insert = array();
			foreach($storex_base as $field){
				if(isset($val[$field])){
					$store_insert[$field] = $val[$field];
				}
				if($field == 'extend_table'){
					$store_insert[$field] = 'storex_hotel';
				}
				if($field == 'store_type'){
					$store_insert[$field] = 1;
				}
			}
			pdo_insert('storex_bases', $store_insert);
			$hotel2_insert = array();
			foreach($storex_hotel as $hotel2_field){
				if(isset($val[$hotel2_field])){
					$hotel2_insert[$hotel2_field] = $val[$hotel2_field];
				}
				if($hotel2_field == 'store_base_id'){
					$hotel2_insert[$hotel2_field] = $val['id'];
				}
			}
			pdo_insert('storex_hotel', $hotel2_insert);
		}
	}
	
	//给每个店铺添加一个默认的分类
	$storex_bases = pdo_getall('storex_bases');
	if(!empty($storex_bases)){
		foreach($storex_bases as $store_info){
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
	// reserve_device	预定说明
	// can_reserve		能否预定
	// can_buy			能否购买
	// sold_num			商品卖的数量
	// store_type		所属店铺的类型
	$store_categorys = pdo_getall('storex_categorys');
	$storex_room = pdo_getall('storex_room');
	if(!empty($storex_room)){
		foreach($storex_room as $room_info){
			$update_room = array(
					'can_reserve' => 1,
					'can_buy' => 1,
					'is_house' => 1,
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
			pdo_update('storex_room', $update_room, array('weid' => $room_info['weid'], 'id' => $room_info['id']));
		}
	}
}
