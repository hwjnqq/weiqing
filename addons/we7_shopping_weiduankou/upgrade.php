<?php

if (!pdo_fieldexists('shopping_weiduankou_goods', 'otherpay')) {
	pdo_query('ALTER TABLE ' . tablename('shopping_weiduankou_goods') . " ADD `otherpay` VARCHAR(255) NOT NULL DEFAULT ''");
}