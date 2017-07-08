<?php
/**
 * 更新uni_account表title_initial字段
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

if (pdo_fieldexists('uni_account', 'title_initial')) {
	$accounts = pdo_getall('uni_account', array(), array('name', 'uniacid', 'default_acid', 'title_initial'));
	if (!empty($accounts)) {
		foreach ($accounts as $account) {
			if (empty($account['title_initial'])) {
				$first_char = get_first_pinyin($account['name']);
				pdo_update('uni_account', array('title_initial' => $first_char), array('uniacid' => $account['uniacid'], 'default_acid' => $account['default_acid']));
			}
		}
	}
}