<?php
defined('IN_IA') or exit('Access Denied');

global $_GPC, $_W;
define('SCRIPT_URL', $_W['siteroot'] . '/addons/wn_storex/template/style/js');
$dos = array('shop_settings', 'shop_category', 'shop_goodsmanage', 'shop_room_status', 'shop_room_price', 'shop_tagmanage', 'shop_comment', 'shop_index', 'shop_order', 'shop_stat', 'shop_plugin', 'shop_plugin_printer', 'shop_plugin_hotelservice', 'shop_homepage', 'shop_wxapphomepage', 'shop_member', 'shop_clerk', 'shop_room_item', 'shop_market', 'shop_memberlevel', 'shop_article', 'shop_sales_package', 'shop_agent', 'shop_agent_level', 'shop_agent_log', 'shop_blast', 'shop_blast_message', 'shop_blast_stat', 'shop_spec', 'shop_spec_value', 'shop_goods_spec', 'shop_activity', 'shop_share');
$log = admin_operation_log();
if (!empty($log)) {
	write_log($log);
}
if (in_array($_GPC['do'], $dos)) {
	if (empty($_GPC['storeid']) && !($_GPC['do'] == 'shop_settings' && $_GPC['action'] == 'add')) {
		message('请重新选择店铺', $this->createWebUrl('storemanage', array('op' => 'list')), 'error');
	}
	$storex_bases = pdo_get('storex_bases', array('id' => $_GPC['storeid']));
	$_W['wn_storex']['store_info'] = $storex_bases;
	$_W['wn_storex']['table_storeid'] = 'store_base_id';
	if (empty($_W['wn_storex']['store_info']['store_type'])) {
		$_W['wn_storex']['goods_table'] = 'storex_goods';
	} elseif ($_W['wn_storex']['store_info']['store_type'] == 1) {
		$_W['wn_storex']['goods_table'] = 'storex_room';
	}
	$aside_show = true;
	if ($_W['user']['type'] == 3) {
		mload()->model('clerk');
		$clerk_permission = clerk_permission($_GPC['storeid'], $_W['uid']);
		$permission_check = true;
		if ($_GPC['do'] == 'shop_order' && (empty($clerk_permission) || !in_array('wn_storex_permission_order', $clerk_permission))) {
			$permission_check = false;
		}
		if ($_GPC['do'] == 'shop_goodsmanage' && (empty($clerk_permission) || !in_array('wn_storex_permission_room', $clerk_permission))) {
			$permission_check = false;
		}
		if ($_GPC['do'] == 'shop_room_status' && (empty($clerk_permission) || !in_array('wn_storex_permission_room', $clerk_permission))) {
			$permission_check = false;
		}
		if ($_GPC['do'] == 'shop_room_price' && (empty($clerk_permission) || !in_array('wn_storex_permission_room', $clerk_permission))) {
			$permission_check = false;
		}
		if (empty($permission_check)) {
			message('您没有管理该店铺的权限', '', 'error');
		}
		$aside_show = false;
		if (in_array('wn_storex_menu_storemanage', $clerk_permission)) {
			$aside_show = true;
		}
	}
}

