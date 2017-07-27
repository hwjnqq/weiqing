<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$ops = array('display');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

if ($op == 'display') {
	$storeid = intval($_GPC['storeid']);
	$clerk_list = pdo_getall('storex_clerk', array('weid' => $_W['uniacid'], 'userid' => $_W['uid']), array('userid', 'permission', 'storeid'), 'storeid');
	if (!empty($clerk_list) && is_array($clerk_list)) {
		$storeids = array_keys($clerk_list);
	}
	if (!in_array($storeid, $storeids)) {
		$storeid = $storeids[0];
	}
	$store_list = pdo_getall('storex_bases', array('id' => $storeids), array('id', 'title', 'thumb'), 'id');
	$current_user_permission_info = pdo_get('users_permission', array('uniacid' => $_W['uniacid'], 'uid' => $_W['uid'], 'type' => $this->module['name']));
	$current_user_permission = explode('|', $current_user_permission_info['permission']);
	$permissions = array(
		array(
			'title' => '店铺操作',
			'items' => array(
				array(
					'title' => '订单管理',
					'icon' => 'fa fa-bar-chart',
					'type' => 'url',
					'url' => $this->createWebUrl('shop_order', array('storeid' => $storeid)),
					'permission' => 'wn_storex_permission_order'
				),
				array(
					'title' => '商品管理',
					'icon' => 'fa fa-bar-chart',
					'type' => 'url',
					'url' => $this->createWebUrl('shop_goodsmanage', array('storeid' => $storeid)),
					'permission' => 'wn_storex_permission_room'
				),
			),
		),
		array(
			'title' => '用户操作',
			'items' => array(
				array(
					'title' => '积分充值',
					'icon' => 'fa fa-bar-chart',
					'type' => 'modal',
					'url' => 'credit1',
					'permission' => 'wn_storex_permission_mc_credit1'
				),
				array(
					'title' => '余额充值',
					'icon' => 'fa fa-bar-chart',
					'type' => 'modal',
					'url' => 'credit2',
					'permission' => 'wn_storex_permission_mc_credit2'
				),
				array(
					'title' => '消费',
					'icon' => 'fa fa-bar-chart',
					'type' => 'modal',
					'url' => 'consume',
					'permission' => 'wn_storex_permission_mc_conusme'
				),
				array(
					'title' => '发放会员卡',
					'icon' => 'fa fa-bar-chart',
					'type' => 'modal',
					'url' => 'card',
					'permission' => 'wn_storex_permission_mc_card'
				),
				array(
					'title' => '卡券核销',
					'icon' => 'fa fa-bar-chart',
					'type' => 'modal',
					'url' => 'cardconsume',
					'permission' => 'wn_storex_permission_coupon_consume'
				),
			)
		)
	);
	if ($_W['user']['type'] == 3) {
		foreach ($permissions as $key => &$row) {
			$has = 0;
			foreach ($row['items'] as $key1 => $row1) {
				if (!in_array($row1['permission'], $current_user_permission)) {
					unset($row['items'][$key1]);
				}
			}
		}
		unset($row);
	}
}

include $this->template('clerk/clerkdesk');