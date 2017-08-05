<?php
$sql = "
	CREATE TABLE IF NOT EXISTS `ims_storex_plugin_foods` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `storeid` int(11) NOT NULL COMMENT '所属酒店',
	  `title` varchar(100) NOT NULL COMMENT '名称',
	  `price` decimal(10,2) NOT NULL COMMENT '价格',
	  `sold_num` int(11) NOT NULL COMMENT '已售的数量',
	  `thumbs` text NOT NULL COMMENT '图片',
	  `content` text NOT NULL COMMENT '描述',
	  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态',
	  `weid` int(11) NOT NULL,
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `ims_storex_plugin_foods_order` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `time` int(11) NOT NULL COMMENT '下单时间',
	  `weid` int(11) NOT NULL,
	  `storeid` int(11) NOT NULL COMMENT '酒店id',
	  `eattime` int(11) NOT NULL COMMENT '用餐时间',
	  `place` varchar(48) NOT NULL COMMENT '用餐地点',
	  `remark` varchar(255) NOT NULL COMMENT '备注',
	  `mngtime` int(11) NOT NULL COMMENT '操作员确认时间',
	  `foods` text NOT NULL COMMENT '用户点的菜单',
	  `status` int(2) NOT NULL COMMENT '0未确认1已确认2完成 -1取消',
	  `sumprice` decimal(10,2) NOT NULL COMMENT '总价',
	  `ordersn` varchar(30) NOT NULL COMMENT '订单号',
	  `openid` varchar(255) NOT NULL COMMENT '预定人openid',
	  `mobile` varchar(255) NOT NULL COMMENT '预定人电话',
	  `contact_name` varchar(255) NOT NULL COMMENT '联系人',
	  `paystatus` tinyint(2) NOT NULL COMMENT '0未支付1已支付',
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;

";
pdo_run($sql);