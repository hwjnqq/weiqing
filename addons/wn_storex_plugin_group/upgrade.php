<?php
if (!pdo_fieldexists('storex_order', 'group_goodsid')) {
	pdo_query("ALTER TABLE " . tablename('storex_order') . " ADD `group_goodsid` INT(11) NOT NULL COMMENT '拼团商品设置的id';");
}
if (!pdo_fieldexists('storex_order', 'group_id')) {
	pdo_query("ALTER TABLE " . tablename('storex_order') . " ADD `group_id` INT(11) NOT NULL COMMENT '开团后的id';");
}