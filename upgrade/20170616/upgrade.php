<?php
/**
 * 删除reply_type字段
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

if(!pdo_fieldexists('rule', 'reply_type')) {
	pdo_query("ALTER TABLE ". tablename('rule') ." DROP `reply_type`;");
}