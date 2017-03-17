<?php 
if (!pdo_fieldexists('storex_room', 'is_house')) {
	pdo_query('ALTER TABLE ' . tablename('storex_room') . " ADD `is_house` INT(11) NOT NULL DEFAULT '1' COMMENT '是否是房型 1 是，2不是 ';");
}
if (!pdo_fieldexists('storex_comment', 'goodsid')) {
	pdo_query('ALTER TABLE ' . tablename('storex_comment') . " ADD `goodsid` INT(11) NOT NULL COMMENT '评论商品的id';");
}
if (!pdo_fieldexists('storex_comment', 'comment_level')) {
	pdo_query('ALTER TABLE ' . tablename('storex_comment') . " ADD `comment_level` TINYINT(11) NOT NULL COMMENT '评论商品的级别';");
}
?>