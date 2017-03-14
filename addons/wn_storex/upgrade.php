<?php 
if (!pdo_fieldexists('storex_room', 'email')) {
	pdo_query('ALTER TABLE ' . tablename('storex_room') . " ADD `is_house` INT(11) NOT NULL DEFAULT '1' COMMENT '是否是房型 1 是，2不是 ';");
}
?>