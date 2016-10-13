<?php
if (pdo_fieldexists('business', 'content')) {
	pdo_query('ALTER TABLE '.tablename('business')." CHANGE `content` `content` mediumtext NOT NULL DEFAULT ''");
}