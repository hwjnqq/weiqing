<?php
/**
 * 优化自定义菜单后，删除数据库中默认菜单冗余数据
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

$all_currentselfmenu = pdo_getall('uni_account_menus', array('type' => 1));
foreach ($all_currentselfmenu as &$menu) {
	$menu['data'] = iunserializer(base64_decode($menu['data']));
	if (isset($menu['data']['matchrule'])) {
		unset($menu['data']['matchrule']);
	}
	if (isset($menu['data']['type'])) {
		unset($menu['data']['type']);
	}
	if (empty($menu['data']) || empty($menu['data']['button'])) {
		pdo_delete('uni_account_menus', array('id' => $menu['id']));
	} else {
		$newmenudata = base64_encode(iserializer($menu['data']));
		pdo_update('uni_account_menus', array('data' => $newmenudata), array('id' => $menu['id']));
	}
}