//店铺后台菜单设置
$aside_nav = array(
	'shop_index' => array(
		'title' => '概况',
		'url' => $this->createWebUrl('shop_index', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-index',
		'active' => array(
			'shop_index',
		)
	),
	'shop_homepage' => array(
		'title' => '首页设置',
		'url' => $this->createWebUrl('shop_homepage', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-home',
		'active' => array(
			'shop_homepage'
		)
	),
	'shop_wxapphomepage' => array(
		'title' => '小程序首页设置',
		'url' => $this->createWebUrl('shop_wxapphomepage', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-home',
		'active' => array(
			'shop_wxapphomepage'
		)
	),
	'shop_category' => array(
		'title' => '商品分类',
		'url' => $this->createWebUrl('shop_category', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-category',
		'active' => array(
			'shop_category',
		)
	),
	'shop_spec' => array(
		'title' => '商品规格',
		'url' => $this->createWebUrl('shop_spec', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-category',
		'active' => array(
			'shop_spec',
			'shop_spec_value'
		)
	),
	'shop_goodsmanage' => array(
		'title' => '商品管理',
		'url' => $this->createWebUrl('shop_goodsmanage', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-goods',
		'active' => array(
			'shop_goodsmanage',
			'shop_room_status',
			'shop_room_price',
			'shop_room_item',
			'shop_tagmanage',
			'shop_goods_spec'
		)
	),
	'shop_comment' => array(
		'title' => '商品评价',
		'url' => $this->createWebUrl('shop_comment', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-comment',
		'active' => array(
			'shop_comment',
		)
	),
	'shop_order' => array(
		'title' => '店铺订单',
		'url' => $this->createWebUrl('shop_order', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-order',
		'active' => array(
			'shop_order',
		)
	),
	'shop_stat' => array(
		'title' => '数据统计',
		'url' => $this->createWebUrl('shop_stat', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-stat',
		'active' => array(
			'shop_stat',
		)
	),
	'shop_member' => array(
		'title' => '用户管理',
		'url' => $this->createWebUrl('shop_member', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-user',
		'active' => array(
			'shop_member',
			'shop_memberlevel',
		)
	),
	'shop_clerk' => array(
		'title' => '店员管理',
		'url' => $this->createWebUrl('shop_clerk', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-clerk',
		'active' => array(
			'shop_clerk',
		)
	),
	'shop_article' => array(
		'title' => '文章管理',
		'url' => $this->createWebUrl('shop_article', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-clerk',
		'active' => array(
			'shop_article',
			'shop_article_category'
		)
	),
	'shop_agent' => array(
		'title' => '销售员管理',
		'url' => $this->createWebUrl('shop_agent', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-clerk',
		'active' => array(
			'shop_agent',
			'shop_agent_level',
			'shop_agent_log',
		)
	),
	'shop_market' => array(
		'title' => '营销',
		'url' => $this->createWebUrl('shop_market', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-app',
		'active' => array(
			'shop_market',
			'shop_sales_package'
		)
	),
	'shop_activity' => array(
		'title' => '活动',
		'url' => $this->createWebUrl('shop_activity', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-app',
		'active' => array(
			'shop_activity',
		)
	),
	'shop_blast' => array(
		'title' => '爆客',
		'url' => $this->createWebUrl('shop_blast', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-app',
		'active' => array(
			'shop_blast',
			'shop_blast_message',
			'shop_blast_stat'
		)
	),
	'shop_plugin' => array(
		'title' => '应用',
		'url' => $this->createWebUrl('shop_plugin', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-app',
		'active' => array(
			'shop_plugin',
			'shop_plugin_printer',
			'shop_plugin_hotelservice'
		)
	),
	'shop_settings' => array(
		'title' => '店铺设置',
		'url' => $this->createWebUrl('shop_settings', array('storeid' => $_GPC['storeid'])),
		'icon' => 'storex-menu-setting',
		'active' => array(
			'shop_settings',
			'shop_share',
		)
	),
);
if ($_W['wn_storex']['store_info']['store_type'] == 1) {
	unset($aside_nav['shop_agent']);
	if ($_GPC['do'] == 'shop_agent' || $_GPC['do'] == 'shop_agent_level') {
		message('酒店暂时没有销售员功能', referer(), 'error');
	}
	unset($aside_nav['shop_spec']);
	if ($_GPC['do'] == 'shop_spec' || $_GPC['do'] == 'shop_spec_value' || $_GPC['do'] == 'shop_goods_spec') {
		message('酒店暂时没有规格功能', referer(), 'error');
	}
	unset($aside_nav['shop_activity']);
	if ($_GPC['do'] == 'shop_activity') {
		message('酒店暂时没有活动功能', referer(), 'error');
	}
}
if (!check_ims_version()) {
	unset($aside_nav['shop_plugin']);
	if ($_GPC['do'] == 'shop_plugin' || $_GPC['do'] == 'shop_plugin_printer' || $_GPC['shop_plugin_hotelservice']) {
		message('请升级微擎系统至1.0以上，并保持最新版本', '', 'error');
	}
}