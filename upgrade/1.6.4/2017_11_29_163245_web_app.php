<?php

namespace We7\V164;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1511944365
 * @version 1.6.4
 */

class WebApp {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('modules', 'webapp_support')) {
			pdo_query('ALTER TABLE ' . tablename('modules') . " ADD `webapp_support` int(2) NOT NULL DEFAULT 1 COMMENT '支持系统PC';");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		